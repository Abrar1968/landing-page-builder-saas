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
            // Section styles
            $sectionStyles = [];
            if (!empty($section['settings']['min_height'])) {
                $sectionStyles[] = "min-height: {$section['settings']['min_height']}px";
            }
            if (!empty($section['settings']['background_color'])) {
                $sectionStyles[] = "background-color: {$section['settings']['background_color']}";
            }
            $sectionStyleAttr = !empty($sectionStyles) ? ' style="' . implode('; ', $sectionStyles) . '"' : '';

            $sectionHtml = "<div class=\"builder-section\"{$sectionStyleAttr}>";

            if (isset($section['elements']) && is_array($section['elements'])) {
                foreach ($section['elements'] as $container) {
                    // Container styles and classes
                    $containerStyles = [];
                    $containerClasses = ['builder-container', 'w-full'];

                    // Container width
                    $contentWidth = $container['settings']['content_width'] ?? 'boxed';
                    if ($contentWidth === 'boxed') {
                        $containerClasses[] = 'max-w-7xl';
                        $containerClasses[] = 'mx-auto';
                        $containerClasses[] = 'px-4';
                    }

                    // Container background
                    if (!empty($container['settings']['background_color'])) {
                        $containerStyles[] = "background-color: {$container['settings']['background_color']}";
                    }

                    // Container padding
                    if (!empty($container['settings']['padding']) && is_array($container['settings']['padding'])) {
                        $p = $container['settings']['padding'];
                        $unit = $p['unit'] ?? 'px';
                        $top = $p['top'] ?? 0;
                        $right = $p['right'] ?? 0;
                        $bottom = $p['bottom'] ?? 0;
                        $left = $p['left'] ?? 0;
                        $containerStyles[] = "padding: {$top}{$unit} {$right}{$unit} {$bottom}{$unit} {$left}{$unit}";
                    }

                    // Container margin
                    if (!empty($container['settings']['margin']) && is_array($container['settings']['margin'])) {
                        $m = $container['settings']['margin'];
                        $unit = $m['unit'] ?? 'px';
                        $top = $m['top'] ?? 0;
                        $right = $m['right'] ?? 0;
                        $bottom = $m['bottom'] ?? 0;
                        $left = $m['left'] ?? 0;
                        $containerStyles[] = "margin: {$top}{$unit} {$right}{$unit} {$bottom}{$unit} {$left}{$unit}";
                    }

                    // Container CSS classes
                    if (!empty($container['settings']['css_classes'])) {
                        $containerClasses[] = $container['settings']['css_classes'];
                    }

                    // Container column direction
                    $columnDirection = $container['settings']['column_direction'] ?? 'row';
                    $flexDirectionClass = $columnDirection === 'column' ? 'flex-col' : 'flex-row';

                    $containerStyleAttr = !empty($containerStyles) ? ' style="' . implode('; ', $containerStyles) . '"' : '';
                    $containerClassAttr = ' class="' . implode(' ', $containerClasses) . '"';

                    $sectionHtml .= "<div{$containerClassAttr}{$containerStyleAttr}>";
                    $sectionHtml .= "<div class=\"flex w-full {$flexDirectionClass}\">";

                    // Now loop through columns inside container
                    if (isset($container['elements']) && is_array($container['elements'])) {
                        foreach ($container['elements'] as $column) {
                            // Column styles
                            $columnStyles = [];
                            $columnWidth = $column['settings']['_column_size'] ?? 100;
                            $columnStyles[] = "width: {$columnWidth}%";

                            if (!empty($column['settings']['background_color'])) {
                                $columnStyles[] = "background-color: {$column['settings']['background_color']}";
                            }

                            // Column padding
                            if (!empty($column['settings']['padding']) && is_array($column['settings']['padding'])) {
                                $p = $column['settings']['padding'];
                                $unit = $p['unit'] ?? 'px';
                                $top = $p['top'] ?? 0;
                                $right = $p['right'] ?? 0;
                                $bottom = $p['bottom'] ?? 0;
                                $left = $p['left'] ?? 0;
                                $columnStyles[] = "padding: {$top}{$unit} {$right}{$unit} {$bottom}{$unit} {$left}{$unit}";
                            }

                            // Column margin
                            if (!empty($column['settings']['margin']) && is_array($column['settings']['margin'])) {
                                $m = $column['settings']['margin'];
                                $unit = $m['unit'] ?? 'px';
                                $top = $m['top'] ?? 0;
                                $right = $m['right'] ?? 0;
                                $bottom = $m['bottom'] ?? 0;
                                $left = $m['left'] ?? 0;
                                $columnStyles[] = "margin: {$top}{$unit} {$right}{$unit} {$bottom}{$unit} {$left}{$unit}";
                            }

                            $columnStyleAttr = !empty($columnStyles) ? ' style="' . implode('; ', $columnStyles) . '"' : '';

                            // Column CSS classes
                            $columnClasses = ['builder-column'];
                            if (!empty($column['settings']['css_classes'])) {
                                $columnClasses[] = $column['settings']['css_classes'];
                            }

                            // Add motion effects (entrance animation)
                            if (!empty($column['settings']['motion_effects']['entrance_animation'])) {
                                $animation = $column['settings']['motion_effects']['entrance_animation'];
                                $columnClasses[] = 'animate__animated';
                                $columnClasses[] = 'animate__' . $animation;

                                // Add animation duration if set
                                if (!empty($column['settings']['motion_effects']['animation_duration'])) {
                                    $duration = $column['settings']['motion_effects']['animation_duration'];
                                    $columnStyles[] = "animation-duration: {$duration}ms";
                                }

                                // Add animation delay if set
                                if (!empty($column['settings']['motion_effects']['animation_delay'])) {
                                    $delay = $column['settings']['motion_effects']['animation_delay'];
                                    $columnStyles[] = "animation-delay: {$delay}ms";
                                }
                            }

                            $columnClassAttr = ' class="' . implode(' ', $columnClasses) . '"';

                            $sectionHtml .= "<div{$columnClassAttr}{$columnStyleAttr}>";

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

                            $sectionHtml .= '</div>'; // Close column
                        }
                    }

                    $sectionHtml .= '</div>'; // Close flex wrapper
                    $sectionHtml .= '</div>'; // Close container
                }
            }

            $sectionHtml .= '</div>'; // Close section
            $html .= $sectionHtml;
        }

        return $html;
    }
}
