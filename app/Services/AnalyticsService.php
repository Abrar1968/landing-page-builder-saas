<?php

namespace App\Services;

use App\Models\Page;
use App\Models\PageView;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AnalyticsService
{
    public function getPageStats(Page $page, int $days = 30): array
    {
        $startDate = Carbon::now()->subDays($days);
        
        $views = $page->pageViews()->where('created_at', '>=', $startDate);
        
        return [
            'total_views' => $views->count(),
            'unique_visitors' => $views->distinct('visitor_id')->count('visitor_id'),
            'views_today' => $page->pageViews()->whereDate('created_at', Carbon::today())->count(),
            'views_this_week' => $page->pageViews()->where('created_at', '>=', Carbon::now()->startOfWeek())->count(),
        ];
    }

    public function getViewsByDay(Page $page, int $days = 30): Collection
    {
        $startDate = Carbon::now()->subDays($days);
        
        return $page->pageViews()
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as views, COUNT(DISTINCT visitor_id) as unique_visitors')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    public function getTopReferrers(Page $page, int $days = 30, int $limit = 10): Collection
    {
        $startDate = Carbon::now()->subDays($days);
        
        return $page->pageViews()
            ->where('created_at', '>=', $startDate)
            ->whereNotNull('referrer')
            ->where('referrer', '!=', '')
            ->selectRaw('referrer, COUNT(*) as count')
            ->groupBy('referrer')
            ->orderByDesc('count')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                $parsed = parse_url($item->referrer);
                $item->domain = $parsed['host'] ?? $item->referrer;
                return $item;
            });
    }

    public function getDeviceBreakdown(Page $page, int $days = 30): Collection
    {
        $startDate = Carbon::now()->subDays($days);
        
        return $page->pageViews()
            ->where('created_at', '>=', $startDate)
            ->selectRaw('device_type, COUNT(*) as count')
            ->groupBy('device_type')
            ->orderByDesc('count')
            ->get();
    }

    public function getBrowserBreakdown(Page $page, int $days = 30): Collection
    {
        $startDate = Carbon::now()->subDays($days);
        
        return $page->pageViews()
            ->where('created_at', '>=', $startDate)
            ->selectRaw('browser, COUNT(*) as count')
            ->groupBy('browser')
            ->orderByDesc('count')
            ->get();
    }

    public function getUserStats(int $userId, int $days = 30): array
    {
        $startDate = Carbon::now()->subDays($days);
        
        $totalViews = PageView::whereHas('page', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->where('created_at', '>=', $startDate)->count();
        
        $uniqueVisitors = PageView::whereHas('page', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->where('created_at', '>=', $startDate)->distinct('visitor_id')->count('visitor_id');
        
        return [
            'total_views' => $totalViews,
            'unique_visitors' => $uniqueVisitors,
        ];
    }
}
