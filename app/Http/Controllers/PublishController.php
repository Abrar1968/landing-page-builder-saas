<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Services\PageRenderer;
use App\Services\WidgetCssGenerator;
use App\Services\WidgetRenderer;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class PublishController extends Controller
{
    public function __construct(
        protected PageRenderer $renderer,
        protected WidgetCssGenerator $cssGenerator,
        protected WidgetRenderer $widgetRenderer
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

        $content = $page->published_content ?? $page->content ?? [];
        
        // Generate widget CSS for hover and responsive states
        $widgetCss = $this->cssGenerator->generatePageCss($content);
        
        // Render widgets to HTML
        $html = $this->renderPageContent($content);

        return view('pages.public', compact('page', 'html', 'widgetCss'));
    }
    
    /**
     * Render page content with all widgets
     */
    protected function renderPageContent(array $content): string
    {
        $html = '';
        
        foreach ($content as $section) {
            $sectionHtml = '<div class="builder-section">';
            
            if (isset($section['elements']) && is_array($section['elements'])) {
                foreach ($section['elements'] as $column) {
                    $sectionHtml .= '<div class="builder-column">';
                    
                    if (isset($column['elements']) && is_array($column['elements'])) {
                        foreach ($column['elements'] as $widget) {
                            $widgetId = $widget['id'] ?? '';
                            $settings = $widget['settings'] ?? [];
                            $widgetHtml = $this->widgetRenderer->render($widget);
                            
                            // Build wrapper classes
                            $classes = ["widget-{$widgetId}"];
                            if (!empty($settings['css_classes'])) {
                                $classes[] = $settings['css_classes'];
                            }
                            
                            // Build wrapper attributes
                            $attributes = 'class="' . implode(' ', $classes) . '"';
                            if (!empty($settings['css_id'])) {
                                $attributes .= ' id="' . e($settings['css_id']) . '"';
                            }
                            
                            // Wrap widget with ID class for CSS targeting
                            $sectionHtml .= "<div {$attributes}>{$widgetHtml}</div>";
                        }
                    }
                    
                    $sectionHtml .= '</div>';
                }
            }
            
            $sectionHtml .= '</div>';
            $html .= $sectionHtml;
        }
        
        return $html;
    }
}
