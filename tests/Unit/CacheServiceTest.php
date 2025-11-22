<?php

namespace Tests\Unit;

use App\Models\Page;
use App\Models\Template;
use App\Models\User;
use App\Services\CacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CacheServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CacheService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CacheService();
        Cache::flush();
    }

    public function test_get_user_pages_caches_result(): void
    {
        $user = User::factory()->create();
        Page::factory()->count(3)->create(['user_id' => $user->id]);

        // First call should hit database
        $pages = $this->service->getUserPages($user->id);
        $this->assertCount(3, $pages);

        // Second call should hit cache
        $cachedPages = $this->service->getUserPages($user->id);
        $this->assertCount(3, $cachedPages);
        $this->assertTrue(Cache::has("user.{$user->id}.pages"));
    }

    public function test_forget_user_pages_clears_cache(): void
    {
        $user = User::factory()->create();
        Page::factory()->create(['user_id' => $user->id]);

        $this->service->getUserPages($user->id);
        $this->assertTrue(Cache::has("user.{$user->id}.pages"));

        $this->service->forgetUserPages($user->id);
        $this->assertFalse(Cache::has("user.{$user->id}.pages"));
    }

    public function test_get_user_stats_returns_correct_counts(): void
    {
        $user = User::factory()->create();
        Page::factory()->count(2)->create(['user_id' => $user->id]);

        $stats = $this->service->getUserStats($user->id);

        $this->assertEquals(2, $stats['pages_count']);
        $this->assertEquals(0, $stats['media_count']);
    }

    public function test_get_featured_templates_caches_result(): void
    {
        Template::factory()->count(5)->create([
            'is_featured' => true,
            'is_public' => true,
        ]);

        $templates = $this->service->getFeaturedTemplates();
        $this->assertCount(5, $templates);
        $this->assertTrue(Cache::has('templates.featured'));
    }

    public function test_get_published_page_returns_null_for_unpublished(): void
    {
        $page = Page::factory()->create([
            'is_published' => false,
            'slug' => 'test-page',
        ]);

        $result = $this->service->getPublishedPage('test-page');

        $this->assertNull($result);
    }

    public function test_get_published_page_returns_page_for_published(): void
    {
        $page = Page::factory()->create([
            'is_published' => true,
            'slug' => 'test-page',
        ]);

        $result = $this->service->getPublishedPage('test-page');

        $this->assertNotNull($result);
        $this->assertEquals($page->id, $result->id);
    }
}
