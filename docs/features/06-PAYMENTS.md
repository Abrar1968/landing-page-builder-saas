# Payments Feature Documentation

## Overview

The payments system implements a Strategy Pattern for handling multiple payment gateways (Stripe, PayPal) with subscription management, usage limits, and webhook processing.

## Tech Stack

- **Backend**: Laravel
- **Frontend**: AlpineJS, TailwindCSS v4, Blade
- **Design Pattern**: Strategy Pattern

---

## Strategy Pattern Implementation

### PaymentGatewayInterface

```php
<?php

namespace App\Contracts;

use App\Models\User;
use App\Models\Subscription;
use App\DTOs\PaymentResult;
use App\DTOs\CheckoutSession;

interface PaymentGatewayInterface
{
    /**
     * Create a checkout session for subscription
     */
    public function createCheckoutSession(User $user, string $planId, string $billingCycle): CheckoutSession;

    /**
     * Process a payment
     */
    public function processPayment(array $paymentData): PaymentResult;

    /**
     * Create a subscription
     */
    public function createSubscription(User $user, string $planId, string $paymentMethodId): Subscription;

    /**
     * Cancel a subscription
     */
    public function cancelSubscription(string $subscriptionId): bool;

    /**
     * Update subscription plan
     */
    public function updateSubscription(string $subscriptionId, string $newPlanId): Subscription;

    /**
     * Get subscription details
     */
    public function getSubscription(string $subscriptionId): ?array;

    /**
     * Handle webhook payload
     */
    public function handleWebhook(array $payload, string $signature): void;

    /**
     * Get gateway identifier
     */
    public function getIdentifier(): string;
}
```

### StripeGateway

```php
<?php

namespace App\Services\Payment\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\Models\User;
use App\Models\Subscription;
use App\DTOs\PaymentResult;
use App\DTOs\CheckoutSession;
use Stripe\StripeClient;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use Illuminate\Support\Facades\Log;

class StripeGateway implements PaymentGatewayInterface
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    public function createCheckoutSession(User $user, string $planId, string $billingCycle): CheckoutSession
    {
        $priceId = $this->getPriceId($planId, $billingCycle);

        $session = $this->stripe->checkout->sessions->create([
            'customer_email' => $user->email,
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price' => $priceId,
                'quantity' => 1,
            ]],
            'mode' => 'subscription',
            'success_url' => route('subscription.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('subscription.cancel'),
            'metadata' => [
                'user_id' => $user->id,
                'plan_id' => $planId,
                'billing_cycle' => $billingCycle,
            ],
            'subscription_data' => [
                'metadata' => [
                    'user_id' => $user->id,
                    'plan_id' => $planId,
                ],
            ],
        ]);

        return new CheckoutSession(
            id: $session->id,
            url: $session->url,
            gateway: 'stripe'
        );
    }

    public function processPayment(array $paymentData): PaymentResult
    {
        try {
            $paymentIntent = $this->stripe->paymentIntents->create([
                'amount' => $paymentData['amount'],
                'currency' => $paymentData['currency'] ?? 'usd',
                'payment_method' => $paymentData['payment_method_id'],
                'confirm' => true,
                'return_url' => route('payment.return'),
            ]);

            return new PaymentResult(
                success: $paymentIntent->status === 'succeeded',
                transactionId: $paymentIntent->id,
                message: $paymentIntent->status === 'succeeded' ? 'Payment successful' : 'Payment pending'
            );
        } catch (\Exception $e) {
            Log::error('Stripe payment failed', ['error' => $e->getMessage()]);

            return new PaymentResult(
                success: false,
                transactionId: null,
                message: $e->getMessage()
            );
        }
    }

    public function createSubscription(User $user, string $planId, string $paymentMethodId): Subscription
    {
        // Get or create Stripe customer
        $customerId = $this->getOrCreateCustomer($user);

        // Attach payment method to customer
        $this->stripe->paymentMethods->attach($paymentMethodId, [
            'customer' => $customerId,
        ]);

        // Set as default payment method
        $this->stripe->customers->update($customerId, [
            'invoice_settings' => ['default_payment_method' => $paymentMethodId],
        ]);

        // Create subscription
        $stripeSubscription = $this->stripe->subscriptions->create([
            'customer' => $customerId,
            'items' => [['price' => $this->getPriceId($planId, 'monthly')]],
            'metadata' => [
                'user_id' => $user->id,
                'plan_id' => $planId,
            ],
        ]);

        // Create local subscription record
        return Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $planId,
            'gateway' => 'stripe',
            'gateway_subscription_id' => $stripeSubscription->id,
            'gateway_customer_id' => $customerId,
            'status' => $stripeSubscription->status,
            'current_period_start' => now()->timestamp($stripeSubscription->current_period_start),
            'current_period_end' => now()->timestamp($stripeSubscription->current_period_end),
        ]);
    }

    public function cancelSubscription(string $subscriptionId): bool
    {
        try {
            $this->stripe->subscriptions->update($subscriptionId, [
                'cancel_at_period_end' => true,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to cancel Stripe subscription', [
                'subscription_id' => $subscriptionId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function updateSubscription(string $subscriptionId, string $newPlanId): Subscription
    {
        $stripeSubscription = $this->stripe->subscriptions->retrieve($subscriptionId);

        $this->stripe->subscriptions->update($subscriptionId, [
            'items' => [[
                'id' => $stripeSubscription->items->data[0]->id,
                'price' => $this->getPriceId($newPlanId, 'monthly'),
            ]],
            'proration_behavior' => 'create_prorations',
        ]);

        $subscription = Subscription::where('gateway_subscription_id', $subscriptionId)->first();
        $subscription->update(['plan_id' => $newPlanId]);

        return $subscription;
    }

    public function getSubscription(string $subscriptionId): ?array
    {
        try {
            $subscription = $this->stripe->subscriptions->retrieve($subscriptionId);

            return [
                'id' => $subscription->id,
                'status' => $subscription->status,
                'current_period_start' => $subscription->current_period_start,
                'current_period_end' => $subscription->current_period_end,
                'cancel_at_period_end' => $subscription->cancel_at_period_end,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    public function handleWebhook(array $payload, string $signature): void
    {
        try {
            $event = Webhook::constructEvent(
                json_encode($payload),
                $signature,
                config('services.stripe.webhook_secret')
            );
        } catch (SignatureVerificationException $e) {
            throw new \Exception('Invalid webhook signature');
        }

        match ($event->type) {
            'checkout.session.completed' => $this->handleCheckoutCompleted($event->data->object),
            'customer.subscription.updated' => $this->handleSubscriptionUpdated($event->data->object),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($event->data->object),
            'invoice.payment_succeeded' => $this->handlePaymentSucceeded($event->data->object),
            'invoice.payment_failed' => $this->handlePaymentFailed($event->data->object),
            default => Log::info('Unhandled Stripe webhook event', ['type' => $event->type]),
        };
    }

    public function getIdentifier(): string
    {
        return 'stripe';
    }

    protected function getOrCreateCustomer(User $user): string
    {
        if ($user->stripe_customer_id) {
            return $user->stripe_customer_id;
        }

        $customer = $this->stripe->customers->create([
            'email' => $user->email,
            'name' => $user->name,
            'metadata' => ['user_id' => $user->id],
        ]);

        $user->update(['stripe_customer_id' => $customer->id]);

        return $customer->id;
    }

    protected function getPriceId(string $planId, string $billingCycle): string
    {
        $prices = config('subscription.stripe_prices');

        return $prices[$planId][$billingCycle] ?? throw new \Exception("Invalid plan: {$planId}");
    }

    protected function handleCheckoutCompleted($session): void
    {
        $user = User::find($session->metadata->user_id);

        if (!$user) {
            Log::error('User not found for checkout session', ['session_id' => $session->id]);
            return;
        }

        // Subscription is created via subscription.created webhook
        Log::info('Checkout completed', ['user_id' => $user->id, 'session_id' => $session->id]);
    }

    protected function handleSubscriptionUpdated($subscription): void
    {
        $localSubscription = Subscription::where('gateway_subscription_id', $subscription->id)->first();

        if ($localSubscription) {
            $localSubscription->update([
                'status' => $subscription->status,
                'current_period_start' => now()->timestamp($subscription->current_period_start),
                'current_period_end' => now()->timestamp($subscription->current_period_end),
                'cancel_at_period_end' => $subscription->cancel_at_period_end,
            ]);
        }
    }

    protected function handleSubscriptionDeleted($subscription): void
    {
        $localSubscription = Subscription::where('gateway_subscription_id', $subscription->id)->first();

        if ($localSubscription) {
            $localSubscription->update(['status' => 'canceled']);
        }
    }

    protected function handlePaymentSucceeded($invoice): void
    {
        $subscription = Subscription::where('gateway_subscription_id', $invoice->subscription)->first();

        if ($subscription) {
            $subscription->payments()->create([
                'amount' => $invoice->amount_paid / 100,
                'currency' => $invoice->currency,
                'status' => 'succeeded',
                'gateway_payment_id' => $invoice->payment_intent,
            ]);
        }
    }

    protected function handlePaymentFailed($invoice): void
    {
        $subscription = Subscription::where('gateway_subscription_id', $invoice->subscription)->first();

        if ($subscription) {
            $subscription->update(['status' => 'past_due']);

            // Notify user
            $subscription->user->notify(new \App\Notifications\PaymentFailed($subscription));
        }
    }
}
```

### PayPalGateway

```php
<?php

namespace App\Services\Payment\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\Models\User;
use App\Models\Subscription;
use App\DTOs\PaymentResult;
use App\DTOs\CheckoutSession;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayPalGateway implements PaymentGatewayInterface
{
    protected string $baseUrl;
    protected string $clientId;
    protected string $clientSecret;

    public function __construct()
    {
        $this->baseUrl = config('services.paypal.sandbox')
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';
        $this->clientId = config('services.paypal.client_id');
        $this->clientSecret = config('services.paypal.client_secret');
    }

    public function createCheckoutSession(User $user, string $planId, string $billingCycle): CheckoutSession
    {
        $accessToken = $this->getAccessToken();
        $paypalPlanId = $this->getPayPalPlanId($planId, $billingCycle);

        $response = Http::withToken($accessToken)
            ->post("{$this->baseUrl}/v1/billing/subscriptions", [
                'plan_id' => $paypalPlanId,
                'subscriber' => [
                    'name' => [
                        'given_name' => $user->first_name ?? $user->name,
                        'surname' => $user->last_name ?? '',
                    ],
                    'email_address' => $user->email,
                ],
                'application_context' => [
                    'brand_name' => config('app.name'),
                    'locale' => 'en-US',
                    'shipping_preference' => 'NO_SHIPPING',
                    'user_action' => 'SUBSCRIBE_NOW',
                    'return_url' => route('subscription.paypal.success'),
                    'cancel_url' => route('subscription.cancel'),
                ],
                'custom_id' => json_encode([
                    'user_id' => $user->id,
                    'plan_id' => $planId,
                    'billing_cycle' => $billingCycle,
                ]),
            ]);

        $data = $response->json();
        $approvalUrl = collect($data['links'])->firstWhere('rel', 'approve')['href'];

        return new CheckoutSession(
            id: $data['id'],
            url: $approvalUrl,
            gateway: 'paypal'
        );
    }

    public function processPayment(array $paymentData): PaymentResult
    {
        $accessToken = $this->getAccessToken();

        $response = Http::withToken($accessToken)
            ->post("{$this->baseUrl}/v2/checkout/orders", [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'amount' => [
                        'currency_code' => $paymentData['currency'] ?? 'USD',
                        'value' => number_format($paymentData['amount'] / 100, 2, '.', ''),
                    ],
                ]],
            ]);

        if ($response->successful()) {
            $data = $response->json();

            return new PaymentResult(
                success: $data['status'] === 'COMPLETED',
                transactionId: $data['id'],
                message: 'Payment created successfully'
            );
        }

        return new PaymentResult(
            success: false,
            transactionId: null,
            message: $response->json()['message'] ?? 'Payment failed'
        );
    }

    public function createSubscription(User $user, string $planId, string $paymentMethodId): Subscription
    {
        // PayPal subscriptions are created through checkout flow
        // This method handles the confirmation after user approves
        $accessToken = $this->getAccessToken();

        $response = Http::withToken($accessToken)
            ->get("{$this->baseUrl}/v1/billing/subscriptions/{$paymentMethodId}");

        $data = $response->json();

        return Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $planId,
            'gateway' => 'paypal',
            'gateway_subscription_id' => $data['id'],
            'status' => strtolower($data['status']),
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
        ]);
    }

    public function cancelSubscription(string $subscriptionId): bool
    {
        $accessToken = $this->getAccessToken();

        $response = Http::withToken($accessToken)
            ->post("{$this->baseUrl}/v1/billing/subscriptions/{$subscriptionId}/cancel", [
                'reason' => 'Customer requested cancellation',
            ]);

        return $response->successful();
    }

    public function updateSubscription(string $subscriptionId, string $newPlanId): Subscription
    {
        $accessToken = $this->getAccessToken();
        $newPayPalPlanId = $this->getPayPalPlanId($newPlanId, 'monthly');

        $response = Http::withToken($accessToken)
            ->post("{$this->baseUrl}/v1/billing/subscriptions/{$subscriptionId}/revise", [
                'plan_id' => $newPayPalPlanId,
            ]);

        $subscription = Subscription::where('gateway_subscription_id', $subscriptionId)->first();
        $subscription->update(['plan_id' => $newPlanId]);

        return $subscription;
    }

    public function getSubscription(string $subscriptionId): ?array
    {
        $accessToken = $this->getAccessToken();

        $response = Http::withToken($accessToken)
            ->get("{$this->baseUrl}/v1/billing/subscriptions/{$subscriptionId}");

        if ($response->successful()) {
            $data = $response->json();

            return [
                'id' => $data['id'],
                'status' => strtolower($data['status']),
                'current_period_start' => $data['billing_info']['last_payment']['time'] ?? null,
                'current_period_end' => $data['billing_info']['next_billing_time'] ?? null,
            ];
        }

        return null;
    }

    public function handleWebhook(array $payload, string $signature): void
    {
        // Verify webhook signature
        if (!$this->verifyWebhookSignature($payload, $signature)) {
            throw new \Exception('Invalid webhook signature');
        }

        $eventType = $payload['event_type'];

        match ($eventType) {
            'BILLING.SUBSCRIPTION.ACTIVATED' => $this->handleSubscriptionActivated($payload['resource']),
            'BILLING.SUBSCRIPTION.CANCELLED' => $this->handleSubscriptionCancelled($payload['resource']),
            'BILLING.SUBSCRIPTION.SUSPENDED' => $this->handleSubscriptionSuspended($payload['resource']),
            'PAYMENT.SALE.COMPLETED' => $this->handlePaymentCompleted($payload['resource']),
            default => Log::info('Unhandled PayPal webhook event', ['type' => $eventType]),
        };
    }

    public function getIdentifier(): string
    {
        return 'paypal';
    }

    protected function getAccessToken(): string
    {
        $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
            ->asForm()
            ->post("{$this->baseUrl}/v1/oauth2/token", [
                'grant_type' => 'client_credentials',
            ]);

        return $response->json()['access_token'];
    }

    protected function getPayPalPlanId(string $planId, string $billingCycle): string
    {
        $plans = config('subscription.paypal_plans');

        return $plans[$planId][$billingCycle] ?? throw new \Exception("Invalid plan: {$planId}");
    }

    protected function verifyWebhookSignature(array $payload, string $signature): bool
    {
        $accessToken = $this->getAccessToken();

        $response = Http::withToken($accessToken)
            ->post("{$this->baseUrl}/v1/notifications/verify-webhook-signature", [
                'auth_algo' => request()->header('PAYPAL-AUTH-ALGO'),
                'cert_url' => request()->header('PAYPAL-CERT-URL'),
                'transmission_id' => request()->header('PAYPAL-TRANSMISSION-ID'),
                'transmission_sig' => $signature,
                'transmission_time' => request()->header('PAYPAL-TRANSMISSION-TIME'),
                'webhook_id' => config('services.paypal.webhook_id'),
                'webhook_event' => $payload,
            ]);

        return $response->json()['verification_status'] === 'SUCCESS';
    }

    protected function handleSubscriptionActivated($resource): void
    {
        $customData = json_decode($resource['custom_id'], true);
        $user = User::find($customData['user_id']);

        if ($user) {
            Subscription::updateOrCreate(
                ['gateway_subscription_id' => $resource['id']],
                [
                    'user_id' => $user->id,
                    'plan_id' => $customData['plan_id'],
                    'gateway' => 'paypal',
                    'status' => 'active',
                    'current_period_start' => now(),
                    'current_period_end' => now()->addMonth(),
                ]
            );
        }
    }

    protected function handleSubscriptionCancelled($resource): void
    {
        Subscription::where('gateway_subscription_id', $resource['id'])
            ->update(['status' => 'canceled']);
    }

    protected function handleSubscriptionSuspended($resource): void
    {
        Subscription::where('gateway_subscription_id', $resource['id'])
            ->update(['status' => 'suspended']);
    }

    protected function handlePaymentCompleted($resource): void
    {
        $subscription = Subscription::where('gateway_subscription_id', $resource['billing_agreement_id'])->first();

        if ($subscription) {
            $subscription->payments()->create([
                'amount' => $resource['amount']['total'],
                'currency' => $resource['amount']['currency'],
                'status' => 'succeeded',
                'gateway_payment_id' => $resource['id'],
            ]);
        }
    }
}
```

### PaymentContext

```php
<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGatewayInterface;
use App\Models\User;
use App\Models\Subscription;
use App\DTOs\PaymentResult;
use App\DTOs\CheckoutSession;
use App\Services\Payment\Gateways\StripeGateway;
use App\Services\Payment\Gateways\PayPalGateway;
use InvalidArgumentException;

class PaymentContext
{
    protected PaymentGatewayInterface $gateway;

    protected array $gateways = [
        'stripe' => StripeGateway::class,
        'paypal' => PayPalGateway::class,
    ];

    public function __construct(?string $gateway = null)
    {
        $gateway = $gateway ?? config('subscription.default_gateway', 'stripe');
        $this->setGateway($gateway);
    }

    public function setGateway(string $gateway): self
    {
        if (!isset($this->gateways[$gateway])) {
            throw new InvalidArgumentException("Payment gateway '{$gateway}' is not supported.");
        }

        $this->gateway = app($this->gateways[$gateway]);

        return $this;
    }

    public function getGateway(): PaymentGatewayInterface
    {
        return $this->gateway;
    }

    public function createCheckoutSession(User $user, string $planId, string $billingCycle): CheckoutSession
    {
        return $this->gateway->createCheckoutSession($user, $planId, $billingCycle);
    }

    public function processPayment(array $paymentData): PaymentResult
    {
        return $this->gateway->processPayment($paymentData);
    }

    public function createSubscription(User $user, string $planId, string $paymentMethodId): Subscription
    {
        return $this->gateway->createSubscription($user, $planId, $paymentMethodId);
    }

    public function cancelSubscription(string $subscriptionId): bool
    {
        return $this->gateway->cancelSubscription($subscriptionId);
    }

    public function updateSubscription(string $subscriptionId, string $newPlanId): Subscription
    {
        return $this->gateway->updateSubscription($subscriptionId, $newPlanId);
    }

    public function getSubscription(string $subscriptionId): ?array
    {
        return $this->gateway->getSubscription($subscriptionId);
    }

    public function handleWebhook(array $payload, string $signature): void
    {
        $this->gateway->handleWebhook($payload, $signature);
    }

    public function getIdentifier(): string
    {
        return $this->gateway->getIdentifier();
    }
}
```

---

## DTOs

### CheckoutSession

```php
<?php

namespace App\DTOs;

class CheckoutSession
{
    public function __construct(
        public readonly string $id,
        public readonly string $url,
        public readonly string $gateway,
    ) {}
}
```

### PaymentResult

```php
<?php

namespace App\DTOs;

class PaymentResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $transactionId,
        public readonly string $message,
    ) {}
}
```

---

## Models

### Subscription Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'plan_id',
        'gateway',
        'gateway_subscription_id',
        'gateway_customer_id',
        'status',
        'current_period_start',
        'current_period_end',
        'cancel_at_period_end',
        'canceled_at',
    ];

    protected $casts = [
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'canceled_at' => 'datetime',
        'cancel_at_period_end' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function plan(): array
    {
        return config("subscription.plans.{$this->plan_id}");
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'trialing']);
    }

    public function isCanceled(): bool
    {
        return $this->status === 'canceled';
    }

    public function onGracePeriod(): bool
    {
        return $this->cancel_at_period_end && $this->current_period_end->isFuture();
    }

    public function hasFeature(string $feature): bool
    {
        $plan = $this->plan();
        return in_array($feature, $plan['features'] ?? []);
    }

    public function getLimit(string $resource): int
    {
        $plan = $this->plan();
        return $plan['limits'][$resource] ?? 0;
    }
}
```

### Payment Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'subscription_id',
        'amount',
        'currency',
        'status',
        'gateway_payment_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
```

---

## Configuration

### config/subscription.php

```php
<?php

return [
    'default_gateway' => env('PAYMENT_GATEWAY', 'stripe'),

    'plans' => [
        'free' => [
            'name' => 'Free',
            'description' => 'Perfect for getting started',
            'price' => [
                'monthly' => 0,
                'yearly' => 0,
            ],
            'features' => [
                '3 Landing Pages',
                'Basic Templates',
                'Community Support',
                'Basic Analytics',
            ],
            'limits' => [
                'landing_pages' => 3,
                'custom_domains' => 0,
                'team_members' => 1,
                'monthly_visitors' => 1000,
            ],
        ],
        'pro' => [
            'name' => 'Pro',
            'description' => 'For growing businesses',
            'price' => [
                'monthly' => 19,
                'yearly' => 190, // 2 months free
            ],
            'features' => [
                '25 Landing Pages',
                'Premium Templates',
                'Priority Support',
                'Advanced Analytics',
                'Custom Domains',
                'A/B Testing',
                'Remove Branding',
            ],
            'limits' => [
                'landing_pages' => 25,
                'custom_domains' => 3,
                'team_members' => 5,
                'monthly_visitors' => 50000,
            ],
        ],
        'business' => [
            'name' => 'Business',
            'description' => 'For teams and agencies',
            'price' => [
                'monthly' => 49,
                'yearly' => 490, // 2 months free
            ],
            'features' => [
                'Unlimited Landing Pages',
                'All Templates',
                'Dedicated Support',
                'Advanced Analytics',
                'Unlimited Custom Domains',
                'A/B Testing',
                'White Label',
                'API Access',
                'Team Collaboration',
            ],
            'limits' => [
                'landing_pages' => -1, // unlimited
                'custom_domains' => -1,
                'team_members' => -1,
                'monthly_visitors' => -1,
            ],
        ],
    ],

    'stripe_prices' => [
        'pro' => [
            'monthly' => env('STRIPE_PRO_MONTHLY_PRICE_ID'),
            'yearly' => env('STRIPE_PRO_YEARLY_PRICE_ID'),
        ],
        'business' => [
            'monthly' => env('STRIPE_BUSINESS_MONTHLY_PRICE_ID'),
            'yearly' => env('STRIPE_BUSINESS_YEARLY_PRICE_ID'),
        ],
    ],

    'paypal_plans' => [
        'pro' => [
            'monthly' => env('PAYPAL_PRO_MONTHLY_PLAN_ID'),
            'yearly' => env('PAYPAL_PRO_YEARLY_PLAN_ID'),
        ],
        'business' => [
            'monthly' => env('PAYPAL_BUSINESS_MONTHLY_PLAN_ID'),
            'yearly' => env('PAYPAL_BUSINESS_YEARLY_PLAN_ID'),
        ],
    ],
];
```

---

## Services

### SubscriptionService

```php
<?php

namespace App\Services;

use App\Models\User;
use App\Models\Subscription;
use App\Services\Payment\PaymentContext;
use App\DTOs\CheckoutSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    public function __construct(
        protected PaymentContext $paymentContext
    ) {}

    /**
     * Get user's current subscription
     */
    public function getCurrentSubscription(User $user): ?Subscription
    {
        return $user->subscriptions()
            ->whereIn('status', ['active', 'trialing', 'past_due'])
            ->latest()
            ->first();
    }

    /**
     * Get user's current plan
     */
    public function getCurrentPlan(User $user): array
    {
        $subscription = $this->getCurrentSubscription($user);

        if (!$subscription) {
            return config('subscription.plans.free');
        }

        return $subscription->plan();
    }

    /**
     * Check if user has active subscription
     */
    public function hasActiveSubscription(User $user): bool
    {
        return $this->getCurrentSubscription($user)?->isActive() ?? false;
    }

    /**
     * Create checkout session for subscription
     */
    public function createCheckout(User $user, string $planId, string $billingCycle, string $gateway = null): CheckoutSession
    {
        if ($gateway) {
            $this->paymentContext->setGateway($gateway);
        }

        // Check if plan exists
        if (!config("subscription.plans.{$planId}")) {
            throw new \InvalidArgumentException("Invalid plan: {$planId}");
        }

        // Check if user already has this plan
        $currentSubscription = $this->getCurrentSubscription($user);
        if ($currentSubscription && $currentSubscription->plan_id === $planId) {
            throw new \Exception("You are already subscribed to this plan.");
        }

        return $this->paymentContext->createCheckoutSession($user, $planId, $billingCycle);
    }

    /**
     * Upgrade or downgrade subscription
     */
    public function changePlan(User $user, string $newPlanId): Subscription
    {
        $subscription = $this->getCurrentSubscription($user);

        if (!$subscription) {
            throw new \Exception("No active subscription found.");
        }

        // Set the correct gateway
        $this->paymentContext->setGateway($subscription->gateway);

        // Check if changing to a different plan
        if ($subscription->plan_id === $newPlanId) {
            throw new \Exception("You are already on this plan.");
        }

        return DB::transaction(function () use ($subscription, $newPlanId) {
            $updatedSubscription = $this->paymentContext->updateSubscription(
                $subscription->gateway_subscription_id,
                $newPlanId
            );

            // Log the plan change
            activity()
                ->performedOn($subscription)
                ->withProperties([
                    'old_plan' => $subscription->plan_id,
                    'new_plan' => $newPlanId,
                ])
                ->log('subscription_plan_changed');

            return $updatedSubscription;
        });
    }

    /**
     * Cancel subscription
     */
    public function cancel(User $user, string $reason = null): bool
    {
        $subscription = $this->getCurrentSubscription($user);

        if (!$subscription) {
            throw new \Exception("No active subscription found.");
        }

        $this->paymentContext->setGateway($subscription->gateway);

        $canceled = $this->paymentContext->cancelSubscription($subscription->gateway_subscription_id);

        if ($canceled) {
            $subscription->update([
                'cancel_at_period_end' => true,
                'canceled_at' => now(),
            ]);

            // Log cancellation
            activity()
                ->performedOn($subscription)
                ->withProperties(['reason' => $reason])
                ->log('subscription_canceled');

            // Send cancellation email
            $user->notify(new \App\Notifications\SubscriptionCanceled($subscription));
        }

        return $canceled;
    }

    /**
     * Resume canceled subscription
     */
    public function resume(User $user): bool
    {
        $subscription = $this->getCurrentSubscription($user);

        if (!$subscription || !$subscription->cancel_at_period_end) {
            throw new \Exception("No canceled subscription found to resume.");
        }

        $this->paymentContext->setGateway($subscription->gateway);

        // For Stripe, we need to update the subscription
        if ($subscription->gateway === 'stripe') {
            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
            $stripe->subscriptions->update($subscription->gateway_subscription_id, [
                'cancel_at_period_end' => false,
            ]);
        }

        $subscription->update([
            'cancel_at_period_end' => false,
            'canceled_at' => null,
        ]);

        return true;
    }

    /**
     * Get subscription history
     */
    public function getHistory(User $user): array
    {
        $subscriptions = $user->subscriptions()
            ->with('payments')
            ->orderBy('created_at', 'desc')
            ->get();

        return $subscriptions->map(function ($subscription) {
            return [
                'id' => $subscription->id,
                'plan' => $subscription->plan(),
                'status' => $subscription->status,
                'gateway' => $subscription->gateway,
                'started_at' => $subscription->created_at,
                'current_period_end' => $subscription->current_period_end,
                'canceled_at' => $subscription->canceled_at,
                'payments' => $subscription->payments->map(function ($payment) {
                    return [
                        'amount' => $payment->amount,
                        'currency' => $payment->currency,
                        'status' => $payment->status,
                        'date' => $payment->created_at,
                    ];
                }),
            ];
        })->toArray();
    }

    /**
     * Check usage limit for a resource
     */
    public function checkLimit(User $user, string $resource): array
    {
        $plan = $this->getCurrentPlan($user);
        $limit = $plan['limits'][$resource] ?? 0;

        // Unlimited
        if ($limit === -1) {
            return [
                'allowed' => true,
                'limit' => -1,
                'used' => $this->getUsage($user, $resource),
                'remaining' => -1,
            ];
        }

        $used = $this->getUsage($user, $resource);
        $remaining = max(0, $limit - $used);

        return [
            'allowed' => $remaining > 0,
            'limit' => $limit,
            'used' => $used,
            'remaining' => $remaining,
        ];
    }

    /**
     * Check if user can perform action based on limits
     */
    public function canUse(User $user, string $resource, int $amount = 1): bool
    {
        $limitCheck = $this->checkLimit($user, $resource);

        if ($limitCheck['limit'] === -1) {
            return true;
        }

        return $limitCheck['remaining'] >= $amount;
    }

    /**
     * Get current usage for a resource
     */
    protected function getUsage(User $user, string $resource): int
    {
        return match ($resource) {
            'landing_pages' => $user->landingPages()->count(),
            'custom_domains' => $user->customDomains()->count(),
            'team_members' => $user->team?->members()->count() ?? 1,
            'monthly_visitors' => $this->getMonthlyVisitors($user),
            default => 0,
        };
    }

    /**
     * Get monthly visitors count
     */
    protected function getMonthlyVisitors(User $user): int
    {
        return $user->landingPages()
            ->withSum(['analytics' => function ($query) {
                $query->where('created_at', '>=', now()->startOfMonth());
            }], 'visitors')
            ->get()
            ->sum('analytics_sum_visitors') ?? 0;
    }
}
```

---

## Controllers

### SubscriptionController

```php
<?php

namespace App\Http\Controllers;

use App\Services\SubscriptionService;
use App\Services\Payment\PaymentContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function __construct(
        protected SubscriptionService $subscriptionService,
        protected PaymentContext $paymentContext
    ) {}

    /**
     * Show pricing page
     */
    public function pricing()
    {
        $plans = config('subscription.plans');
        $currentPlan = $this->subscriptionService->getCurrentPlan(Auth::user());

        return view('subscription.pricing', [
            'plans' => $plans,
            'currentPlan' => $currentPlan,
        ]);
    }

    /**
     * Create checkout session
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'plan' => 'required|string|in:pro,business',
            'billing_cycle' => 'required|string|in:monthly,yearly',
            'gateway' => 'nullable|string|in:stripe,paypal',
        ]);

        try {
            $session = $this->subscriptionService->createCheckout(
                Auth::user(),
                $request->plan,
                $request->billing_cycle,
                $request->gateway
            );

            return response()->json([
                'checkout_url' => $session->url,
                'session_id' => $session->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Handle successful checkout
     */
    public function success(Request $request)
    {
        return view('subscription.success');
    }

    /**
     * Handle canceled checkout
     */
    public function cancel()
    {
        return view('subscription.cancel');
    }

    /**
     * Show subscription management page
     */
    public function manage()
    {
        $user = Auth::user();
        $subscription = $this->subscriptionService->getCurrentSubscription($user);
        $currentPlan = $this->subscriptionService->getCurrentPlan($user);
        $plans = config('subscription.plans');
        $history = $this->subscriptionService->getHistory($user);

        // Get usage limits
        $usage = [
            'landing_pages' => $this->subscriptionService->checkLimit($user, 'landing_pages'),
            'custom_domains' => $this->subscriptionService->checkLimit($user, 'custom_domains'),
            'team_members' => $this->subscriptionService->checkLimit($user, 'team_members'),
            'monthly_visitors' => $this->subscriptionService->checkLimit($user, 'monthly_visitors'),
        ];

        return view('subscription.manage', [
            'subscription' => $subscription,
            'currentPlan' => $currentPlan,
            'plans' => $plans,
            'history' => $history,
            'usage' => $usage,
        ]);
    }

    /**
     * Change subscription plan
     */
    public function changePlan(Request $request)
    {
        $request->validate([
            'plan' => 'required|string|in:free,pro,business',
        ]);

        try {
            // Downgrade to free
            if ($request->plan === 'free') {
                $this->subscriptionService->cancel(Auth::user(), 'Downgraded to free plan');

                return response()->json([
                    'message' => 'Your subscription will be canceled at the end of the billing period.',
                ]);
            }

            $subscription = $this->subscriptionService->changePlan(
                Auth::user(),
                $request->plan
            );

            return response()->json([
                'message' => 'Plan changed successfully.',
                'subscription' => $subscription,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Cancel subscription
     */
    public function cancelSubscription(Request $request)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $this->subscriptionService->cancel(Auth::user(), $request->reason);

            return response()->json([
                'message' => 'Your subscription has been canceled. You will retain access until the end of your billing period.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Resume canceled subscription
     */
    public function resume()
    {
        try {
            $this->subscriptionService->resume(Auth::user());

            return response()->json([
                'message' => 'Your subscription has been resumed.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get billing history
     */
    public function billingHistory()
    {
        $history = $this->subscriptionService->getHistory(Auth::user());

        return response()->json($history);
    }
}
```

### PaymentController

```php
<?php

namespace App\Http\Controllers;

use App\Services\Payment\PaymentContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentContext $paymentContext
    ) {}

    /**
     * Handle Stripe webhook
     */
    public function stripeWebhook(Request $request)
    {
        $payload = $request->all();
        $signature = $request->header('Stripe-Signature');

        try {
            $this->paymentContext->setGateway('stripe');
            $this->paymentContext->handleWebhook($payload, $signature);

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Stripe webhook error', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Handle PayPal webhook
     */
    public function paypalWebhook(Request $request)
    {
        $payload = $request->all();
        $signature = $request->header('PAYPAL-TRANSMISSION-SIG');

        try {
            $this->paymentContext->setGateway('paypal');
            $this->paymentContext->handleWebhook($payload, $signature);

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('PayPal webhook error', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Handle PayPal subscription approval
     */
    public function paypalSuccess(Request $request)
    {
        $subscriptionId = $request->get('subscription_id');

        // The subscription activation is handled by the webhook
        return redirect()->route('subscription.success');
    }
}
```

---

## Routes

### routes/web.php

```php
<?php

use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\PaymentController;

// Public pricing page
Route::get('/pricing', [SubscriptionController::class, 'pricing'])->name('pricing');

// Authenticated subscription routes
Route::middleware(['auth'])->group(function () {
    Route::prefix('subscription')->name('subscription.')->group(function () {
        Route::post('/checkout', [SubscriptionController::class, 'checkout'])->name('checkout');
        Route::get('/success', [SubscriptionController::class, 'success'])->name('success');
        Route::get('/cancel', [SubscriptionController::class, 'cancel'])->name('cancel');
        Route::get('/manage', [SubscriptionController::class, 'manage'])->name('manage');
        Route::post('/change-plan', [SubscriptionController::class, 'changePlan'])->name('change-plan');
        Route::post('/cancel', [SubscriptionController::class, 'cancelSubscription'])->name('cancel-subscription');
        Route::post('/resume', [SubscriptionController::class, 'resume'])->name('resume');
        Route::get('/history', [SubscriptionController::class, 'billingHistory'])->name('history');

        // PayPal return URL
        Route::get('/paypal/success', [PaymentController::class, 'paypalSuccess'])->name('paypal.success');
    });
});

// Webhook routes (no auth, but verified by signature)
Route::post('/webhooks/stripe', [PaymentController::class, 'stripeWebhook'])->name('webhooks.stripe');
Route::post('/webhooks/paypal', [PaymentController::class, 'paypalWebhook'])->name('webhooks.paypal');
```

---

## Views

### Pricing Page (resources/views/subscription/pricing.blade.php)

```blade
<x-app-layout>
    <div class="py-12" x-data="pricingPage()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">
                    Choose Your Plan
                </h1>
                <p class="text-xl text-gray-600 mb-8">
                    Start free and scale as you grow
                </p>

                <!-- Billing Toggle -->
                <div class="flex items-center justify-center gap-4">
                    <span
                        class="text-sm font-medium"
                        :class="billingCycle === 'monthly' ? 'text-gray-900' : 'text-gray-500'"
                    >
                        Monthly
                    </span>
                    <button
                        type="button"
                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
                        :class="billingCycle === 'yearly' ? 'bg-indigo-600' : 'bg-gray-200'"
                        @click="billingCycle = billingCycle === 'monthly' ? 'yearly' : 'monthly'"
                    >
                        <span
                            class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                            :class="billingCycle === 'yearly' ? 'translate-x-6' : 'translate-x-1'"
                        ></span>
                    </button>
                    <span
                        class="text-sm font-medium"
                        :class="billingCycle === 'yearly' ? 'text-gray-900' : 'text-gray-500'"
                    >
                        Yearly
                        <span class="text-green-600 font-semibold">(Save 17%)</span>
                    </span>
                </div>
            </div>

            <!-- Pricing Cards -->
            <div class="grid md:grid-cols-3 gap-8">
                @foreach(['free', 'pro', 'business'] as $planKey)
                    @php $plan = $plans[$planKey]; @endphp
                    <div
                        class="relative rounded-2xl border-2 p-8 shadow-sm transition-all hover:shadow-lg"
                        :class="{
                            'border-indigo-600 ring-2 ring-indigo-600': '{{ $planKey }}' === 'pro',
                            'border-gray-200': '{{ $planKey }}' !== 'pro'
                        }"
                    >
                        @if($planKey === 'pro')
                            <div class="absolute -top-4 left-1/2 -translate-x-1/2">
                                <span class="bg-indigo-600 text-white px-4 py-1 rounded-full text-sm font-medium">
                                    Most Popular
                                </span>
                            </div>
                        @endif

                        <div class="text-center">
                            <h3 class="text-xl font-bold text-gray-900">{{ $plan['name'] }}</h3>
                            <p class="mt-2 text-sm text-gray-500">{{ $plan['description'] }}</p>

                            <div class="mt-6">
                                <span class="text-4xl font-bold text-gray-900">
                                    $<span x-text="billingCycle === 'monthly' ? '{{ $plan['price']['monthly'] }}' : '{{ round($plan['price']['yearly'] / 12) }}'"></span>
                                </span>
                                <span class="text-gray-500">/month</span>

                                <template x-if="billingCycle === 'yearly' && {{ $plan['price']['yearly'] }} > 0">
                                    <p class="mt-1 text-sm text-gray-500">
                                        Billed annually (${{ $plan['price']['yearly'] }}/year)
                                    </p>
                                </template>
                            </div>
                        </div>

                        <ul class="mt-8 space-y-3">
                            @foreach($plan['features'] as $feature)
                                <li class="flex items-center gap-3">
                                    <svg class="h-5 w-5 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <span class="text-sm text-gray-600">{{ $feature }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <div class="mt-8">
                            @if($planKey === 'free')
                                @if($currentPlan['name'] === 'Free')
                                    <button
                                        disabled
                                        class="w-full rounded-lg bg-gray-100 px-4 py-3 text-sm font-semibold text-gray-400 cursor-not-allowed"
                                    >
                                        Current Plan
                                    </button>
                                @else
                                    <a
                                        href="{{ route('subscription.manage') }}"
                                        class="block w-full rounded-lg bg-gray-100 px-4 py-3 text-center text-sm font-semibold text-gray-900 hover:bg-gray-200"
                                    >
                                        Downgrade
                                    </a>
                                @endif
                            @else
                                @if($currentPlan['name'] === $plan['name'])
                                    <button
                                        disabled
                                        class="w-full rounded-lg bg-gray-100 px-4 py-3 text-sm font-semibold text-gray-400 cursor-not-allowed"
                                    >
                                        Current Plan
                                    </button>
                                @else
                                    <button
                                        @click="subscribe('{{ $planKey }}')"
                                        :disabled="loading"
                                        class="w-full rounded-lg px-4 py-3 text-sm font-semibold transition-colors disabled:opacity-50"
                                        :class="{
                                            'bg-indigo-600 text-white hover:bg-indigo-700': '{{ $planKey }}' === 'pro',
                                            'bg-gray-900 text-white hover:bg-gray-800': '{{ $planKey }}' !== 'pro'
                                        }"
                                    >
                                        <span x-show="!loading">
                                            {{ $currentPlan['price']['monthly'] < $plan['price']['monthly'] ? 'Upgrade' : 'Subscribe' }}
                                        </span>
                                        <span x-show="loading" x-cloak>Processing...</span>
                                    </button>
                                @endif
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Gateway Selection Modal -->
            <div
                x-show="showGatewayModal"
                x-cloak
                class="fixed inset-0 z-50 overflow-y-auto"
                @keydown.escape.window="showGatewayModal = false"
            >
                <div class="flex min-h-screen items-center justify-center p-4">
                    <div
                        class="fixed inset-0 bg-black/50"
                        @click="showGatewayModal = false"
                    ></div>

                    <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            Choose Payment Method
                        </h3>

                        <div class="space-y-3">
                            <button
                                @click="checkout('stripe')"
                                class="w-full flex items-center justify-center gap-3 rounded-lg border-2 border-gray-200 px-4 py-3 hover:border-indigo-600 transition-colors"
                            >
                                <svg class="h-8 w-8" viewBox="0 0 32 32">
                                    <path fill="#6772E5" d="M32 16c0-8.837-7.163-16-16-16S0 7.163 0 16s7.163 16 16 16 16-7.163 16-16z"/>
                                    <path fill="#FFF" d="M15.5 11.5c0-.828.666-1.5 1.5-1.5h.001c.828 0 1.5.666 1.5 1.5v9c0 .828-.666 1.5-1.5 1.5H17c-.828 0-1.5-.666-1.5-1.5v-9z"/>
                                </svg>
                                <span class="font-medium">Pay with Card</span>
                            </button>

                            <button
                                @click="checkout('paypal')"
                                class="w-full flex items-center justify-center gap-3 rounded-lg border-2 border-gray-200 px-4 py-3 hover:border-indigo-600 transition-colors"
                            >
                                <svg class="h-8 w-8" viewBox="0 0 32 32">
                                    <path fill="#003087" d="M12.5 27.5h-4l3-19h5c3.5 0 6 2.5 5.5 6-.5 4.5-4 7-7.5 7h-1l-1 6z"/>
                                    <path fill="#009CDE" d="M23.5 8.5c-.5 4.5-4 7-7.5 7h-1l-1 6h-3l3-19h5c3.5 0 6 2.5 4.5 6z"/>
                                </svg>
                                <span class="font-medium">Pay with PayPal</span>
                            </button>
                        </div>

                        <button
                            @click="showGatewayModal = false"
                            class="mt-4 w-full text-sm text-gray-500 hover:text-gray-700"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function pricingPage() {
            return {
                billingCycle: 'monthly',
                loading: false,
                showGatewayModal: false,
                selectedPlan: null,

                subscribe(plan) {
                    this.selectedPlan = plan;
                    this.showGatewayModal = true;
                },

                async checkout(gateway) {
                    this.loading = true;
                    this.showGatewayModal = false;

                    try {
                        const response = await fetch('{{ route('subscription.checkout') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            },
                            body: JSON.stringify({
                                plan: this.selectedPlan,
                                billing_cycle: this.billingCycle,
                                gateway: gateway,
                            }),
                        });

                        const data = await response.json();

                        if (data.error) {
                            alert(data.error);
                            return;
                        }

                        // Redirect to checkout
                        window.location.href = data.checkout_url;
                    } catch (error) {
                        console.error('Checkout error:', error);
                        alert('Something went wrong. Please try again.');
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
```

### Subscription Management (resources/views/subscription/manage.blade.php)

```blade
<x-app-layout>
    <div class="py-12" x-data="subscriptionManager()">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Current Plan -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Current Plan</h2>

                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900">{{ $currentPlan['name'] }}</h3>
                        <p class="text-gray-500">{{ $currentPlan['description'] }}</p>

                        @if($subscription)
                            <div class="mt-2 flex items-center gap-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $subscription->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $subscription->status === 'past_due' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $subscription->status === 'canceled' ? 'bg-red-100 text-red-800' : '' }}
                                ">
                                    {{ ucfirst($subscription->status) }}
                                </span>

                                @if($subscription->onGracePeriod())
                                    <span class="text-sm text-gray-500">
                                        Cancels on {{ $subscription->current_period_end->format('M d, Y') }}
                                    </span>
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="text-right">
                        <div class="text-3xl font-bold text-gray-900">
                            ${{ $currentPlan['price']['monthly'] }}
                            <span class="text-sm font-normal text-gray-500">/month</span>
                        </div>

                        @if($subscription && $subscription->current_period_end)
                            <p class="text-sm text-gray-500">
                                Next billing: {{ $subscription->current_period_end->format('M d, Y') }}
                            </p>
                        @endif
                    </div>
                </div>

                @if($subscription && $subscription->onGracePeriod())
                    <div class="mt-4 p-4 bg-yellow-50 rounded-lg">
                        <p class="text-sm text-yellow-800">
                            Your subscription has been canceled and will end on {{ $subscription->current_period_end->format('M d, Y') }}.
                        </p>
                        <button
                            @click="resumeSubscription"
                            class="mt-2 text-sm font-medium text-yellow-800 underline hover:no-underline"
                        >
                            Resume subscription
                        </button>
                    </div>
                @endif
            </div>

            <!-- Usage Limits -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Usage</h2>

                <div class="space-y-4">
                    @foreach($usage as $resource => $data)
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-600">{{ ucwords(str_replace('_', ' ', $resource)) }}</span>
                                <span class="text-gray-900 font-medium">
                                    @if($data['limit'] === -1)
                                        {{ $data['used'] }} / Unlimited
                                    @else
                                        {{ $data['used'] }} / {{ $data['limit'] }}
                                    @endif
                                </span>
                            </div>

                            @if($data['limit'] !== -1)
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div
                                        class="h-2 rounded-full transition-all
                                            {{ ($data['used'] / $data['limit']) >= 0.9 ? 'bg-red-500' : '' }}
                                            {{ ($data['used'] / $data['limit']) >= 0.7 && ($data['used'] / $data['limit']) < 0.9 ? 'bg-yellow-500' : '' }}
                                            {{ ($data['used'] / $data['limit']) < 0.7 ? 'bg-indigo-600' : '' }}
                                        "
                                        style="width: {{ min(100, ($data['used'] / $data['limit']) * 100) }}%"
                                    ></div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if(collect($usage)->contains(fn($data) => $data['limit'] !== -1 && ($data['used'] / $data['limit']) >= 0.9))
                    <div class="mt-4 p-4 bg-indigo-50 rounded-lg">
                        <p class="text-sm text-indigo-800">
                            You're approaching your usage limits.
                            <a href="{{ route('pricing') }}" class="font-medium underline">Upgrade your plan</a>
                            to get more resources.
                        </p>
                    </div>
                @endif
            </div>

            <!-- Change Plan -->
            @if($subscription && !$subscription->onGracePeriod())
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Change Plan</h2>

                    <div class="grid sm:grid-cols-3 gap-4">
                        @foreach(['free', 'pro', 'business'] as $planKey)
                            @php $plan = $plans[$planKey]; @endphp
                            <div
                                class="border rounded-lg p-4 cursor-pointer transition-all"
                                :class="{
                                    'border-indigo-600 bg-indigo-50': selectedPlan === '{{ $planKey }}',
                                    'border-gray-200 hover:border-gray-300': selectedPlan !== '{{ $planKey }}',
                                    'opacity-50 cursor-not-allowed': '{{ $planKey }}' === '{{ $subscription->plan_id }}'
                                }"
                                @click="{{ $planKey !== $subscription->plan_id ? "selectedPlan = '$planKey'" : '' }}"
                            >
                                <h4 class="font-medium text-gray-900">{{ $plan['name'] }}</h4>
                                <p class="text-sm text-gray-500">${{ $plan['price']['monthly'] }}/mo</p>

                                @if($planKey === $subscription->plan_id)
                                    <span class="text-xs text-indigo-600 font-medium">Current</span>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 flex justify-end">
                        <button
                            @click="changePlan"
                            :disabled="!selectedPlan || selectedPlan === '{{ $subscription->plan_id }}' || loading"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span x-show="!loading">Change Plan</span>
                            <span x-show="loading" x-cloak>Processing...</span>
                        </button>
                    </div>
                </div>
            @endif

            <!-- Billing History -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Billing History</h2>

                @if(count($history) > 0 && isset($history[0]['payments']) && count($history[0]['payments']) > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="text-left text-sm text-gray-500 border-b">
                                    <th class="pb-3 font-medium">Date</th>
                                    <th class="pb-3 font-medium">Amount</th>
                                    <th class="pb-3 font-medium">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach($history[0]['payments'] as $payment)
                                    <tr>
                                        <td class="py-3 text-sm text-gray-900">
                                            {{ \Carbon\Carbon::parse($payment['date'])->format('M d, Y') }}
                                        </td>
                                        <td class="py-3 text-sm text-gray-900">
                                            ${{ number_format($payment['amount'], 2) }} {{ strtoupper($payment['currency']) }}
                                        </td>
                                        <td class="py-3">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                                {{ $payment['status'] === 'succeeded' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}
                                            ">
                                                {{ ucfirst($payment['status']) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-gray-500">No billing history available.</p>
                @endif
            </div>

            <!-- Cancel Subscription -->
            @if($subscription && $subscription->isActive() && !$subscription->onGracePeriod())
                <div class="bg-white rounded-xl shadow-sm border border-red-200 p-6">
                    <h2 class="text-xl font-semibold text-red-600 mb-2">Cancel Subscription</h2>
                    <p class="text-sm text-gray-600 mb-4">
                        Once you cancel, you'll retain access until the end of your current billing period.
                    </p>

                    <button
                        @click="showCancelModal = true"
                        class="px-4 py-2 border border-red-300 text-red-600 rounded-lg text-sm font-medium hover:bg-red-50"
                    >
                        Cancel Subscription
                    </button>
                </div>
            @endif
        </div>

        <!-- Cancel Modal -->
        <div
            x-show="showCancelModal"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
        >
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/50" @click="showCancelModal = false"></div>

                <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        Cancel your subscription?
                    </h3>
                    <p class="text-sm text-gray-600 mb-4">
                        We're sorry to see you go. Please let us know why you're leaving.
                    </p>

                    <textarea
                        x-model="cancelReason"
                        rows="3"
                        placeholder="Your feedback helps us improve..."
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm mb-4"
                    ></textarea>

                    <div class="flex gap-3">
                        <button
                            @click="showCancelModal = false"
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50"
                        >
                            Keep Subscription
                        </button>
                        <button
                            @click="cancelSubscription"
                            :disabled="loading"
                            class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 disabled:opacity-50"
                        >
                            <span x-show="!loading">Cancel</span>
                            <span x-show="loading" x-cloak>Processing...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function subscriptionManager() {
            return {
                loading: false,
                selectedPlan: null,
                showCancelModal: false,
                cancelReason: '',

                async changePlan() {
                    if (!this.selectedPlan) return;

                    this.loading = true;

                    try {
                        const response = await fetch('{{ route('subscription.change-plan') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            },
                            body: JSON.stringify({
                                plan: this.selectedPlan,
                            }),
                        });

                        const data = await response.json();

                        if (data.error) {
                            alert(data.error);
                            return;
                        }

                        window.location.reload();
                    } catch (error) {
                        console.error('Change plan error:', error);
                        alert('Something went wrong. Please try again.');
                    } finally {
                        this.loading = false;
                    }
                },

                async cancelSubscription() {
                    this.loading = true;

                    try {
                        const response = await fetch('{{ route('subscription.cancel-subscription') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            },
                            body: JSON.stringify({
                                reason: this.cancelReason,
                            }),
                        });

                        const data = await response.json();

                        if (data.error) {
                            alert(data.error);
                            return;
                        }

                        window.location.reload();
                    } catch (error) {
                        console.error('Cancel error:', error);
                        alert('Something went wrong. Please try again.');
                    } finally {
                        this.loading = false;
                        this.showCancelModal = false;
                    }
                },

                async resumeSubscription() {
                    this.loading = true;

                    try {
                        const response = await fetch('{{ route('subscription.resume') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            },
                        });

                        const data = await response.json();

                        if (data.error) {
                            alert(data.error);
                            return;
                        }

                        window.location.reload();
                    } catch (error) {
                        console.error('Resume error:', error);
                        alert('Something went wrong. Please try again.');
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
```

### Success Page (resources/views/subscription/success.blade.php)

```blade
<x-app-layout>
    <div class="py-12">
        <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>

                <h1 class="text-2xl font-bold text-gray-900 mb-2">Payment Successful!</h1>
                <p class="text-gray-600 mb-6">
                    Thank you for your subscription. Your account has been upgraded successfully.
                </p>

                <div class="space-y-3">
                    <a
                        href="{{ route('subscription.manage') }}"
                        class="block w-full px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700"
                    >
                        View Subscription
                    </a>
                    <a
                        href="{{ route('dashboard') }}"
                        class="block w-full px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50"
                    >
                        Go to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

### Cancel Page (resources/views/subscription/cancel.blade.php)

```blade
<x-app-layout>
    <div class="py-12">
        <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>

                <h1 class="text-2xl font-bold text-gray-900 mb-2">Payment Canceled</h1>
                <p class="text-gray-600 mb-6">
                    Your payment was not completed. No charges have been made.
                </p>

                <div class="space-y-3">
                    <a
                        href="{{ route('pricing') }}"
                        class="block w-full px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700"
                    >
                        Try Again
                    </a>
                    <a
                        href="{{ route('dashboard') }}"
                        class="block w-full px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50"
                    >
                        Return to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

---

## Middleware

### CheckSubscriptionLimit Middleware

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\SubscriptionService;

class CheckSubscriptionLimit
{
    public function __construct(
        protected SubscriptionService $subscriptionService
    ) {}

    public function handle(Request $request, Closure $next, string $resource, int $amount = 1)
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$this->subscriptionService->canUse($user, $resource, $amount)) {
            $limit = $this->subscriptionService->checkLimit($user, $resource);

            return response()->json([
                'error' => "You have reached your {$resource} limit ({$limit['used']}/{$limit['limit']}). Please upgrade your plan.",
                'upgrade_url' => route('pricing'),
            ], 403);
        }

        return $next($request);
    }
}
```

### Usage in Routes

```php
// Protect routes with subscription limits
Route::middleware(['auth', 'subscription.limit:landing_pages'])->group(function () {
    Route::post('/landing-pages', [LandingPageController::class, 'store']);
});

Route::middleware(['auth', 'subscription.limit:custom_domains'])->group(function () {
    Route::post('/domains', [DomainController::class, 'store']);
});
```

---

## Database Migrations

### subscriptions table

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('plan_id');
            $table->string('gateway'); // stripe, paypal
            $table->string('gateway_subscription_id')->unique();
            $table->string('gateway_customer_id')->nullable();
            $table->string('status'); // active, canceled, past_due, etc.
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->boolean('cancel_at_period_end')->default(false);
            $table->timestamp('canceled_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
```

### payments table

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('usd');
            $table->string('status'); // succeeded, failed, pending
            $table->string('gateway_payment_id')->nullable();
            $table->timestamps();

            $table->index('subscription_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
```

---

## Testing

### Feature Test Example

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_pricing_page(): void
    {
        $response = $this->get('/pricing');
        $response->assertStatus(200);
        $response->assertSee('Free');
        $response->assertSee('Pro');
        $response->assertSee('Business');
    }

    public function test_authenticated_user_can_create_checkout_session(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/subscription/checkout', [
            'plan' => 'pro',
            'billing_cycle' => 'monthly',
            'gateway' => 'stripe',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['checkout_url', 'session_id']);
    }

    public function test_usage_limits_are_enforced(): void
    {
        $user = User::factory()->create();
        $service = app(SubscriptionService::class);

        // Free plan allows 3 landing pages
        $limit = $service->checkLimit($user, 'landing_pages');

        $this->assertEquals(3, $limit['limit']);
        $this->assertTrue($limit['allowed']);
    }

    public function test_user_with_pro_plan_has_higher_limits(): void
    {
        $user = User::factory()->create();

        Subscription::factory()->create([
            'user_id' => $user->id,
            'plan_id' => 'pro',
            'status' => 'active',
        ]);

        $service = app(SubscriptionService::class);
        $limit = $service->checkLimit($user, 'landing_pages');

        $this->assertEquals(25, $limit['limit']);
    }
}
```

---

## Environment Variables

```env
# Payment Gateway
PAYMENT_GATEWAY=stripe

# Stripe
STRIPE_KEY=pk_test_xxx
STRIPE_SECRET=sk_test_xxx
STRIPE_WEBHOOK_SECRET=whsec_xxx
STRIPE_PRO_MONTHLY_PRICE_ID=price_xxx
STRIPE_PRO_YEARLY_PRICE_ID=price_xxx
STRIPE_BUSINESS_MONTHLY_PRICE_ID=price_xxx
STRIPE_BUSINESS_YEARLY_PRICE_ID=price_xxx

# PayPal
PAYPAL_CLIENT_ID=xxx
PAYPAL_CLIENT_SECRET=xxx
PAYPAL_WEBHOOK_ID=xxx
PAYPAL_SANDBOX=true
PAYPAL_PRO_MONTHLY_PLAN_ID=P-xxx
PAYPAL_PRO_YEARLY_PLAN_ID=P-xxx
PAYPAL_BUSINESS_MONTHLY_PLAN_ID=P-xxx
PAYPAL_BUSINESS_YEARLY_PLAN_ID=P-xxx
```

---

## Summary

This payments feature implements:

1. **Strategy Pattern** - Flexible payment gateway switching (Stripe/PayPal)
2. **Subscription Plans** - Free ($0), Pro ($19), Business ($49)
3. **Pricing Page** - Interactive plan cards with monthly/yearly toggle
4. **Checkout Flow** - Secure gateway-specific checkout sessions
5. **Subscription Management** - Upgrade, downgrade, cancel, resume
6. **Webhook Handling** - Automated subscription and payment updates
7. **Usage Limits** - Resource tracking and enforcement via middleware
8. **Billing History** - Payment records and receipts

The architecture follows Laravel best practices with service classes, DTOs, and clean separation of concerns.
