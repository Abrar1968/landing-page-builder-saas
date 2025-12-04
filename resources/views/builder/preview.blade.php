<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->title }} - Preview</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&family=Roboto:wght@100;300;400;500;700;900&family=Open+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700;800;900&family=Lato:wght@100;300;400;700;900&family=Poppins:wght@100;200;300;400;500;600;700;800;900&family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Animate.css for motion effects -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    @vite(['resources/css/app.css'])
    <style>
        body { margin: 0; padding: 0; }
        .builder-section { width: 100%; box-sizing: border-box; }
        .builder-container { width: 100%; box-sizing: border-box; }
        .builder-column { box-sizing: border-box; flex-shrink: 0; }
    </style>
</head>
<body>
    @php
        use App\Services\WidgetRenderer;
        use App\Services\HtmlSanitizer;
        $widgetRenderer = new WidgetRenderer(new HtmlSanitizer());

        $content = $page->content ?? [];
    @endphp

    @foreach($content as $section)
        @php
            $sectionMinHeight = isset($section['settings']['min_height']) ? $section['settings']['min_height'] : null;
            $sectionBgColor = isset($section['settings']['background_color']) ? $section['settings']['background_color'] : null;
            $sectionStyle = '';
            if ($sectionMinHeight) {
                $sectionStyle .= 'min-height: ' . $sectionMinHeight . 'px;';
            }
            if ($sectionBgColor) {
                $sectionStyle .= ' background-color: ' . $sectionBgColor . ';';
            }
        @endphp
        <div class="builder-section" style="{{ $sectionStyle }}">
            @if(isset($section['elements']) && is_array($section['elements']))
                @foreach($section['elements'] as $container)
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
                            $containerStyle .= ' padding: ' . $top . $unit . ' ' . $right . $unit . ' ' . $bottom . $unit . ' ' . $left . $unit . ';';
                        }

                        // Container margin
                        if (!empty($container['settings']['margin']) && is_array($container['settings']['margin'])) {
                            $m = $container['settings']['margin'];
                            $unit = $m['unit'] ?? 'px';
                            $top = $m['top'] ?? 0;
                            $right = $m['right'] ?? 0;
                            $bottom = $m['bottom'] ?? 0;
                            $left = $m['left'] ?? 0;
                            $containerStyle .= ' margin: ' . $top . $unit . ' ' . $right . $unit . ' ' . $bottom . $unit . ' ' . $left . $unit . ';';
                        }

                        // Container CSS classes
                        if (!empty($container['settings']['css_classes'])) {
                            $containerClasses .= ' ' . $container['settings']['css_classes'];
                        }

                        // Container column direction
                        $columnDirection = $container['settings']['column_direction'] ?? 'row';
                        $flexDirectionClass = $columnDirection === 'column' ? 'flex-col' : 'flex-row';
                    @endphp
                    <div class="{{ $containerClasses }}" style="{{ $containerStyle }}">
                        <div class="flex w-full {{ $flexDirectionClass }}">
                            @if(isset($container['elements']) && is_array($container['elements']))
                                @foreach($container['elements'] as $column)
                                    @php
                                        $columnWidth = $column['settings']['_column_size'] ?? 100;
                                        $columnBgColor = $column['settings']['background_color'] ?? null;
                                        $columnStyle = 'width: ' . $columnWidth . '%;';
                                        if ($columnBgColor) {
                                            $columnStyle .= ' background-color: ' . $columnBgColor . ';';
                                        }

                                        // Add padding from column settings
                                        if (!empty($column['settings']['padding']) && is_array($column['settings']['padding'])) {
                                            $p = $column['settings']['padding'];
                                            $unit = $p['unit'] ?? 'px';
                                            $top = $p['top'] ?? 0;
                                            $right = $p['right'] ?? 0;
                                            $bottom = $p['bottom'] ?? 0;
                                            $left = $p['left'] ?? 0;
                                            $columnStyle .= ' padding: ' . $top . $unit . ' ' . $right . $unit . ' ' . $bottom . $unit . ' ' . $left . $unit . ';';
                                        } else {
                                            $columnStyle .= ' padding: 1rem;'; // Default padding
                                        }

                                        // Add margin from column settings
                                        if (!empty($column['settings']['margin']) && is_array($column['settings']['margin'])) {
                                            $m = $column['settings']['margin'];
                                            $unit = $m['unit'] ?? 'px';
                                            $top = $m['top'] ?? 0;
                                            $right = $m['right'] ?? 0;
                                            $bottom = $m['bottom'] ?? 0;
                                            $left = $m['left'] ?? 0;
                                            $columnStyle .= ' margin: ' . $top . $unit . ' ' . $right . $unit . ' ' . $bottom . $unit . ' ' . $left . $unit . ';';
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
                                        @if(isset($column['elements']) && is_array($column['elements']))
                                            @foreach($column['elements'] as $widget)
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
</body>
</html>
