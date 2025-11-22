<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SubscriptionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SubscriptionService();
    }

    public function test_get_current_plan_returns_free_for_user_without_subscription(): void
    {
        $user = User::factory()->create();

        $plan = $this->service->getCurrentPlan($user);

        $this->assertEquals('free', $plan);
    }

    public function test_get_current_plan_returns_subscription_plan(): void
    {
        $user = User::factory()->create();
        Subscription::factory()->create([
            'user_id' => $user->id,
            'plan' => 'pro',
            'status' => 'active',
        ]);

        $plan = $this->service->getCurrentPlan($user);

        $this->assertEquals('pro', $plan);
    }

    public function test_can_create_page_returns_true_when_under_limit(): void
    {
        $user = User::factory()->create();

        $canCreate = $this->service->canCreatePage($user);

        $this->assertTrue($canCreate);
    }

    public function test_can_create_page_returns_false_when_at_limit(): void
    {
        $user = User::factory()->create();
        \App\Models\Page::factory()->create(['user_id' => $user->id]);

        $canCreate = $this->service->canCreatePage($user);

        $this->assertFalse($canCreate);
    }

    public function test_get_usage_stats_returns_correct_data(): void
    {
        $user = User::factory()->create();

        $stats = $this->service->getUsageStats($user);

        $this->assertArrayHasKey('pages', $stats);
        $this->assertArrayHasKey('storage', $stats);
        $this->assertArrayHasKey('custom_domains', $stats);
        $this->assertEquals(0, $stats['pages']['used']);
    }

    public function test_get_plan_limits_returns_free_limits_for_unsubscribed_user(): void
    {
        $user = User::factory()->create();

        $limits = $this->service->getPlanLimits($user);

        $this->assertEquals(1, $limits['pages']);
        $this->assertEquals(100, $limits['storage']);
        $this->assertEquals(0, $limits['custom_domains']);
    }

    public function test_get_plan_limits_returns_pro_limits_for_pro_user(): void
    {
        $user = User::factory()->create();
        Subscription::factory()->create([
            'user_id' => $user->id,
            'plan' => 'pro',
            'status' => 'active',
        ]);

        $limits = $this->service->getPlanLimits($user);

        $this->assertEquals(10, $limits['pages']);
        $this->assertEquals(5120, $limits['storage']);
        $this->assertEquals(3, $limits['custom_domains']);
    }
}
