<?php

namespace App\Repositories;

use App\Models\Subscription;
use Illuminate\Database\Eloquent\Collection;

class SubscriptionRepository extends BaseRepository
{
    public function __construct(Subscription $model)
    {
        $this->model = $model;
    }

    public function getActiveByUser(int $userId): ?Subscription
    {
        return $this->model
            ->where('user_id', $userId)
            ->active()
            ->first();
    }

    public function getByStripeId(string $stripeSubscriptionId): ?Subscription
    {
        return $this->model
            ->where('stripe_subscription_id', $stripeSubscriptionId)
            ->first();
    }

    public function getExpiringSoon(int $days = 7): Collection
    {
        return $this->model
            ->active()
            ->whereBetween('current_period_end', [now(), now()->addDays($days)])
            ->get();
    }

    public function getByPlan(string $plan): Collection
    {
        return $this->model
            ->where('plan', $plan)
            ->active()
            ->get();
    }
}
