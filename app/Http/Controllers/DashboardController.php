<?php

namespace App\Http\Controllers;

use App\Repositories\PageRepository;
use App\Services\PageService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected PageService $pageService,
        protected PageRepository $pageRepository
    ) {}

    public function index(): View
    {
        $user = auth()->user();

        $stats = [
            [
                'name' => 'Total Pages',
                'value' => $user->pages()->count(),
                'icon' => 'document-duplicate',
                'change' => '+2',
                'changeType' => 'increase'
            ],
            [
                'name' => 'Published',
                'value' => $user->pages()->where('status', 'published')->count(),
                'icon' => 'check-circle',
                'change' => '+1',
                'changeType' => 'increase'
            ],
            [
                'name' => 'Total Views',
                'value' => number_format($this->pageRepository->getTotalViews($user)),
                'icon' => 'eye',
                'change' => '+12%',
                'changeType' => 'increase'
            ],
            [
                'name' => 'Conversions',
                'value' => $this->pageRepository->getTotalConversions($user),
                'icon' => 'cursor-arrow-rays',
                'change' => '+8%',
                'changeType' => 'increase'
            ],
        ];

        $recentPages = $this->pageRepository->getRecentPages($user, 6);

        return view('dashboard.index', compact('stats', 'recentPages'));
    }
}
