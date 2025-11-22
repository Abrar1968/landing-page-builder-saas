<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function __construct(
        protected AnalyticsService $analyticsService
    ) {}

    public function index(): View
    {
        $pages = auth()->user()->pages()->withCount('pageViews')->latest()->get();
        $userStats = $this->analyticsService->getUserStats(auth()->id());
        
        return view('dashboard.analytics.index', compact('pages', 'userStats'));
    }

    public function show(Page $page): View
    {
        $this->authorize('view', $page);
        
        $stats = $this->analyticsService->getPageStats($page);
        $viewsByDay = $this->analyticsService->getViewsByDay($page);
        $referrers = $this->analyticsService->getTopReferrers($page);
        $devices = $this->analyticsService->getDeviceBreakdown($page);
        $browsers = $this->analyticsService->getBrowserBreakdown($page);
        
        return view('dashboard.analytics.show', compact(
            'page', 'stats', 'viewsByDay', 'referrers', 'devices', 'browsers'
        ));
    }

    public function data(Page $page, Request $request): JsonResponse
    {
        $this->authorize('view', $page);
        
        $days = $request->input('days', 30);
        
        return response()->json([
            'stats' => $this->analyticsService->getPageStats($page, $days),
            'views_by_day' => $this->analyticsService->getViewsByDay($page, $days),
            'referrers' => $this->analyticsService->getTopReferrers($page, $days),
            'devices' => $this->analyticsService->getDeviceBreakdown($page, $days),
            'browsers' => $this->analyticsService->getBrowserBreakdown($page, $days),
        ]);
    }
}
