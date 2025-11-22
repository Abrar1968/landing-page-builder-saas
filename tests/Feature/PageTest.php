<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_pages_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('pages.index'));

        $response->assertStatus(200);
    }

    public function test_user_can_create_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('pages.store'), [
            'title' => 'Test Page',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pages', [
            'title' => 'Test Page',
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_update_own_page(): void
    {
        $user = User::factory()->create();
        $page = Page::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put(route('pages.update', $page), [
            'title' => 'Updated Title',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pages', [
            'id' => $page->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_user_cannot_update_others_page(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $page = Page::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->put(route('pages.update', $page), [
            'title' => 'Hacked',
        ]);

        $response->assertForbidden();
    }

    public function test_user_can_delete_own_page(): void
    {
        $user = User::factory()->create();
        $page = Page::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete(route('pages.destroy', $page));

        $response->assertRedirect();
        $this->assertDatabaseMissing('pages', ['id' => $page->id]);
    }

    public function test_user_cannot_delete_others_page(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $page = Page::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->delete(route('pages.destroy', $page));

        $response->assertForbidden();
    }

    public function test_user_can_duplicate_page(): void
    {
        $user = User::factory()->create();
        $page = Page::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('pages.duplicate', $page));

        $response->assertRedirect();
        $this->assertDatabaseCount('pages', 2);
    }

    public function test_guest_cannot_access_pages(): void
    {
        $response = $this->get(route('pages.index'));

        $response->assertRedirect(route('login'));
    }
}
