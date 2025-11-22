<?php

namespace App\Services;

use App\Models\Page;
use App\Models\Template;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class CacheService
{
    protected const TTL_SHORT = 300;      // 5 minutes
    protected const TTL_MEDIUM = 3600;    // 1 hour
    protected const TTL_LONG = 86400;     // 24 hours

    // User pages cache
    public function getUserPages(int $userId)
    {
        return Cache::remember("user.{$userId}.pages", self::TTL_MEDIUM, function () use ($userId) {
            return Page::where('user_id', $userId)
                ->with('domain')
                ->orderByDesc('updated_at')
                ->get();
        });
    }

    public function forgetUserPages(int $userId): void
    {
        Cache::forget("user.{$userId}.pages");
    }

    // User stats cache
    public function getUserStats(int $userId): array
    {
        return Cache::remember("user.{$userId}.stats", self::TTL_SHORT, function () use ($userId) {
            $user = User::find($userId);
            return [
                'pages_count' => $user->pages()->count(),
                'media_count' => $user->media()->count(),
                'storage_used' => $user->media()->sum('size'),
            ];
        });
    }

    public function forgetUserStats(int $userId): void
    {
        Cache::forget("user.{$userId}.stats");
    }

    // Featured templates cache
    public function getFeaturedTemplates()
    {
        return Cache::remember('templates.featured', self::TTL_LONG, function () {
            return Template::where('is_featured', true)
                ->where('is_public', true)
                ->orderByDesc('created_at')
                ->limit(12)
                ->get();
        });
    }

    public function forgetFeaturedTemplates(): void
    {
        Cache::forget('templates.featured');
    }

    // Template categories cache
    public function getTemplateCategories()
    {
        return Cache::remember('templates.categories', self::TTL_LONG, function () {
            return Template::where('is_public', true)
                ->select('category')
                ->distinct()
                ->pluck('category')
                ->filter()
                ->values();
        });
    }

    public function forgetTemplateCategories(): void
    {
        Cache::forget('templates.categories');
    }

    // Public page cache (for published pages)
    public function getPublishedPage(string $slug)
    {
        return Cache::remember("page.published.{$slug}", self::TTL_MEDIUM, function () use ($slug) {
            return Page::where('slug', $slug)
                ->where('is_published', true)
                ->first();
        });
    }

    public function forgetPublishedPage(string $slug): void
    {
        Cache::forget("page.published.{$slug}");
    }

    // Analytics cache
    public function getPageAnalytics(int $pageId, string $period = '7d')
    {
        return Cache::remember("analytics.page.{$pageId}.{$period}", self::TTL_SHORT, function () use ($pageId, $period) {
            $page = Page::find($pageId);
            if (!$page) return null;

            $days = match ($period) {
                '24h' => 1,
                '7d' => 7,
                '30d' => 30,
                default => 7,
            };

            return [
                'total_views' => $page->pageViews()->count(),
                'unique_visitors' => $page->pageViews()->distinct('visitor_id')->count('visitor_id'),
                'views_by_day' => $page->pageViews()
                    ->where('created_at', '>=', now()->subDays($days))
                    ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->pluck('count', 'date'),
            ];
        });
    }

    public function forgetPageAnalytics(int $pageId): void
    {
        Cache::forget("analytics.page.{$pageId}.24h");
        Cache::forget("analytics.page.{$pageId}.7d");
        Cache::forget("analytics.page.{$pageId}.30d");
    }

    // Clear all user-related caches
    public function forgetUserCaches(int $userId): void
    {
        $this->forgetUserPages($userId);
        $this->forgetUserStats($userId);
    }
}
