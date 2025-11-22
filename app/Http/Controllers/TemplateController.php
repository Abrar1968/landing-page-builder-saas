<?php

namespace App\Http\Controllers;

use App\Models\Template;
use App\Models\TemplateCategory;
use App\Models\Page;
use App\Services\TemplateService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class TemplateController extends Controller
{
    public function __construct(
        protected TemplateService $templateService
    ) {}

    /**
     * Display template gallery
     */
    public function index(Request $request): View
    {
        $categories = TemplateCategory::active()
            ->ordered()
            ->withCount(['templates' => fn($q) => $q->public()])
            ->get();

        $templates = $this->templateService->getFilteredTemplates(
            categoryId: $request->get('category'),
            search: $request->get('search'),
            tags: $request->get('tags', []),
            sortBy: $request->get('sort', 'popular'),
            perPage: 12
        );

        return view('templates.index', compact('categories', 'templates'));
    }

    /**
     * Get templates via AJAX for filtering
     */
    public function filter(Request $request): JsonResponse
    {
        $templates = $this->templateService->getFilteredTemplates(
            categoryId: $request->get('category'),
            search: $request->get('search'),
            tags: $request->get('tags', []),
            sortBy: $request->get('sort', 'popular'),
            perPage: $request->get('per_page', 12)
        );

        $html = view('templates.partials.grid', compact('templates'))->render();

        return response()->json([
            'html' => $html,
            'hasMore' => $templates->hasMorePages(),
            'total' => $templates->total(),
        ]);
    }

    /**
     * Show template preview
     */
    public function show(Template $template): JsonResponse
    {
        $template->load(['category', 'tags', 'user']);

        return response()->json([
            'template' => [
                'id' => $template->id,
                'name' => $template->name,
                'description' => $template->description,
                'thumbnail' => $template->thumbnail,
                'preview_image' => $template->preview_image,
                'category' => $template->category?->name ?? 'Uncategorized',
                'tags' => $template->tags->pluck('name'),
                'is_premium' => $template->is_premium,
                'usage_count' => $template->usage_count,
                'rating' => $template->formatted_rating,
                'rating_count' => $template->rating_count,
                'content' => $template->content,
                'settings' => $template->settings,
            ],
        ]);
    }

    /**
     * Apply template to create new page
     */
    public function apply(Request $request, Template $template): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $page = $this->templateService->applyTemplate(
            template: $template,
            user: $request->user(),
            name: $request->name
        );

        return response()->json([
            'success' => true,
            'message' => 'Template applied successfully',
            'page' => [
                'id' => $page->id,
                'slug' => $page->slug,
                'edit_url' => route('builder.edit', $page),
            ],
        ]);
    }

    /**
     * Save page as template
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'page_id' => 'required|exists:pages,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category_id' => 'required|exists:template_categories,id',
            'is_public' => 'boolean',
            'tags' => 'array',
            'tags.*' => 'string|max:50',
        ]);

        $page = Page::findOrFail($validated['page_id']);

        // Check if user owns the page
        if ($page->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $template = $this->templateService->createFromPage(
            page: $page,
            user: $request->user(),
            data: $validated
        );

        return response()->json([
            'success' => true,
            'message' => 'Template saved successfully',
            'template' => [
                'id' => $template->id,
                'name' => $template->name,
                'slug' => $template->slug,
            ],
        ]);
    }

    /**
     * Get user's saved templates
     */
    public function userTemplates(Request $request): JsonResponse
    {
        $templates = $this->templateService->getUserTemplates(
            $request->user(),
            $request->get('per_page', 10)
        );

        return response()->json([
            'templates' => $templates,
        ]);
    }

    /**
     * Delete user's template
     */
    public function destroy(Template $template): JsonResponse
    {
        // Check if user owns the template
        if (!$template->isOwnedBy(auth()->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $template->delete();

        return response()->json([
            'success' => true,
            'message' => 'Template deleted successfully',
        ]);
    }

    /**
     * Get live preview HTML
     */
    public function preview(Template $template): View
    {
        return view('templates.preview', [
            'content' => $template->content,
            'settings' => $template->settings,
        ]);
    }
}
