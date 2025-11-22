# Day 12 - Step 1: Feature Gating & Billing UI

## Objective
Implement plan-based feature limits and billing management.

## Tasks

### 1.1 Plan Configuration
```php
// config/plans.php
return [
    'free' => [
        'name' => 'Free',
        'price' => 0,
        'pages' => 1,
        'storage' => 100 * 1024 * 1024, // 100MB
        'custom_domain' => false,
    ],
    'pro' => [
        'name' => 'Pro',
        'price' => 19,
        'pages' => 10,
        'storage' => 5 * 1024 * 1024 * 1024, // 5GB
        'custom_domain' => true,
    ],
    'business' => [
        'name' => 'Business',
        'price' => 49,
        'pages' => -1, // unlimited
        'storage' => 50 * 1024 * 1024 * 1024, // 50GB
        'custom_domain' => true,
    ],
];
```

### 1.2 Feature Gate Middleware
```php
// app/Http/Middleware/CheckPlanLimit.php
public function handle(Request $request, Closure $next, string $feature)
{
    $user = auth()->user();
    $plan = config("plans.{$user->subscription_tier}");

    if ($feature === 'pages' && $plan['pages'] !== -1) {
        if ($user->pages()->count() >= $plan['pages']) {
            return redirect()->route('subscription.upgrade')
                ->with('error', 'Page limit reached');
        }
    }

    return $next($request);
}
```

### 1.3 Pricing Page
Display plans with features comparison:
- Plan cards with pricing
- Feature checkmarks
- Current plan indicator
- Upgrade/downgrade buttons

### 1.4 Billing History
- List past invoices
- Download invoice PDFs
- Update payment method

### 1.5 Upsell Prompts
Show upgrade prompts when limits approached.

## Reference Documentation
- `docs/features/06-PAYMENTS.md` - Plan tiers, feature gating
- `docs/04-IMPLEMENTATION-FLOW.md` - Day 12 billing UI

## Expected Deliverables
- [x] Feature gating working
- [x] Pricing page
- [x] Billing history view
- [x] Invoice downloads
- [x] Upsell modals

## Day 12 Complete - Milestone M4: Monetization Live
→ Proceed to Day 13: Performance & Security
