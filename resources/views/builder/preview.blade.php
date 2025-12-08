<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->title }} - Preview</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&family=Roboto:wght@100;300;400;500;700;900&family=Open+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700;800;900&family=Lato:wght@100;300;400;700;900&family=Poppins:wght@100;200;300;400;500;600;700;800;900&family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <!-- Animate.css for motion effects -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    @vite(['resources/css/app.css'])
    <style>
        body {
            margin: 0;
            padding: 0;
        }

        .builder-section {
            width: 100%;
            box-sizing: border-box;
        }

        .builder-container {
            width: 100%;
            box-sizing: border-box;
        }

        .builder-column {
            box-sizing: border-box;
            flex-shrink: 0;
        }
    </style>
</head>

<body>
    @php
        use App\Services\WidgetRenderer;
        use App\Services\HtmlSanitizer;
        $widgetRenderer = new WidgetRenderer(new HtmlSanitizer());

        $content = $page->content ?? [];
    @endphp

    @foreach ($content as $section)
        @php
            $sectionMinHeight = isset($section['settings']['min_height']) ? $section['settings']['min_height'] : null;
            $sectionBgColor = $section['settings']['background_color'] ?? null;

            // Build Section Styles
            $sectionStyles = [];
            if ($sectionMinHeight) {
                $sectionStyles[] = 'min-height: ' . $sectionMinHeight . 'px';
            }
            if ($sectionBgColor) {
                $sectionStyles[] = 'background-color: ' . $sectionBgColor;
            }

            // Background Image
            if (!empty($section['settings']['background_image'])) {
                $sectionStyles[] = "background-image: url('" . $section['settings']['background_image'] . "')";
                $sectionStyles[] = 'background-size: ' . ($section['settings']['background_size'] ?? 'cover');
                $sectionStyles[] = 'background-position: ' . ($section['settings']['background_position'] ?? 'center');
                $sectionStyles[] = 'background-repeat: ' . ($section['settings']['background_repeat'] ?? 'no-repeat');
                if (!empty($section['settings']['background_attachment'])) {
                    $sectionStyles[] = 'background-attachment: ' . $section['settings']['background_attachment'];
                }
            }

            // Border
            if (!empty($section['settings']['border']['type']) && $section['settings']['border']['type'] !== 'none') {
                $b = $section['settings']['border'];
                $width = $b['width'] ?? 1;
                $w = is_array($width) ? $width['top'] ?? 1 : $width; // Simplification
                $color = $b['color'] ?? '#000';
                $sectionStyles[] = "border: {$w}px {$b['type']} {$color}";
            }
            // Border Radius
            if (!empty($section['settings']['border_radius'])) {
                $br = $section['settings']['border_radius'];
                if (is_array($br)) {
                    $unit = $br['unit'] ?? 'px';
                    $sectionStyles[] = "border-radius: {$br['topLeft']}{$unit} {$br['topRight']}{$unit} {$br['bottomRight']}{$unit} {$br['bottomLeft']}{$unit}";
                } else {
                    $sectionStyles[] = "border-radius: {$br}px";
                }
            }

            // Box Shadow
            if (!empty($section['settings']['box_shadow']['color'])) {
                $s = $section['settings']['box_shadow'];
                $h = $s['horizontal'] ?? 0;
                $v = $s['vertical'] ?? 0;
                $b = $s['blur'] ?? 10;
                $sp = $s['spread'] ?? 0;
                $c = $s['color'];
                $pos = !empty($s['position']) && $s['position'] === 'inset' ? 'inset ' : '';
                $sectionStyles[] = "box-shadow: {$pos}{$h}px {$v}px {$b}px {$sp}px {$c}";
            }

            $sectionStyle = implode('; ', $sectionStyles);
        @endphp
        <div class="builder-section" style="{{ $sectionStyle }}">
            @if (isset($section['elements']) && is_array($section['elements']))
                @foreach ($section['elements'] as $container)
                    @php
                        // Container settings
                        $contentWidth = $container['settings']['content_width'] ?? 'boxed';
                        $containerBgColor = $container['settings']['background_color'] ?? null;
                        $containerClasses = 'builder-container w-full';
                        $containerStyle = '';

                        if ($contentWidth === 'boxed') {
                            $containerClasses .= ' max-w-7xl mx-auto px-4';
                        }

                        if ($containerBgColor) {
                            $containerStyle .= 'background-color: ' . $containerBgColor . ';';
                        }

                        // Container padding
                        if (!empty($container['settings']['padding']) && is_array($container['settings']['padding'])) {
                            $p = $container['settings']['padding'];
                            $unit = $p['unit'] ?? 'px';
                            $top = $p['top'] ?? 0;
                            $right = $p['right'] ?? 0;
                            $bottom = $p['bottom'] ?? 0;
                            $left = $p['left'] ?? 0;
                            $containerStyle .=
                                ' padding: ' .
                                $top .
                                $unit .
                                ' ' .
                                $right .
                                $unit .
                                ' ' .
                                $bottom .
                                $unit .
                                ' ' .
                                $left .
                                $unit .
                                ';';
                        }

                        // Container margin
                        if (!empty($container['settings']['margin']) && is_array($container['settings']['margin'])) {
                            $m = $container['settings']['margin'];
                            $unit = $m['unit'] ?? 'px';
                            $top = $m['top'] ?? 0;
                            $right = $m['right'] ?? 0;
                            $bottom = $m['bottom'] ?? 0;
                            $left = $m['left'] ?? 0;
                            $containerStyle .=
                                ' margin: ' .
                                $top .
                                $unit .
                                ' ' .
                                $right .
                                $unit .
                                ' ' .
                                $bottom .
                                $unit .
                                ' ' .
                                $left .
                                $unit .
                                ';';
                        }

                        // Container CSS classes
                        if (!empty($container['settings']['css_classes'])) {
                            $containerClasses .= ' ' . $container['settings']['css_classes'];
                        }

                        // Container Background & Border (Missing parity)
                        $containerStylesList = [];
                        if ($containerStyle) {
                            $containerStylesList[] = trim($containerStyle, '; ');
                        } // Add existing styles

                        if (!empty($container['settings']['background_image'])) {
                            $containerStylesList[] =
                                "background-image: url('" . $container['settings']['background_image'] . "')";
                            $containerStylesList[] =
                                'background-size: ' . ($container['settings']['background_size'] ?? 'cover');
                            $containerStylesList[] =
                                'background-position: ' . ($container['settings']['background_position'] ?? 'center');
                            $containerStylesList[] =
                                'background-repeat: ' . ($container['settings']['background_repeat'] ?? 'no-repeat');
                        }

                        // Border
                        if (
                            !empty($container['settings']['border']['type']) &&
                            $container['settings']['border']['type'] !== 'none'
                        ) {
                            $b = $container['settings']['border'];
                            $width = $b['width'] ?? 1;
                            $w = is_array($width) ? $width['top'] ?? 1 : $width;
                            $color = $b['color'] ?? '#000';
                            $containerStylesList[] = "border: {$w}px {$b['type']} {$color}";
                        }
                        // Radius
                        if (!empty($container['settings']['border_radius'])) {
                            $br = $container['settings']['border_radius'];
                            if (is_array($br)) {
                                $unit = $br['unit'] ?? 'px';
                                $containerStylesList[] = "border-radius: {$br['topLeft']}{$unit} {$br['topRight']}{$unit} {$br['bottomRight']}{$unit} {$br['bottomLeft']}{$unit}";
                            }
                        }
                        // Shadow
                        if (!empty($container['settings']['box_shadow']['color'])) {
                            $s = $container['settings']['box_shadow'];
                            $h = $s['horizontal'] ?? 0;
                            $v = $s['vertical'] ?? 0;
                            $b = $s['blur'] ?? 10;
                            $sp = $s['spread'] ?? 0;
                            $c = $s['color'];
                            $containerStylesList[] = "box-shadow: {$h}px {$v}px {$b}px {$sp}px {$c}";
                        }
                        $containerStyle = implode('; ', $containerStylesList);

                        // Flexbox Settings
                        $columnDirection = $container['settings']['column_direction'] ?? 'row';
                        $flexDirectionClass = $columnDirection === 'column' ? 'flex-col' : 'flex-row';

                        $flexStyles = [];
                        if (!empty($container['settings']['justify_content'])) {
                            $flexStyles[] = 'justify-content: ' . $container['settings']['justify_content'];
                        }
                        if (!empty($container['settings']['align_items'])) {
                            $flexStyles[] = 'align-items: ' . $container['settings']['align_items'];
                        }
                        if (!empty($container['settings']['flex_wrap'])) {
                            $flexStyles[] = 'flex-wrap: ' . $container['settings']['flex_wrap'];
                        }
                        if (!empty($container['settings']['gaps'])) {
                            $gap = $container['settings']['gaps'];
                            if (is_array($gap)) {
                                $flexStyles[] = 'gap: ' . ($gap['row'] ?? 20) . 'px ' . ($gap['column'] ?? 20) . 'px';
                            } else {
                                $flexStyles[] = 'gap: ' . ($gap ?? 20) . 'px';
                            }
                        }
                        $flexStyleString = implode('; ', $flexStyles);
                    @endphp
                    <div class="{{ $containerClasses }}" style="{{ $containerStyle }}">
                        <div class="flex w-full {{ $flexDirectionClass }}" style="{{ $flexStyleString }}">
                            @if (isset($container['elements']) && is_array($container['elements']))
                                @foreach ($container['elements'] as $column)
                                    @php
                                        $columnWidth = $column['settings']['_column_size'] ?? 100;
                                        $columnBgColor = $column['settings']['background_color'] ?? null;
                                        $columnStyle = 'width: ' . $columnWidth . '%;';
                                        if ($columnBgColor) {
                                            $columnStyle .= ' background-color: ' . $columnBgColor . ';';
                                        }

                                        // Add padding from column settings
                                        if (
                                            !empty($column['settings']['padding']) &&
                                            is_array($column['settings']['padding'])
                                        ) {
                                            $p = $column['settings']['padding'];
                                            $unit = $p['unit'] ?? 'px';
                                            $top = $p['top'] ?? 0;
                                            $right = $p['right'] ?? 0;
                                            $bottom = $p['bottom'] ?? 0;
                                            $left = $p['left'] ?? 0;
                                            $columnStyle .=
                                                ' padding: ' .
                                                $top .
                                                $unit .
                                                ' ' .
                                                $right .
                                                $unit .
                                                ' ' .
                                                $bottom .
                                                $unit .
                                                ' ' .
                                                $left .
                                                $unit .
                                                ';';
                                        } else {
                                            $columnStyle .= ' padding: 1rem;'; // Default padding
                                        }

                                        // Add margin from column settings
                                        if (
                                            !empty($column['settings']['margin']) &&
                                            is_array($column['settings']['margin'])
                                        ) {
                                            $m = $column['settings']['margin'];
                                            $unit = $m['unit'] ?? 'px';
                                            $top = $m['top'] ?? 0;
                                            $right = $m['right'] ?? 0;
                                            $bottom = $m['bottom'] ?? 0;
                                            $left = $m['left'] ?? 0;
                                            $columnStyle .=
                                                ' margin: ' .
                                                $top .
                                                $unit .
                                                ' ' .
                                                $right .
                                                $unit .
                                                ' ' .
                                                $bottom .
                                                $unit .
                                                ' ' .
                                                $left .
                                                $unit .
                                                ';';
                                        }

                                        // Add CSS classes from column settings
                                        $columnClasses = 'builder-column';
                                        if (!empty($column['settings']['css_classes'])) {
                                            $columnClasses .= ' ' . $column['settings']['css_classes'];
                                        }

                                        // Add motion effects (entrance animation)
                                        if (!empty($column['settings']['motion_effects']['entrance_animation'])) {
                                            $animation = $column['settings']['motion_effects']['entrance_animation'];
                                            $columnClasses .= ' animate__animated animate__' . $animation;

                                            // Add animation duration if set
                                            if (!empty($column['settings']['motion_effects']['animation_duration'])) {
                                                $duration = $column['settings']['motion_effects']['animation_duration'];
                                                $columnStyle .= ' animation-duration: ' . $duration . 'ms;';
                                            }

                                            // Add animation delay if set
                                            if (!empty($column['settings']['motion_effects']['animation_delay'])) {
                                                $delay = $column['settings']['motion_effects']['animation_delay'];
                                                $columnStyle .= ' animation-delay: ' . $delay . 'ms;';
                                            }
                                        }
                                    @endphp
                                    <div class="{{ $columnClasses }}" style="{{ $columnStyle }}">
                                        @if (isset($column['elements']) && is_array($column['elements']))
                                            @foreach ($column['elements'] as $widget)
                                                {!! $widgetRenderer->render($widget) !!}
                                            @endforeach
                                        @endif
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    @endforeach

    <!-- Motion Effects CSS -->
    <style>
        /* Sticky positioning */
        .sticky-top {
            position: sticky !important;
            top: 0;
            z-index: 100;
        }

        .sticky-bottom {
            position: sticky !important;
            bottom: 0;
            z-index: 100;
        }

        /* Parallax base */
        .parallax-scroll {
            will-change: transform;
            transition: transform 0.1s ease-out;
        }

        /* Widget transition for hover effects */
        [id^="widget-"] {
            transition: background-color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease, color 0.3s ease;
        }
    </style>

    <!-- Parallax Effect Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const parallaxElements = document.querySelectorAll('.parallax-scroll');

            if (parallaxElements.length === 0) return;

            function updateParallax() {
                const viewportHeight = window.innerHeight;
                const viewportCenter = viewportHeight / 2;

                parallaxElements.forEach(function(el) {
                    const rect = el.getBoundingClientRect();
                    const elementCenter = rect.top + rect.height / 2;
                    const distanceFromCenter = elementCenter - viewportCenter;

                    // Get parallax speed from data attribute or use default
                    const speed = parseFloat(el.getAttribute('data-parallax-speed') || '0.5');
                    const offset = distanceFromCenter * (1 - speed) * 0.3;

                    el.style.transform = 'translateY(' + offset + 'px)';
                });
            }

            window.addEventListener('scroll', updateParallax, {
                passive: true
            });
            updateParallax(); // Initial call
        });
    </script>
</body>

</html>
