<?php

namespace App\Services;

use App\Models\User;
use App\Models\Subscription;
use App\Services\Payment\PaymentContext;
use App\DTOs\CheckoutSession;

class SubscriptionService
{
    protected PaymentContext $payment;

    public function __construct()
    {
        $this->payment = new PaymentContext();
    }

    public function createCheckoutSession(User $user, string $plan): CheckoutSession
    {
        $planConfig = config("subscription.plans.{$plan}");

        if (!$planConfig || !isset($planConfig['stripe_price_id'])) {
            throw new \InvalidArgumentException("Invalid plan: {$plan}");
        }

        return $this->payment->createCheckoutSession(
            $user,
            $planConfig['stripe_price_id'],
            route('subscription.success'),
            route('subscription.cancel')
        );
    }

    public function getBillingPortalUrl(User $user): string
    {
        return $this->payment->createBillingPortalSession(
            $user,
            route('subscription.manage')
        );
    }

    public function cancel(User $user): bool
    {
        $subscription = $user->subscription;
        if (!$subscription) {
            return false;
        }

        $result = $this->payment->cancelSubscription($subscription->stripe_subscription_id);

        if ($result->success) {
            $subscription->update(['cancel_at_period_end' => true]);
        }

        return $result->success;
    }

    public function resume(User $user): bool
    {
        $subscription = $user->subscription;
        if (!$subscription) {
            return false;
        }

        $result = $this->payment->resumeSubscription($subscription->stripe_subscription_id);

        if ($result->success) {
            $subscription->update(['cancel_at_period_end' => false]);
        }

        return $result->success;
    }

    public function getCurrentPlan(User $user): string
    {
        return $user->subscription?->plan ?? 'free';
    }

    public function getPlanLimits(User $user): array
    {
        $plan = $this->getCurrentPlan($user);
        return config("subscription.plans.{$plan}.limits", config('subscription.plans.free.limits'));
    }

    public function canCreatePage(User $user): bool
    {
        $limits = $this->getPlanLimits($user);
        $currentPages = $user->pages()->count();

        return $limits['pages'] === -1 || $currentPages < $limits['pages'];
    }

    public function canUploadMedia(User $user, int $fileSize): bool
    {
        $limits = $this->getPlanLimits($user);
        $currentStorage = $user->media()->sum('size');

        $maxStorage = $limits['storage'] * 1024 * 1024; // Convert MB to bytes

        return ($currentStorage + $fileSize) <= $maxStorage;
    }

    public function getUsageStats(User $user): array
    {
        $limits = $this->getPlanLimits($user);

        return [
            'pages' => [
                'used' => $user->pages()->count(),
                'limit' => $limits['pages'],
                'unlimited' => $limits['pages'] === -1,
            ],
            'storage' => [
                'used' => $user->media()->sum('size'),
                'limit' => $limits['storage'] * 1024 * 1024,
                'used_mb' => round($user->media()->sum('size') / (1024 * 1024), 2),
                'limit_mb' => $limits['storage'],
            ],
            'custom_domains' => [
                'used' => $user->domains()->count(),
                'limit' => $limits['custom_domains'],
                'allowed' => $limits['custom_domains'] > 0,
            ],
        ];
    }
}
