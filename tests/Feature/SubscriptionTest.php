<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_pricing_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('subscription.pricing'));

        $response->assertStatus(200);
        $response->assertSee('Free');
        $response->assertSee('Pro');
        $response->assertSee('Business');
    }

    public function test_user_can_view_subscription_management(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('subscription.manage'));

        $response->assertStatus(200);
    }

    public function test_free_user_sees_current_plan(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('subscription.pricing'));

        $response->assertStatus(200);
        $response->assertSee('Current Plan');
    }

    public function test_subscribed_user_can_view_billing_history(): void
    {
        $user = User::factory()->create();
        Subscription::factory()->create([
            'user_id' => $user->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get(route('billing.history'));

        $response->assertStatus(200);
    }
}
