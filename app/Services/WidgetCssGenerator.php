<?php

namespace App\Services;

class WidgetCssGenerator
{
    /**
     * Generate complete CSS for a single widget including hover and responsive states
     */
    public function generateCss(array $widget, string $widgetId): string
    {
        $css = '';
        $settings = $widget['settings'] ?? [];
        $hoverSettings = $widget['hover_settings'] ?? [];

        // Generate base widget CSS
        $css .= $this->generateWidgetCss($widgetId, $settings);

        // Generate hover state CSS
        if (!empty($hoverSettings)) {
            $css .= $this->generateHoverCss($widgetId, $hoverSettings);
        }

        // Generate responsive CSS
        $css .= $this->generateResponsiveCss($widgetId, $settings);

        return $css;
    }

    /**
     * Generate CSS for all widgets in page content
     */
    public function generatePageCss(array $content): string
    {
        $css = '';

        foreach ($content as $section) {
            if (isset($section['elements']) && is_array($section['elements'])) {
                foreach ($section['elements'] as $column) {
                    if (isset($column['elements']) && is_array($column['elements'])) {
                        foreach ($column['elements'] as $widget) {
                            if (isset($widget['id'])) {
                                $css .= $this->generateCss($widget, $widget['id']);
                            }
                        }
                    }
                }
            }
        }

        return $css;
    }

    /**
     * Generate base CSS for a widget
     */
    protected function generateWidgetCss(string $widgetId, array $settings): string
    {
        $selector = ".widget-{$widgetId}";
        $styles = [];

        // Typography
        if (isset($settings['typography']) && is_array($settings['typography'])) {
            $typo = $settings['typography'];
            if (!empty($typo['family'])) {
                $styles[] = "font-family: " . $this->sanitizeCssValue($typo['family']);
            }
            if (!empty($typo['size'])) {
                $unit = $typo['sizeUnit'] ?? 'px';
                $styles[] = "font-size: {$typo['size']}{$unit}";
            }
            if (!empty($typo['weight'])) {
                $styles[] = "font-weight: {$typo['weight']}";
            }
            if (!empty($typo['lineHeight'])) {
                $styles[] = "line-height: {$typo['lineHeight']}";
            }
            if (!empty($typo['letterSpacing'])) {
                $unit = $typo['letterSpacingUnit'] ?? 'px';
                $styles[] = "letter-spacing: {$typo['letterSpacing']}{$unit}";
            }
        }

        // Background
        if (isset($settings['background']) && is_array($settings['background'])) {
            $bg = $settings['background'];
            if (($bg['type'] ?? '') === 'classic' && !empty($bg['color'])) {
                $styles[] = "background-color: " . $this->sanitizeCssValue($bg['color']);
            } elseif (($bg['type'] ?? '') === 'gradient') {
                $angle = $bg['gradientAngle'] ?? 180;
                $color1 = $this->sanitizeCssValue($bg['gradientColor1'] ?? '#000000');
                $color2 = $this->sanitizeCssValue($bg['gradientColor2'] ?? '#ffffff');
                $styles[] = "background: linear-gradient({$angle}deg, {$color1}, {$color2})";
            }
        }

        // Border
        if (isset($settings['border']) && is_array($settings['border'])) {
            $border = $settings['border'];
            if (($border['style'] ?? 'none') !== 'none') {
                $styles[] = "border-style: " . $this->sanitizeCssValue($border['style'] ?? 'solid');
                if (!empty($border['color'])) {
                    $styles[] = "border-color: " . $this->sanitizeCssValue($border['color']);
                }
                $width = $border['width'] ?? 1;
                $styles[] = "border-width: {$width}px";
            }
            if (!empty($border['radius'])) {
                $radius = $border['radius'];
                $unit = $border['radiusUnit'] ?? 'px';
                
                // Handle both scalar and array radius values
                if (is_array($radius)) {
                    // Individual corner radii
                    $top = $radius['top'] ?? 0;
                    $right = $radius['right'] ?? 0;
                    $bottom = $radius['bottom'] ?? 0;
                    $left = $radius['left'] ?? 0;
                    $styles[] = "border-radius: {$top}{$unit} {$right}{$unit} {$bottom}{$unit} {$left}{$unit}";
                } else {
                    // Uniform radius
                    $styles[] = "border-radius: {$radius}{$unit}";
                }
            }
        }

        // Margin
        if (isset($settings['margin']) && is_array($settings['margin'])) {
            $margin = $settings['margin'];
            $unit = $margin['unit'] ?? 'px';
            $top = $margin['top'] ?? 0;
            $right = $margin['right'] ?? 0;
            $bottom = $margin['bottom'] ?? 0;
            $left = $margin['left'] ?? 0;
            $styles[] = "margin: {$top}{$unit} {$right}{$unit} {$bottom}{$unit} {$left}{$unit}";
        }

        // Padding
        if (isset($settings['padding']) && is_array($settings['padding'])) {
            $padding = $settings['padding'];
            $unit = $padding['unit'] ?? 'px';
            $top = $padding['top'] ?? 0;
            $right = $padding['right'] ?? 0;
            $bottom = $padding['bottom'] ?? 0;
            $left = $padding['left'] ?? 0;
            $styles[] = "padding: {$top}{$unit} {$right}{$unit} {$bottom}{$unit} {$left}{$unit}";
        }

        // Generic properties
        foreach ($settings as $key => $value) {
            if (is_scalar($value)) {
                $cssProperty = $this->settingToCssProperty($key);
                if ($cssProperty) {
                    $styles[] = "{$cssProperty}: " . $this->sanitizeCssValue($value);
                }
            }
        }

        if (empty($styles)) {
            return '';
        }

        $css = $selector . " {\n    " . implode(";\n    ", $styles) . ";\n}\n\n";
        
        // Add custom CSS if provided
        if (!empty($settings['custom_css'])) {
            $css .= "/* Custom CSS for widget {$widgetId} */\n";
            $css .= $settings['custom_css'] . "\n\n";
        }
        
        return $css;
    }

    /**
     * Generate hover state CSS
     */
    protected function generateHoverCss(string $widgetId, array $hoverSettings): string
    {
        $selector = ".widget-{$widgetId}:hover";
        $styles = [];

        // Background color
        if (!empty($hoverSettings['background_color'])) {
            $styles[] = "background-color: " . $this->sanitizeCssValue($hoverSettings['background_color']);
        }

        // Text color
        if (!empty($hoverSettings['text_color'])) {
            $styles[] = "color: " . $this->sanitizeCssValue($hoverSettings['text_color']);
        }

        // Border color
        if (!empty($hoverSettings['border_color'])) {
            $styles[] = "border-color: " . $this->sanitizeCssValue($hoverSettings['border_color']);
        }

        // Opacity
        if (isset($hoverSettings['opacity'])) {
            $styles[] = "opacity: {$hoverSettings['opacity']}";
        }

        // Transform (scale, rotate, etc)
        if (!empty($hoverSettings['transform'])) {
            $styles[] = "transform: " . $this->sanitizeCssValue($hoverSettings['transform']);
        }

        // Add transition for smooth hover effects
        if (!empty($styles)) {
            array_unshift($styles, "transition: all 0.3s ease");
        }

        if (empty($styles)) {
            return '';
        }

        return $selector . " {\n    " . implode(";\n    ", $styles) . ";\n}\n\n";
    }

    /**
     * Generate responsive CSS for tablet and mobile breakpoints
     */
    protected function generateResponsiveCss(string $widgetId, array $settings): string
    {
        $css = '';

        // Tablet styles (< 1024px)
        $tabletStyles = [];
        foreach ($settings as $key => $value) {
            if (str_ends_with($key, '_tablet') && !empty($value)) {
                $baseKey = str_replace('_tablet', '', $key);
                $cssProperty = $this->settingToCssProperty($baseKey);
                if ($cssProperty && is_scalar($value)) {
                    $tabletStyles[] = "{$cssProperty}: " . $this->sanitizeCssValue($value);
                }
            }
        }

        if (!empty($tabletStyles)) {
            $css .= "@media (max-width: 1024px) {\n    .widget-{$widgetId} {\n        ";
            $css .= implode(";\n        ", $tabletStyles);
            $css .= ";\n    }\n}\n\n";
        }

        // Mobile styles (< 768px)
        $mobileStyles = [];
        foreach ($settings as $key => $value) {
            if (str_ends_with($key, '_mobile') && !empty($value)) {
                $baseKey = str_replace('_mobile', '', $key);
                $cssProperty = $this->settingToCssProperty($baseKey);
                if ($cssProperty && is_scalar($value)) {
                    $mobileStyles[] = "{$cssProperty}: " . $this->sanitizeCssValue($value);
                }
            }
        }

        if (!empty($mobileStyles)) {
            $css .= "@media (max-width: 768px) {\n    .widget-{$widgetId} {\n        ";
            $css .= implode(";\n        ", $mobileStyles);
            $css .= ";\n    }\n}\n\n";
        }

        return $css;
    }

    /**
     * Map widget setting name to CSS property
     */
    protected function settingToCssProperty(string $setting): ?string
    {
        return match($setting) {
            'font_size' => 'font-size',
            'text_color' => 'color',
            'background_color' => 'background-color',
            'border_color' => 'border-color',
            'margin_top' => 'margin-top',
            'margin_right' => 'margin-right',
            'margin_bottom' => 'margin-bottom',
            'margin_left' => 'margin-left',
            'padding_top' => 'padding-top',
            'padding_right' => 'padding-right',
            'padding_bottom' => 'padding-bottom',
            'padding_left' => 'padding-left',
            'width' => 'width',
            'height' => 'height',
            'max_width' => 'max-width',
            'min_height' => 'min-height',
            'flex_direction' => 'flex-direction',
            'justify_content' => 'justify-content',
            'align_items' => 'align-items',
            'gap' => 'gap',
            'flex_wrap' => 'flex-wrap',
            default => null,
        };
    }

    /**
     * Sanitize CSS value to prevent injection
     */
    protected function sanitizeCssValue(string $value): string
    {
        // Remove potentially dangerous characters
        $value = preg_replace('/[<>{}]/', '', $value);
        
        // Escape quotes
        $value = str_replace(['\\', '"', "'"], ['\\\\', '\\"', "\\'"], $value);
        
        return $value;
    }
}
