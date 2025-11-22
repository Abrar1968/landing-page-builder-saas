<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Services\PageService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class BuilderController extends Controller
{
    public function __construct(
        protected PageService $pageService
    ) {}

    /**
     * Show the page builder
     */
    public function edit(Page $page): View
    {
        $this->authorize('update', $page);

        return view('builder.edit', [
            'page' => $page,
            'elements' => config('builder.elements'),
        ]);
    }

    /**
     * Save page content via AJAX
     */
    public function save(Request $request, Page $page): JsonResponse
    {
        $this->authorize('update', $page);

        $validated = $request->validate([
            'content' => 'required|array',
            'settings' => 'nullable|array',
        ]);

        $page->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Page saved successfully',
            'saved_at' => now()->format('g:i A'),
        ]);
    }

    /**
     * Auto-save page content
     */
    public function autosave(Request $request, Page $page): JsonResponse
    {
        $this->authorize('update', $page);

        $validated = $request->validate([
            'content' => 'required|array',
        ]);

        $page->update([
            'content' => $validated['content'],
        ]);

        return response()->json([
            'success' => true,
            'saved_at' => now()->format('g:i A'),
        ]);
    }

    /**
     * Publish the page
     */
    public function publish(Page $page): JsonResponse
    {
        $this->authorize('update', $page);

        $page->publish();

        return response()->json([
            'success' => true,
            'message' => 'Page published successfully',
            'url' => route('page.show', $page->slug),
        ]);
    }

    /**
     * Preview the page
     */
    public function preview(Page $page): View
    {
        $this->authorize('view', $page);

        return view('builder.preview', [
            'page' => $page,
        ]);
    }
}
