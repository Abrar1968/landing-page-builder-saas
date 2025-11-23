<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Repositories\PageRepository;
use App\Services\PageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function __construct(
        protected PageService $pageService,
        protected PageRepository $pageRepository
    ) {}

    public function index(): View
    {
        $pages = $this->pageRepository->getUserPagesCollection(auth()->user())
            ->map(fn($page) => [
                'id' => $page->id,
                'name' => $page->title,
                'url' => $page->slug,
                'status' => $page->status,
                'views' => $page->pageViews()->count(),
                'lastModified' => $page->updated_at->format('M d, Y'),
            ]);

        return view('dashboard.pages.index', compact('pages'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'template' => 'nullable|string',
        ]);

        $page = $this->pageService->create(auth()->user(), [
            'title' => $validated['name'],
        ]);

        return response()->json([
            'success' => true,
            'id' => $page->id,
            'message' => 'Page created successfully',
        ]);
    }

    public function show(Page $page): View
    {
        $this->authorize('view', $page);

        return view('dashboard.pages.show', compact('page'));
    }

    public function edit(Page $page): RedirectResponse
    {
        $this->authorize('update', $page);

        return redirect()->route('builder.edit', $page);
    }

    public function update(Request $request, Page $page): JsonResponse
    {
        $this->authorize('update', $page);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|array',
            'settings' => 'sometimes|array',
        ]);

        $this->pageService->update($page, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Page updated successfully',
        ]);
    }

    public function destroy(Page $page): JsonResponse
    {
        $this->authorize('delete', $page);

        $this->pageService->delete($page);

        return response()->json([
            'success' => true,
            'message' => 'Page deleted successfully',
        ]);
    }

    public function duplicate(Page $page): JsonResponse
    {
        $this->authorize('view', $page);

        $newPage = $this->pageService->duplicate($page);

        return response()->json([
            'success' => true,
            'id' => $newPage->id,
            'message' => 'Page duplicated successfully',
        ]);
    }

    public function versions(Page $page): View
    {
        $this->authorize('view', $page);

        $versions = $page->versions()->latest()->get();

        return view('dashboard.pages.versions', compact('page', 'versions'));
    }

    public function restoreVersion(Page $page, int $versionId): JsonResponse
    {
        $this->authorize('update', $page);

        $version = $page->versions()->findOrFail($versionId);

        $this->pageService->update($page, [
            'content' => $version->content,
            'settings' => $version->settings,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Version restored successfully',
        ]);
    }
}
