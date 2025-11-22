<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Services\PageRenderer;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class PublishController extends Controller
{
    public function __construct(
        protected PageRenderer $renderer
    ) {}

    public function publish(Page $page): JsonResponse
    {
        $this->authorize('update', $page);

        $page->update([
            'status' => 'published',
            'published_at' => now(),
            'published_content' => $page->content,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Page published successfully',
            'published_at' => $page->published_at->toISOString(),
        ]);
    }

    public function unpublish(Page $page): JsonResponse
    {
        $this->authorize('update', $page);

        $page->update([
            'status' => 'draft',
            'published_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Page unpublished successfully',
        ]);
    }

    public function show(string $slug): View
    {
        $page = Page::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $page->increment('views');

        $html = $this->renderer->render($page->published_content ?? $page->content ?? []);

        return view('pages.public', compact('page', 'html'));
    }
}
