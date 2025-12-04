<?php

namespace App\Services\Payment\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\DTOs\CheckoutSession;
use App\DTOs\PaymentResult;
use App\Models\User;
use App\Models\Subscription;
use App\Models\Payment;
use Stripe\StripeClient;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class StripeGateway implements PaymentGatewayInterface
{
    protected ?StripeClient $stripe = null;

    public function __construct()
    {
        // Only initialize Stripe if we have a valid secret key
        $secret = config('services.stripe.secret');
        if ($secret && !str_contains($secret, 'placeholder')) {
            $this->stripe = new StripeClient($secret);
        }
    }

    protected function getStripe(): StripeClient
    {
        if (!$this->stripe) {
            throw new \RuntimeException('Stripe is not configured. Please set STRIPE_SECRET in your .env file.');
        }
        return $this->stripe;
    }

    public function createCustomer(User $user): string
    {
        $customer = $this->getStripe()->customers->create([
            'email' => $user->email,
            'name' => $user->name,
            'metadata' => ['user_id' => $user->id],
        ]);

        $user->update(['stripe_customer_id' => $customer->id]);

        return $customer->id;
    }

    public function createCheckoutSession(User $user, string $priceId, string $successUrl, string $cancelUrl): CheckoutSession
    {
        $customerId = $user->stripe_customer_id ?? $this->createCustomer($user);

        $session = $this->getStripe()->checkout->sessions->create([
            'customer' => $customerId,
            'mode' => 'subscription',
            'line_items' => [[
                'price' => $priceId,
                'quantity' => 1,
            ]],
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata' => ['user_id' => $user->id],
        ]);

        return CheckoutSession::fromStripe($session);
    }

    public function createBillingPortalSession(User $user, string $returnUrl): string
    {
        $session = $this->getStripe()->billingPortal->sessions->create([
            'customer' => $user->stripe_customer_id,
            'return_url' => $returnUrl,
        ]);

        return $session->url;
    }

    public function cancelSubscription(string $subscriptionId): PaymentResult
    {
        try {
            $this->getStripe()->subscriptions->update($subscriptionId, [
                'cancel_at_period_end' => true,
            ]);

            return PaymentResult::success($subscriptionId, 'Subscription will cancel at period end');
        } catch (\Exception $e) {
            return PaymentResult::failure($e->getMessage());
        }
    }

    public function resumeSubscription(string $subscriptionId): PaymentResult
    {
        try {
            $this->getStripe()->subscriptions->update($subscriptionId, [
                'cancel_at_period_end' => false,
            ]);

            return PaymentResult::success($subscriptionId, 'Subscription resumed');
        } catch (\Exception $e) {
            return PaymentResult::failure($e->getMessage());
        }
    }

    public function handleWebhook(string $payload, string $signature): PaymentResult
    {
        try {
            $event = Webhook::constructEvent(
                $payload,
                $signature,
                config('services.stripe.webhook_secret')
            );
        } catch (SignatureVerificationException $e) {
            return PaymentResult::failure('Invalid signature');
        }

        switch ($event->type) {
            case 'checkout.session.completed':
                return $this->handleCheckoutComplete($event->data->object);

            case 'customer.subscription.updated':
                return $this->handleSubscriptionUpdated($event->data->object);

            case 'customer.subscription.deleted':
                return $this->handleSubscriptionDeleted($event->data->object);

            case 'invoice.payment_succeeded':
                return $this->handlePaymentSucceeded($event->data->object);

            case 'invoice.payment_failed':
                return $this->handlePaymentFailed($event->data->object);

            default:
                return PaymentResult::success($event->id, 'Event ignored');
        }
    }

    public function getSubscription(string $subscriptionId): ?array
    {
        try {
            $subscription = $this->getStripe()->subscriptions->retrieve($subscriptionId);
            return [
                'id' => $subscription->id,
                'status' => $subscription->status,
                'current_period_end' => $subscription->current_period_end,
                'cancel_at_period_end' => $subscription->cancel_at_period_end,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function handleCheckoutComplete(object $session): PaymentResult
    {
        $user = User::find($session->metadata->user_id);
        if (!$user) {
            return PaymentResult::failure('User not found');
        }

        $stripeSubscription = $this->getStripe()->subscriptions->retrieve($session->subscription);
        $plan = $this->getPlanFromPriceId($stripeSubscription->items->data[0]->price->id);

        Subscription::updateOrCreate(
            ['user_id' => $user->id],
            [
                'stripe_subscription_id' => $session->subscription,
                'stripe_price_id' => $stripeSubscription->items->data[0]->price->id,
                'plan' => $plan,
                'status' => 'active',
                'current_period_start' => now(),
                'current_period_end' => \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_end),
            ]
        );

        return PaymentResult::success($session->id, 'Subscription created');
    }

    protected function handleSubscriptionUpdated(object $subscription): PaymentResult
    {
        $sub = Subscription::where('stripe_subscription_id', $subscription->id)->first();
        if (!$sub) {
            return PaymentResult::failure('Subscription not found');
        }

        $sub->update([
            'status' => $subscription->status,
            'current_period_end' => \Carbon\Carbon::createFromTimestamp($subscription->current_period_end),
            'cancel_at_period_end' => $subscription->cancel_at_period_end,
        ]);

        return PaymentResult::success($subscription->id, 'Subscription updated');
    }

    protected function handleSubscriptionDeleted(object $subscription): PaymentResult
    {
        $sub = Subscription::where('stripe_subscription_id', $subscription->id)->first();
        if ($sub) {
            $sub->update(['status' => 'canceled']);
        }

        return PaymentResult::success($subscription->id, 'Subscription canceled');
    }

    protected function handlePaymentSucceeded(object $invoice): PaymentResult
    {
        $subscription = Subscription::where('stripe_subscription_id', $invoice->subscription)->first();
        if (!$subscription) {
            return PaymentResult::failure('Subscription not found');
        }

        Payment::create([
            'user_id' => $subscription->user_id,
            'subscription_id' => $subscription->id,
            'stripe_payment_id' => $invoice->payment_intent,
            'amount' => $invoice->amount_paid,
            'currency' => $invoice->currency,
            'status' => 'succeeded',
            'paid_at' => now(),
        ]);

        return PaymentResult::success($invoice->id, 'Payment recorded');
    }

    protected function handlePaymentFailed(object $invoice): PaymentResult
    {
        $subscription = Subscription::where('stripe_subscription_id', $invoice->subscription)->first();
        if ($subscription) {
            $subscription->update(['status' => 'past_due']);
        }

        return PaymentResult::success($invoice->id, 'Payment failure recorded');
    }

    protected function getPlanFromPriceId(string $priceId): string
    {
        $plans = config('subscription.plans');
        foreach ($plans as $name => $plan) {
            if ($plan['stripe_price_id'] === $priceId) {
                return $name;
            }
        }
        return 'free';
    }
}
