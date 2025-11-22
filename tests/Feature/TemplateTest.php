<?php

namespace Tests\Feature;

use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_templates(): void
    {
        $user = User::factory()->create();
        Template::factory()->count(3)->create(['is_public' => true]);

        $response = $this->actingAs($user)->get(route('templates.index'));

        $response->assertStatus(200);
    }

    public function test_user_can_apply_template(): void
    {
        $user = User::factory()->create();
        $template = Template::factory()->create(['is_public' => true]);

        $response = $this->actingAs($user)->post(route('templates.apply', $template));

        $response->assertRedirect();
        $this->assertDatabaseCount('pages', 1);
    }

    public function test_user_can_create_template_from_page(): void
    {
        $user = User::factory()->create();
        $page = \App\Models\Page::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post(route('templates.store'), [
            'page_id' => $page->id,
            'name' => 'My Template',
            'description' => 'A test template',
            'category' => 'landing',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('templates', [
            'name' => 'My Template',
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_delete_own_template(): void
    {
        $user = User::factory()->create();
        $template = Template::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete(route('templates.destroy', $template));

        $response->assertRedirect();
        $this->assertDatabaseMissing('templates', ['id' => $template->id]);
    }

    public function test_user_cannot_delete_system_template(): void
    {
        $user = User::factory()->create();
        $template = Template::factory()->create(['user_id' => null, 'is_public' => true]);

        $response = $this->actingAs($user)->delete(route('templates.destroy', $template));

        $response->assertForbidden();
    }
}
