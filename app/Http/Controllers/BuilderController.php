<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Rules\ValidWidgetStructure;
use App\Services\PageService;
use App\Services\HtmlSanitizer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class BuilderController extends Controller
{
    public function __construct(
        protected PageService $pageService,
        protected HtmlSanitizer $htmlSanitizer
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
            'title' => 'sometimes|string|max:255',
            'content' => ['required', 'array', new ValidWidgetStructure()],
            'settings' => 'nullable|array',
        ]);

        // ✅ Sanitize all WYSIWYG/HTML content before saving
        if (isset($validated['content'])) {
            $validated['content'] = $this->sanitizeWidgetContent($validated['content']);
        }

        // Use PageService for proper architecture pattern
        // PageObserver automatically creates versions when content changes
        $this->pageService->update($page, $validated);

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
            'content' => ['required', 'array', new ValidWidgetStructure()],
            'settings' => 'nullable|array',
        ]);

        // ✅ Sanitize all WYSIWYG/HTML content before saving
        if (isset($validated['content'])) {
            $validated['content'] = $this->sanitizeWidgetContent($validated['content']);
        }

        // Use PageService for proper architecture pattern
        $this->pageService->update($page, $validated);

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

        // Use PageService for proper architecture pattern
        $this->pageService->publish($page);

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

    /**
     * Recursively sanitize HTML content in widgets
     */
    protected function sanitizeWidgetContent(array $content): array
    {
        foreach ($content as &$section) {
            if (isset($section['elements'])) {
                foreach ($section['elements'] as &$column) {
                    if (isset($column['elements'])) {
                        foreach ($column['elements'] as &$widget) {
                            if (isset($widget['settings'])) {
                                $widget['settings'] = $this->sanitizeWidgetSettings(
                                    $widget['widgetType'] ?? '',
                                    $widget['settings']
                                );
                            }
                        }
                    }
                }
            }
        }

        return $content;
    }

    /**
     * Sanitize specific widget settings based on widget type
     */
    protected function sanitizeWidgetSettings(string $widgetType, array $settings): array
    {
        // Fields that contain HTML and need sanitization
        $htmlFields = [
            'editor', 'content', 'description',
            'tab1_content', 'tab2_content', 'tab3_content',
            'item1_content', 'item2_content', 'item3_content',
            'slide1_description', 'slide2_description', 'slide3_description',
        ];

        foreach ($htmlFields as $field) {
            if (isset($settings[$field]) && is_string($settings[$field])) {
                $settings[$field] = $this->htmlSanitizer->sanitize($settings[$field]);
            }
        }

        return $settings;
    }
}
