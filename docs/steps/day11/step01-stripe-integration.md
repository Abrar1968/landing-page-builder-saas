# Day 11 - Step 1: Stripe Integration

## Objective
Integrate Stripe for subscription payments using Strategy pattern.

## Tasks

### 1.1 Payment Gateway Interface
```php
// app/Contracts/PaymentGateway.php
interface PaymentGateway
{
    public function createCustomer(array $data): string;
    public function createSubscription(string $customerId, string $planId): array;
    public function cancelSubscription(string $subscriptionId): bool;
    public function handleWebhook(array $payload): void;
}
```

### 1.2 Stripe Gateway
```php
// app/Services/Payments/StripeGateway.php
class StripeGateway implements PaymentGateway
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    public function createSubscription(string $customerId, string $planId): array
    {
        $subscription = $this->stripe->subscriptions->create([
            'customer' => $customerId,
            'items' => [['price' => $planId]],
        ]);

        return [
            'id' => $subscription->id,
            'status' => $subscription->status,
        ];
    }
}
```

### 1.3 Payment Service Provider
```php
// app/Providers/PaymentServiceProvider.php
$this->app->bind(PaymentGateway::class, function () {
    return match(config('payments.default')) {
        'stripe' => new StripeGateway(),
        'paypal' => new PayPalGateway(),
        default => new StripeGateway(),
    };
});
```

### 1.4 Subscription Controller
Handle subscribe, upgrade, downgrade, cancel flows.

### 1.5 Webhook Handler
Process Stripe webhooks for payment events.

## Reference Documentation
- `docs/features/06-PAYMENTS.md` - Complete payment specification
- `docs/backend/01-ARCHITECTURE.md` - Strategy pattern
- `docs/04-IMPLEMENTATION-FLOW.md` - Day 11-12 payments

## Expected Deliverables
- [x] Stripe SDK integrated
- [x] PaymentGateway interface
- [x] StripeGateway implementation
- [x] Subscription creation working
- [x] Webhook handling

## Day 11 Complete
→ Proceed to Day 12: Plan Limitations & Billing UI
