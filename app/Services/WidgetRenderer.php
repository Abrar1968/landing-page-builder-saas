<?php

namespace App\Services;

class WidgetRenderer
{
    public function __construct(
        protected HtmlSanitizer $htmlSanitizer
    ) {}

    /**
     * Build CSS classes for responsive visibility and motion effects
     */
    protected function buildWrapperClasses(array $settings): string
    {
        $classes = [];

        // CSS Classes from settings
        if (!empty($settings['css_classes'])) {
            $classes[] = e($settings['css_classes']);
        }

        // Responsive visibility classes
        if (!empty($settings['responsive_visibility'])) {
            $visibility = $settings['responsive_visibility'];
            if (!empty($visibility['hide_desktop'])) {
                $classes[] = 'hidden-desktop';
            }
            if (!empty($visibility['hide_tablet'])) {
                $classes[] = 'hidden-tablet';
            }
            if (!empty($visibility['hide_mobile'])) {
                $classes[] = 'hidden-mobile';
            }
        }

        // Motion effects - entrance animation
        if (!empty($settings['motion_effects'])) {
            $motion = $settings['motion_effects'];

            // Entrance animation
            if (!empty($motion['entrance_animation']) && $motion['entrance_animation'] !== 'none') {
                $animation = $motion['entrance_animation'];
                $duration = $motion['animation_duration'] ?? 'normal';
                $classes[] = "animate-{$animation}";
                if ($duration !== 'normal') {
                    $classes[] = "animate-{$duration}";
                }
            }

            // Sticky effect
            if (!empty($motion['sticky']) && $motion['sticky'] !== 'none') {
                $classes[] = 'sticky-element';
            }

            // Parallax effect
            if (!empty($motion['scrolling_effect']) && $motion['scrolling_effect'] === 'parallax') {
                $classes[] = 'parallax-element';
            }
        }

        return implode(' ', $classes);
    }

    /**
     * Build inline styles for wrapper element
     */
    protected function buildWrapperStyles(array $settings): string
    {
        $styles = [];

        // Margin
        if (!empty($settings['margin']) && is_array($settings['margin'])) {
            $m = $settings['margin'];
            $unit = $m['unit'] ?? 'px';
            $top = $m['top'] ?? 0;
            $right = $m['right'] ?? 0;
            $bottom = $m['bottom'] ?? 0;
            $left = $m['left'] ?? 0;
            if ($top || $right || $bottom || $left) {
                $styles[] = "margin: {$top}{$unit} {$right}{$unit} {$bottom}{$unit} {$left}{$unit}";
            }
        }

        // Padding
        if (!empty($settings['padding']) && is_array($settings['padding'])) {
            $p = $settings['padding'];
            $unit = $p['unit'] ?? 'px';
            $top = $p['top'] ?? 0;
            $right = $p['right'] ?? 0;
            $bottom = $p['bottom'] ?? 0;
            $left = $p['left'] ?? 0;
            if ($top || $right || $bottom || $left) {
                $styles[] = "padding: {$top}{$unit} {$right}{$unit} {$bottom}{$unit} {$left}{$unit}";
            }
        }

        // Background color
        if (!empty($settings['background_color'])) {
            $styles[] = "background-color: {$settings['background_color']}";
        }

        // Border
        if (!empty($settings['border']) && is_array($settings['border'])) {
            $border = $settings['border'];
            if (!empty($border['type']) && $border['type'] !== 'none') {
                $borderColor = $border['color'] ?? '#000000';
                $borderType = $border['type'];
                $borderWidth = 1;

                if (!empty($border['width']) && is_array($border['width'])) {
                    $borderWidth = $border['width']['top'] ?? 1;
                } elseif (!empty($border['width'])) {
                    $borderWidth = $border['width'];
                }

                $styles[] = "border: {$borderWidth}px {$borderType} {$borderColor}";
            }
        }

        // Border radius
        if (!empty($settings['border_radius']) && is_array($settings['border_radius'])) {
            $br = $settings['border_radius'];
            $unit = $br['unit'] ?? 'px';
            $topLeft = $br['topLeft'] ?? 0;
            $topRight = $br['topRight'] ?? 0;
            $bottomRight = $br['bottomRight'] ?? 0;
            $bottomLeft = $br['bottomLeft'] ?? 0;
            if ($topLeft || $topRight || $bottomRight || $bottomLeft) {
                $styles[] = "border-radius: {$topLeft}{$unit} {$topRight}{$unit} {$bottomRight}{$unit} {$bottomLeft}{$unit}";
            }
        }

        // Box shadow
        if (!empty($settings['box_shadow']) && is_array($settings['box_shadow'])) {
            $shadow = $settings['box_shadow'];
            if (!empty($shadow['color'])) {
                $h = $shadow['horizontal'] ?? 0;
                $v = $shadow['vertical'] ?? 0;
                $blur = $shadow['blur'] ?? 10;
                $spread = $shadow['spread'] ?? 0;
                $color = $shadow['color'];
                $position = !empty($shadow['position']) && $shadow['position'] === 'inset' ? 'inset ' : '';
                $styles[] = "box-shadow: {$position}{$h}px {$v}px {$blur}px {$spread}px {$color}";
            }
        }

        // Z-index
        if (!empty($settings['z_index'])) {
            $styles[] = "z-index: {$settings['z_index']}";
        }

        // Motion effects - sticky offset
        if (!empty($settings['motion_effects']['sticky']) && $settings['motion_effects']['sticky'] !== 'none') {
            $offset = $settings['motion_effects']['sticky_offset'] ?? 0;
            $sticky = $settings['motion_effects']['sticky'];
            if ($sticky === 'top') {
                $styles[] = "position: sticky";
                $styles[] = "top: {$offset}px";
            } elseif ($sticky === 'bottom') {
                $styles[] = "position: sticky";
                $styles[] = "bottom: {$offset}px";
            }
        }

        // Animation delay
        if (!empty($settings['motion_effects']['animation_delay'])) {
            $delay = $settings['motion_effects']['animation_delay'];
            $styles[] = "animation-delay: {$delay}ms";
        }

        return implode('; ', $styles);
    }

    /**
     * Build complete wrapper attributes string
     */
    protected function buildWrapperAttributes(array $settings): string
    {
        $attrs = [];

        // ID
        if (!empty($settings['css_id'])) {
            $attrs[] = 'id="' . e($settings['css_id']) . '"';
        }

        // Classes
        $classes = $this->buildWrapperClasses($settings);
        if (!empty($classes)) {
            $attrs[] = 'class="' . $classes . '"';
        }

        // Styles
        $styles = $this->buildWrapperStyles($settings);
        if (!empty($styles)) {
            $attrs[] = 'style="' . $styles . '"';
        }

        // Data attributes for motion effects
        if (!empty($settings['motion_effects'])) {
            $motion = $settings['motion_effects'];
            if (!empty($motion['entrance_animation']) && $motion['entrance_animation'] !== 'none') {
                $attrs[] = 'data-animation="' . e($motion['entrance_animation']) . '"';
            }
            if (!empty($motion['scrolling_effect']) && $motion['scrolling_effect'] === 'parallax') {
                $speed = $motion['parallax_speed'] ?? 0.5;
                $attrs[] = 'data-parallax-speed="' . e($speed) . '"';
            }
        }

        return implode(' ', $attrs);
    }

    /**
     * Render a widget to HTML
     */
    public function render(array $widget): string
    {
        $type = $widget['widgetType'] ?? 'unknown';
        $settings = $widget['settings'] ?? [];

        return match ($type) {
            'heading' => $this->renderHeading($settings),
            'text-editor' => $this->renderTextEditor($settings),
            'image' => $this->renderImage($settings),
            'button' => $this->renderButton($settings),
            'video' => $this->renderVideo($settings),
            'divider' => $this->renderDivider($settings),
            'spacer' => $this->renderSpacer($settings),
            'icon' => $this->renderIcon($settings),
            'icon-box' => $this->renderIconBox($settings),
            'counter' => $this->renderCounter($settings),
            'progress-bar' => $this->renderProgressBar($settings),
            'testimonial' => $this->renderTestimonial($settings),
            'social-icons' => $this->renderSocialIcons($settings),
            'alert' => $this->renderAlert($settings),
            'toggle' => $this->renderToggle($settings),
            'icon-list' => $this->renderIconList($settings),
            'text-path' => $this->renderTextPath($settings),
            'image-carousel' => $this->renderImageCarousel($settings),
            'basic-gallery' => $this->renderBasicGallery($settings),
            'soundcloud' => $this->renderSoundCloud($settings),
            'image-box' => $this->renderImageBox($settings),
            'star-rating' => $this->renderStarRating($settings),
            'tabs' => $this->renderTabs($settings),
            'accordion' => $this->renderAccordion($settings),
            'countdown' => $this->renderCountdown($settings),
            'google-maps' => $this->renderGoogleMaps($settings),
            'call-to-action' => $this->renderCallToAction($settings),
            'flip-box' => $this->renderFlipBox($settings),
            'price-table' => $this->renderPriceTable($settings),
            'form' => $this->renderForm($settings),
            'slider' => $this->renderSlider($settings),
            'container' => $this->renderContainer($settings),
            'inner-section' => $this->renderInnerSection($settings),
            'menu-anchor' => $this->renderMenuAnchor($settings),
            'sidebar' => $this->renderSidebar($settings),
            'html' => $this->renderHtml($settings),
            'shortcode' => $this->renderShortcode($settings),
            default => $this->renderUnknown($type),
        };
    }

    protected function renderHeading(array $settings): string
    {
        // Allow HTML in title but sanitize for security
        $title = $this->htmlSanitizer->sanitize($settings['title'] ?? 'Heading');
        $tag = $settings['size'] ?? 'h2';

        // Handle link as object/array (from Vue frontend)
        $linkData = $settings['link'] ?? null;
        $link = '';
        $target = '_self';
        $nofollow = false;

        if (is_array($linkData)) {
            $link = $linkData['url'] ?? '';
            $target = !empty($linkData['is_external']) ? '_blank' : '_self';
            $nofollow = !empty($linkData['nofollow']);
        } elseif (is_string($linkData)) {
            $link = $linkData;
        }

        // Build inline styles
        $styles = [];

        // Text color
        if (!empty($settings['text_color'])) {
            $styles[] = "color: {$settings['text_color']}";
        }

        // Alignment
        if (!empty($settings['alignment'])) {
            $styles[] = "text-align: {$settings['alignment']}";
        }

        // Typography settings
        if (!empty($settings['typography']) && is_array($settings['typography'])) {
            $typo = $settings['typography'];

            if (!empty($typo['family']) && $typo['family'] !== 'Default') {
                // Wrap font names with spaces in quotes
                $fontFamily = strpos($typo['family'], ' ') !== false
                    ? "'{$typo['family']}', sans-serif"
                    : "{$typo['family']}, sans-serif";
                $styles[] = "font-family: {$fontFamily}";
            }

            if (!empty($typo['size'])) {
                $unit = $typo['sizeUnit'] ?? 'px';
                $styles[] = "font-size: {$typo['size']}{$unit}";
            }

            if (!empty($typo['weight']) && $typo['weight'] !== 'Normal' && $typo['weight'] !== '400') {
                $styles[] = "font-weight: {$typo['weight']}";
            }

            if (!empty($typo['transform']) && $typo['transform'] !== 'None' && strtolower($typo['transform']) !== 'none') {
                $styles[] = "text-transform: " . strtolower($typo['transform']);
            }

            if (!empty($typo['style']) && $typo['style'] !== 'Normal' && strtolower($typo['style']) !== 'normal') {
                $styles[] = "font-style: " . strtolower($typo['style']);
            }

            if (!empty($typo['lineHeight']) && $typo['lineHeight'] != 0) {
                $styles[] = "line-height: {$typo['lineHeight']}";
            }

            if (isset($typo['letterSpacing']) && $typo['letterSpacing'] !== '' && $typo['letterSpacing'] !== null) {
                $styles[] = "letter-spacing: {$typo['letterSpacing']}px";
            }
        }

        // Margin
        if (!empty($settings['margin']) && is_array($settings['margin'])) {
            $m = $settings['margin'];
            $unit = $m['unit'] ?? 'px';
            $top = $m['top'] ?? 0;
            $right = $m['right'] ?? 0;
            $bottom = $m['bottom'] ?? 0;
            $left = $m['left'] ?? 0;
            $styles[] = "margin: {$top}{$unit} {$right}{$unit} {$bottom}{$unit} {$left}{$unit}";
        }

        // Padding
        if (!empty($settings['padding']) && is_array($settings['padding'])) {
            $p = $settings['padding'];
            $unit = $p['unit'] ?? 'px';
            $top = $p['top'] ?? 0;
            $right = $p['right'] ?? 0;
            $bottom = $p['bottom'] ?? 0;
            $left = $p['left'] ?? 0;
            $styles[] = "padding: {$top}{$unit} {$right}{$unit} {$bottom}{$unit} {$left}{$unit}";
        }

        $styleAttr = !empty($styles) ? ' style="' . implode('; ', $styles) . '"' : '';

        // CSS Classes and ID
        $classAttr = !empty($settings['css_classes']) ? ' class="' . e($settings['css_classes']) . '"' : '';
        $idAttr = !empty($settings['css_id']) ? ' id="' . e($settings['css_id']) . '"' : '';

        // Build the heading HTML
        $headingContent = "<{$tag}{$styleAttr}{$classAttr}{$idAttr}>";

        // Wrap text in link if provided
        if (!empty($link)) {
            $relAttr = $nofollow ? ' rel="nofollow"' : '';
            $linkStyle = ' style="color: inherit; text-decoration: inherit;"';
            $headingContent .= "<a href=\"" . e($link) . "\" target=\"{$target}\"{$relAttr}{$linkStyle}>{$title}</a>";
        } else {
            $headingContent .= $title;
        }

        $headingContent .= "</{$tag}>";

        // Custom CSS
        if (!empty($settings['custom_css'])) {
            $headingContent .= "\n<style>" . $settings['custom_css'] . "</style>";
        }

        // Wrap with responsive visibility and motion effects
        $wrapperAttrs = $this->buildWrapperAttributes($settings);
        if (!empty($wrapperAttrs)) {
            $headingContent = "<div {$wrapperAttrs}>{$headingContent}</div>";
        }

        return $headingContent;
    }

    protected function renderTextEditor(array $settings): string
    {
        $content = $settings['editor'] ?? '<p>Lorem ipsum dolor sit amet</p>';

        // ✅ Sanitize HTML to prevent XSS attacks
        $content = $this->htmlSanitizer->sanitize($content);

        $color = $settings['text_color'] ?? '#4b5563';
        $alignment = $settings['alignment'] ?? 'left';

        // Build wrapper classes for responsive visibility and motion effects
        $wrapperClasses = $this->buildWrapperClasses($settings);
        $wrapperStyles = $this->buildWrapperStyles($settings);
        $classAttr = !empty($wrapperClasses) ? ' class="' . $wrapperClasses . '"' : '';
        $styleAttr = "color: {$color}; text-align: {$alignment};";
        if (!empty($wrapperStyles)) {
            $styleAttr .= ' ' . $wrapperStyles;
        }

        return "<div{$classAttr} style=\"{$styleAttr}\">{$content}</div>";
    }

    protected function renderImage(array $settings): string
    {
        if (empty($settings['image_url'])) {
            return '';
        }

        $url = e($settings['image_url']);
        $alt = e($settings['alt_text'] ?? '');
        $width = $settings['width'] ?? 100;
        $maxWidth = $settings['max_width'] ?? 0;
        $alignment = $settings['alignment'] ?? 'left';
        $link = $settings['link'] ?? '';
        $linkTarget = !empty($settings['link_target']) ? '_blank' : '_self';

        // CSS Filters
        $opacity = $settings['opacity'] ?? 1;
        $filterBlur = $settings['filter_blur'] ?? 0;
        $filterBrightness = $settings['filter_brightness'] ?? 100;
        $filterContrast = $settings['filter_contrast'] ?? 100;
        $filterSaturation = $settings['filter_saturation'] ?? 100;
        $filterHue = $settings['filter_hue'] ?? 0;

        // Build CSS filter string
        $filters = [];
        if ($filterBlur > 0) {
            $filters[] = "blur({$filterBlur}px)";
        }
        if ($filterBrightness != 100) {
            $filters[] = "brightness({$filterBrightness}%)";
        }
        if ($filterContrast != 100) {
            $filters[] = "contrast({$filterContrast}%)";
        }
        if ($filterSaturation != 100) {
            $filters[] = "saturate({$filterSaturation}%)";
        }
        if ($filterHue > 0) {
            $filters[] = "hue-rotate({$filterHue}deg)";
        }
        $filterCss = !empty($filters) ? 'filter: ' . implode(' ', $filters) . ';' : '';

        // Hover animation
        $hoverAnimation = $settings['hover_animation'] ?? 'none';
        $imgId = 'img-' . uniqid();

        // Hover animation CSS
        $hoverCss = '';
        $transitionCss = 'transition: all 0.3s ease;';
        if ($hoverAnimation !== 'none') {
            $hoverStyles = match ($hoverAnimation) {
                'zoom' => 'transform: scale(1.1);',
                'zoom_out' => 'transform: scale(0.9);',
                'grayscale' => 'filter: grayscale(100%);',
                'blur' => 'filter: blur(3px);',
                'brightness' => 'filter: brightness(130%);',
                default => '',
            };
            $hoverCss = "<style>#{$imgId}:hover img{{$hoverStyles}}</style>";
        }

        // Max width style
        $maxWidthStyle = $maxWidth > 0 ? "max-width: {$maxWidth}px;" : '';

        $imgStyle = "width: {$width}%; opacity: {$opacity}; {$filterCss} {$transitionCss}";

        $html = $hoverCss;
        $html .= "<div id=\"{$imgId}\" style=\"text-align: {$alignment}; {$maxWidthStyle}\">";

        $imgTag = "<img src=\"{$url}\" alt=\"{$alt}\" style=\"{$imgStyle}\" />";

        if (!empty($link)) {
            $linkUrl = e($link);
            $html .= "<a href=\"{$linkUrl}\" target=\"{$linkTarget}\">{$imgTag}</a>";
        } else {
            $html .= $imgTag;
        }

        if (!empty($settings['caption'])) {
            $caption = e($settings['caption']);
            $html .= "<p style=\"margin-top: 0.5rem; font-size: 0.875rem; color: #6b7280;\">{$caption}</p>";
        }

        $html .= "</div>";

        // Add responsive visibility and motion effects wrapper
        $wrapperClasses = $this->buildWrapperClasses($settings);
        $wrapperStyles = $this->buildWrapperStyles($settings);
        if (!empty($wrapperClasses) || !empty($wrapperStyles)) {
            $classAttr = !empty($wrapperClasses) ? ' class="' . $wrapperClasses . '"' : '';
            $styleAttr = !empty($wrapperStyles) ? ' style="' . $wrapperStyles . '"' : '';
            $html = "<div{$classAttr}{$styleAttr}>{$html}</div>";
        }

        return $html;
    }

    protected function renderButton(array $settings): string
    {
        $text = e($settings['text'] ?? 'Button');
        $link = e($settings['link'] ?? '#');
        $bgColor = $settings['background_color'] ?? '#4f46e5';
        $textColor = $settings['text_color'] ?? '#ffffff';
        $borderColor = $settings['border_color'] ?? '#4f46e5';
        $borderRadius = $settings['border_radius'] ?? '6';
        $borderWidth = $settings['border_width'] ?? 0;
        $alignment = $settings['alignment'] ?? 'left';
        $target = !empty($settings['target']) ? '_blank' : '_self';

        // Padding controls
        $paddingH = $settings['padding_horizontal'] ?? 24;
        $paddingV = $settings['padding_vertical'] ?? 12;

        // Icon support
        $icon = e($settings['icon'] ?? '');
        $iconPosition = $settings['icon_position'] ?? 'left';
        $iconSpacing = $settings['icon_spacing'] ?? 8;

        // Hover colors (for CSS class)
        $hoverBgColor = $settings['hover_background_color'] ?? '#4338ca';
        $hoverTextColor = $settings['hover_text_color'] ?? '#ffffff';
        $hoverBorderColor = $settings['hover_border_color'] ?? '#4338ca';

        // Typography settings
        $typography = $settings['typography'] ?? [];
        $fontFamily = $typography['family'] ?? 'inherit';
        $fontSize = $typography['size'] ?? 16;
        $fontWeight = $typography['weight'] ?? 500;
        $fontStyle = $typography['style'] ?? 'normal';
        $lineHeight = $typography['line_height'] ?? 1.5;
        $letterSpacing = $typography['letter_spacing'] ?? 0;
        $textTransform = $typography['transform'] ?? 'none';
        $textDecoration = $typography['decoration'] ?? 'none';

        // Build font-family
        $fontFamilyCss = $fontFamily !== 'inherit' && $fontFamily !== 'Default' ? "font-family: '{$fontFamily}', sans-serif;" : '';

        // Generate unique ID for hover effect
        $btnId = 'btn-' . uniqid();

        // Build button content with icon
        $buttonContent = '';
        if ($icon && $iconPosition === 'left') {
            $buttonContent .= "<span style=\"margin-right: {$iconSpacing}px;\">{$icon}</span>";
        }
        $buttonContent .= $text;
        if ($icon && $iconPosition === 'right') {
            $buttonContent .= "<span style=\"margin-left: {$iconSpacing}px;\">{$icon}</span>";
        }

        $styles = "background-color: {$bgColor}; color: {$textColor}; padding: {$paddingV}px {$paddingH}px; border-radius: {$borderRadius}px; text-decoration: {$textDecoration}; display: inline-flex; align-items: center; justify-content: center; border: {$borderWidth}px solid {$borderColor}; cursor: pointer; transition: all 0.3s ease; {$fontFamilyCss} font-size: {$fontSize}px; font-weight: {$fontWeight}; font-style: {$fontStyle}; line-height: {$lineHeight}; letter-spacing: {$letterSpacing}px; text-transform: {$textTransform};";

        // Add inline hover style via CSS
        $hoverCss = "<style>#{$btnId}:hover{background-color:{$hoverBgColor}!important;color:{$hoverTextColor}!important;border-color:{$hoverBorderColor}!important;}</style>";

        // Build wrapper classes and styles
        $wrapperClasses = $this->buildWrapperClasses($settings);
        $wrapperStyles = $this->buildWrapperStyles($settings);
        $wrapperClassAttr = !empty($wrapperClasses) ? ' class="' . $wrapperClasses . '"' : '';
        $wrapperStyleAttr = !empty($wrapperStyles) ? ' ' . $wrapperStyles : '';

        return "{$hoverCss}<div{$wrapperClassAttr} style=\"text-align: {$alignment};{$wrapperStyleAttr}\"><a id=\"{$btnId}\" href=\"{$link}\" target=\"{$target}\" style=\"{$styles}\">{$buttonContent}</a></div>";
    }

    protected function renderVideo(array $settings): string
    {
        $url = $settings['youtube_url'] ?? '';
        $videoType = $settings['video_type'] ?? 'youtube';

        if (empty($url)) {
            return '';
        }

        // Playback settings
        $autoplay = !empty($settings['autoplay']) ? '1' : '0';
        $mute = !empty($settings['mute']) ? '1' : '0';
        $loop = !empty($settings['loop']) ? '1' : '0';
        $controls = ($settings['controls'] ?? true) ? '1' : '0';
        $startTime = $settings['start_time'] ?? 0;
        $endTime = $settings['end_time'] ?? null;
        $modestBranding = !empty($settings['modest_branding']) ? '1' : '0';

        // Width setting
        $width = $settings['width'] ?? 100;

        // Extract video ID and build embed URL
        $embedUrl = '';

        if ($videoType === 'vimeo') {
            // Extract Vimeo video ID
            preg_match('/(?:vimeo\.com\/|player\.vimeo\.com\/video\/)(\d+)/', $url, $match);
            $videoId = $match[1] ?? '';

            if (empty($videoId)) {
                return '';
            }

            // Build Vimeo embed URL with parameters
            $params = [];
            if ($autoplay === '1') {
                $params[] = 'autoplay=1';
            }
            if ($mute === '1') {
                $params[] = 'muted=1';
            }
            if ($loop === '1') {
                $params[] = 'loop=1';
            }
            if ($controls === '0') {
                $params[] = 'controls=0';
            }
            if ($startTime > 0) {
                $params[] = "#t={$startTime}s";
            }

            $embedUrl = "https://player.vimeo.com/video/{$videoId}";
            if (!empty($params)) {
                $embedUrl .= '?' . implode('&', array_filter($params, fn ($p) => !str_starts_with($p, '#')));
                $hashParam = array_filter($params, fn ($p) => str_starts_with($p, '#'));
                if (!empty($hashParam)) {
                    $embedUrl .= reset($hashParam);
                }
            }
        } else {
            // YouTube - Extract video ID
            preg_match('/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([^&?]+)/', $url, $match);
            $videoId = $match[1] ?? '';

            if (empty($videoId)) {
                return '';
            }

            // Build YouTube embed URL with parameters
            $params = [
                "autoplay={$autoplay}",
                "mute={$mute}",
                "loop={$loop}",
                "controls={$controls}",
                "modestbranding={$modestBranding}",
            ];

            if ($startTime > 0) {
                $params[] = "start={$startTime}";
            }
            if ($endTime > 0) {
                $params[] = "end={$endTime}";
            }
            if ($loop === '1') {
                $params[] = "playlist={$videoId}"; // Required for YouTube loop
            }

            $embedUrl = "https://www.youtube.com/embed/{$videoId}?" . implode('&', $params);
        }

        $aspectRatio = $settings['aspect_ratio'] ?? '16:9';
        [$w, $h] = explode(':', $aspectRatio);
        $paddingBottom = ($h / $w) * 100;

        return "<div style=\"width: {$width}%; margin: 0 auto;\"><div style=\"position: relative; padding-bottom: {$paddingBottom}%; height: 0;\"><iframe src=\"{$embedUrl}\" frameborder=\"0\" allow=\"autoplay; fullscreen; picture-in-picture\" allowfullscreen style=\"position: absolute; top: 0; left: 0; width: 100%; height: 100%;\"></iframe></div></div>";
    }

    protected function renderDivider(array $settings): string
    {
        $style = $settings['style'] ?? 'solid';
        $color = $settings['color'] ?? '#e5e7eb';
        $weight = $settings['weight'] ?? 1;
        $width = $settings['width'] ?? 100;
        $alignment = $settings['alignment'] ?? 'center';
        $gap = $settings['gap'] ?? 20;

        // Element settings
        $dividerElement = $settings['divider_element'] ?? 'none';
        $elementText = e($settings['element_text'] ?? 'OR');
        $elementIcon = e($settings['element_icon'] ?? '★');
        $elementColor = $settings['element_color'] ?? '#6b7280';
        $elementSize = $settings['element_size'] ?? 16;
        $elementSpacing = $settings['element_spacing'] ?? 16;

        // If element is none, render simple divider
        if ($dividerElement === 'none') {
            return "<div style=\"text-align: {$alignment}; margin: {$gap}px 0;\"><hr style=\"border: none; border-top-style: {$style}; border-top-color: {$color}; border-top-width: {$weight}px; width: {$width}%; display: inline-block; margin: 0;\" /></div>";
        }

        // Divider with element (text or icon)
        $elementContent = $dividerElement === 'text' ? $elementText : $elementIcon;

        $justifyMap = [
            'left' => 'flex-start',
            'center' => 'center',
            'right' => 'flex-end',
        ];
        $justify = $justifyMap[$alignment] ?? 'center';

        $html = "<div style=\"display: flex; align-items: center; justify-content: {$justify}; margin: {$gap}px 0; width: {$width}%;\">";
        $html .= "<div style=\"flex: 1; border-top-style: {$style}; border-top-color: {$color}; border-top-width: {$weight}px;\"></div>";
        $html .= "<span style=\"margin: 0 {$elementSpacing}px; color: {$elementColor}; font-size: {$elementSize}px; white-space: nowrap;\">{$elementContent}</span>";
        $html .= "<div style=\"flex: 1; border-top-style: {$style}; border-top-color: {$color}; border-top-width: {$weight}px;\"></div>";
        $html .= "</div>";

        return $html;
    }

    protected function renderSpacer(array $settings): string
    {
        $space = $settings['space'] ?? 50;
        $unit = $settings['space_unit'] ?? 'px';

        // Only allow px or vh units for security
        $unit = in_array($unit, ['px', 'vh']) ? $unit : 'px';

        return "<div style=\"height: {$space}{$unit};\"></div>";
    }

    protected function renderIcon(array $settings): string
    {
        $icon = e($settings['icon'] ?? '★');
        $size = $settings['size'] ?? 50;
        $color = $settings['primary_color'] ?? '#4f46e5';
        $alignment = $settings['alignment'] ?? 'center';

        $html = "<div style=\"text-align: {$alignment}; font-size: {$size}px; color: {$color};\">";

        if (!empty($settings['link'])) {
            $link = e($settings['link']);
            $html .= "<a href=\"{$link}\" style=\"color: inherit;\">{$icon}</a>";
        } else {
            $html .= $icon;
        }

        $html .= "</div>";

        return $html;
    }

    protected function renderIconBox(array $settings): string
    {
        $icon = e($settings['icon'] ?? '⚡');
        $title = e($settings['title'] ?? 'Icon Box');
        $description = e($settings['description'] ?? 'Description');
        $iconSize = $settings['icon_size'] ?? 50;
        $iconColor = $settings['icon_color'] ?? '#4f46e5';
        $hoverIconColor = $settings['hover_icon_color'] ?? '';
        $titleColor = $settings['title_color'] ?? '#1f2937';
        $hoverTitleColor = $settings['hover_title_color'] ?? '';
        $descriptionColor = $settings['description_color'] ?? '#6b7280';
        $alignment = $settings['alignment'] ?? 'center';
        $iconPosition = $settings['icon_position'] ?? 'top';
        $iconSpacing = $settings['icon_spacing'] ?? 15;
        $verticalAlign = $settings['content_vertical_alignment'] ?? 'top';
        $link = $settings['link'] ?? '';

        // Vertical alignment mapping
        $alignItemsMap = [
            'top' => 'flex-start',
            'center' => 'center',
            'bottom' => 'flex-end',
        ];
        $alignItems = $alignItemsMap[$verticalAlign] ?? 'flex-start';

        // Generate unique ID for hover effects
        $boxId = 'icon-box-' . uniqid();

        // Build hover CSS if hover colors are set
        $hoverCss = '';
        if ($hoverIconColor || $hoverTitleColor) {
            $hoverCss = '<style>';
            if ($hoverIconColor) {
                $hoverCss .= "#{$boxId}:hover .icon-box-icon{color:{$hoverIconColor}!important;}";
            }
            if ($hoverTitleColor) {
                $hoverCss .= "#{$boxId}:hover .icon-box-title{color:{$hoverTitleColor}!important;}";
            }
            $hoverCss .= '</style>';
        }

        $iconHtml = "<div class=\"icon-box-icon\" style=\"font-size: {$iconSize}px; color: {$iconColor}; transition: color 0.3s ease;\">{$icon}</div>";
        $contentHtml = "<div>
            <h4 class=\"icon-box-title\" style=\"color: {$titleColor}; font-weight: 600; margin: 0; transition: color 0.3s ease;\">{$title}</h4>
            <p style=\"color: {$descriptionColor}; margin-top: 0.5rem;\">{$description}</p>
        </div>";

        // Build layout based on position
        $html = $hoverCss;

        if ($iconPosition === 'top') {
            $html .= "<div id=\"{$boxId}\" style=\"text-align: {$alignment};\">";
            $html .= "<div style=\"margin-bottom: {$iconSpacing}px;\">{$iconHtml}</div>";
            $html .= $contentHtml;
            $html .= "</div>";
        } elseif ($iconPosition === 'left') {
            $html .= "<div id=\"{$boxId}\" style=\"display: flex; align-items: {$alignItems}; text-align: left;\">";
            $html .= "<div style=\"margin-right: {$iconSpacing}px; flex-shrink: 0;\">{$iconHtml}</div>";
            $html .= $contentHtml;
            $html .= "</div>";
        } else {
            // right
            $html .= "<div id=\"{$boxId}\" style=\"display: flex; align-items: {$alignItems}; text-align: right; flex-direction: row-reverse;\">";
            $html .= "<div style=\"margin-left: {$iconSpacing}px; flex-shrink: 0;\">{$iconHtml}</div>";
            $html .= $contentHtml;
            $html .= "</div>";
        }

        // Wrap with link if provided
        if (!empty($link)) {
            $linkUrl = e($link);
            $html = "<a href=\"{$linkUrl}\" style=\"text-decoration: none; color: inherit; display: block;\">{$html}</a>";
        }

        return $html;
    }

    protected function renderCounter(array $settings): string
    {
        $number = $settings['ending_number'] ?? 100;
        $prefix = e($settings['prefix'] ?? '');
        $suffix = e($settings['suffix'] ?? '');
        $title = e($settings['title'] ?? 'Cool Number');
        $numberSize = $settings['number_size'] ?? 48;
        $numberColor = $settings['number_color'] ?? '#4f46e5';
        $titleColor = $settings['title_color'] ?? '#6b7280';
        $alignment = $settings['alignment'] ?? 'center';
        $duration = $settings['duration'] ?? 2000;

        // Generate unique ID for this counter widget
        $counterId = 'counter-' . uniqid();

        return "<div id=\"{$counterId}\" class=\"counter-widget\" data-widget-type=\"counter\" data-target=\"{$number}\" data-duration=\"{$duration}\" style=\"text-align: {$alignment};\">
            <div style=\"font-size: {$numberSize}px; color: {$numberColor}; font-weight: bold;\">
                <span class=\"counter-prefix\">{$prefix}</span><span class=\"counter-number\">0</span><span class=\"counter-suffix\">{$suffix}</span>
            </div>
            <div style=\"color: {$titleColor};\">{$title}</div>
        </div>";
    }

    protected function renderProgressBar(array $settings): string
    {
        $title = e($settings['title'] ?? 'Progress');
        $percent = $settings['percent'] ?? 75;
        $barColor = $settings['bar_color'] ?? '#4f46e5';
        $bgColor = $settings['bg_color'] ?? '#e5e7eb';
        $height = $settings['height'] ?? 12;
        $displayPercent = $settings['display_percent'] ?? true;

        // Generate unique ID for this progress bar widget
        $progressId = 'progress-' . uniqid();

        // CSS with animation
        $css = "<style>
            #{$progressId} .progress-fill {
                width: 0;
                transition: width 1s ease-out;
            }
            #{$progressId}.animated .progress-fill {
                width: {$percent}%;
            }
        </style>";

        $html = $css;
        $html .= "<div id=\"{$progressId}\" class=\"progress-bar-widget\" data-widget-type=\"progress-bar\" data-percent=\"{$percent}\">";

        if ($title || $displayPercent) {
            $html .= '<div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">';
            if ($title) {
                $html .= "<span>{$title}</span>";
            }
            if ($displayPercent) {
                $html .= "<span class=\"progress-percent\">0%</span>";
            }
            $html .= '</div>';
        }

        $html .= "<div style=\"background-color: {$bgColor}; height: {$height}px; border-radius: 9999px; overflow: hidden;\">
            <div class=\"progress-fill\" style=\"background-color: {$barColor}; height: 100%; border-radius: 9999px;\"></div>
        </div>";

        $html .= '</div>';

        return $html;
    }

    protected function renderTestimonial(array $settings): string
    {
        $content = e($settings['content'] ?? 'Lorem ipsum dolor sit amet');
        $name = e($settings['name'] ?? 'John Doe');
        $title = e($settings['title'] ?? 'Designer');
        $contentColor = $settings['content_color'] ?? '#4b5563';
        $nameColor = $settings['name_color'] ?? '#1f2937';
        $alignment = $settings['alignment'] ?? 'center';

        $html = "<div style=\"text-align: {$alignment};\">";
        $html .= "<p style=\"color: {$contentColor}; font-style: italic; margin-bottom: 1rem;\">\"{$content}\"</p>";
        $html .= '<div style="display: flex; align-items: center; justify-content: center; gap: 0.75rem;">';

        if (!empty($settings['image_url'])) {
            $imageUrl = e($settings['image_url']);
            $html .= "<img src=\"{$imageUrl}\" style=\"width: 3rem; height: 3rem; border-radius: 9999px; object-fit: cover;\" />";
        }

        $html .= "<div>
            <div style=\"color: {$nameColor}; font-weight: 600;\">{$name}</div>
            <div style=\"color: #6b7280; font-size: 0.875rem;\">{$title}</div>
        </div>";
        $html .= '</div></div>';

        return $html;
    }

    protected function renderSocialIcons(array $settings): string
    {
        $iconColor = $settings['icon_color'] ?? '#4b5563';
        $hoverColor = $settings['hover_color'] ?? '#4f46e5';
        $iconSize = $settings['icon_size'] ?? 24;
        $alignment = $settings['alignment'] ?? 'center';
        $spacing = $settings['spacing'] ?? 10;

        // Generate unique ID for hover effects
        $socialId = 'social-' . uniqid();

        // CSS with hover effects
        $css = "<style>
            #{$socialId} .social-icon {
                color: {$iconColor};
                font-size: {$iconSize}px;
                margin: 0 {$spacing}px;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 2.5em;
                height: 2.5em;
                border-radius: 50%;
                transition: all 0.3s ease;
            }
            #{$socialId} .social-icon:hover {
                color: {$hoverColor};
                transform: translateY(-3px);
            }
        </style>";

        $html = $css;
        $html .= "<div id=\"{$socialId}\" style=\"text-align: {$alignment};\">";

        // SVG icons for better appearance
        $platforms = [
            'facebook' => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>',
            'twitter' => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
            'instagram' => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>',
            'linkedin' => '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" fill="currentColor"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>',
        ];

        foreach ($platforms as $platform => $icon) {
            if (!empty($settings[$platform])) {
                $url = e($settings[$platform]);
                $html .= "<a href=\"{$url}\" class=\"social-icon\" target=\"_blank\" rel=\"noopener noreferrer\" aria-label=\"{$platform}\">{$icon}</a>";
            }
        }

        $html .= "</div>";

        return $html;
    }

    protected function renderAlert(array $settings): string
    {
        $title = e($settings['title'] ?? 'This is an Alert');
        $content = e($settings['content'] ?? 'Click to edit this text.');
        $type = $settings['alert_type'] ?? 'info';
        $showIcon = $settings['show_icon'] ?? true;

        $colors = [
            'info' => ['bg' => '#eff6ff', 'text' => '#1e40af', 'border' => '#bfdbfe', 'icon' => 'ℹ️'],
            'success' => ['bg' => '#f0fdf4', 'text' => '#166534', 'border' => '#bbf7d0', 'icon' => '✅'],
            'warning' => ['bg' => '#fefce8', 'text' => '#854d0e', 'border' => '#fef08a', 'icon' => '⚠️'],
            'danger' => ['bg' => '#fef2f2', 'text' => '#991b1b', 'border' => '#fecaca', 'icon' => '❌'],
        ];

        $color = $colors[$type] ?? $colors['info'];

        $html = "<div style=\"background-color: {$color['bg']}; color: {$color['text']}; border: 1px solid {$color['border']}; padding: 1rem; border-radius: 0.5rem;\">";
        $html .= '<div style="display: flex; align-items: flex-start; gap: 0.75rem;">';

        if ($showIcon) {
            $html .= "<span style=\"font-size: 1.25rem;\">{$color['icon']}</span>";
        }

        $html .= "<div>
            <div style=\"font-weight: 600;\">{$title}</div>
            <div style=\"margin-top: 0.25rem;\">{$content}</div>
        </div>";
        $html .= '</div></div>';

        return $html;
    }

    // New widgets rendering methods

    protected function renderToggle(array $settings): string
    {
        $titleColor = $settings['title_color'] ?? '#1f2937';
        $titleBg = $settings['title_background'] ?? '#f3f4f6';
        $contentColor = $settings['content_color'] ?? '#4b5563';

        // Generate unique ID for this toggle widget
        $toggleId = 'toggle-' . uniqid();

        // CSS for toggle
        $css = "<style>
            #{$toggleId} .toggle-item {
                border-bottom: 1px solid #e5e7eb;
            }
            #{$toggleId} .toggle-header {
                background-color: {$titleBg};
                color: {$titleColor};
                padding: 0.75rem 1rem;
                font-weight: 500;
                display: flex;
                justify-content: space-between;
                align-items: center;
                cursor: pointer;
                transition: background-color 0.3s ease;
            }
            #{$toggleId} .toggle-header:hover {
                background-color: #e5e7eb;
            }
            #{$toggleId} .toggle-content {
                display: none;
                padding: 0.75rem 1rem;
                color: {$contentColor};
            }
            #{$toggleId} .toggle-content.active {
                display: block;
            }
            #{$toggleId} .toggle-icon {
                transition: transform 0.3s ease;
            }
            #{$toggleId} .toggle-header.active .toggle-icon {
                transform: rotate(45deg);
            }
        </style>";

        $html = $css;
        $html .= "<div id=\"{$toggleId}\" class=\"toggle-widget\" data-widget-type=\"toggle\" style=\"border: 1px solid #e5e7eb; border-radius: 0.5rem; overflow: hidden;\">";

        for ($i = 1; $i <= 3; $i++) {
            $title = e($settings["item{$i}_title"] ?? "Toggle Item {$i}");
            $content = $settings["item{$i}_content"] ?? "<p>Content for toggle item {$i}.</p>";

            // ✅ Sanitize HTML content
            $content = $this->htmlSanitizer->sanitize($content);

            $html .= '<div class="toggle-item">';
            $html .= "<div class=\"toggle-header\" data-toggle-index=\"{$i}\">";
            $html .= "<span>{$title}</span>";
            $html .= '<span class="toggle-icon">+</span>';
            $html .= '</div>';
            $html .= "<div class=\"toggle-content\" data-toggle-content=\"{$i}\">{$content}</div>";
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    protected function renderIconList(array $settings): string
    {
        $iconColor = $settings['icon_color'] ?? '#4f46e5';
        $textColor = $settings['text_color'] ?? '#1f2937';
        $iconSize = $settings['icon_size'] ?? 20;
        $spacing = $settings['spacing'] ?? 12;

        $html = '<div>';

        for ($i = 1; $i <= 3; $i++) {
            $icon = e($settings["item{$i}_icon"] ?? '✓');
            $text = e($settings["item{$i}_text"] ?? "List Item {$i}");
            $link = $settings["item{$i}_link"] ?? '';

            $itemHtml = "<div style=\"display: flex; align-items: center; gap: 0.75rem; margin-bottom: {$spacing}px;\">";
            $itemHtml .= "<span style=\"font-size: {$iconSize}px; color: {$iconColor};\">{$icon}</span>";
            $itemHtml .= "<span style=\"color: {$textColor};\">{$text}</span>";
            $itemHtml .= "</div>";

            if ($link) {
                $html .= "<a href=\"" . e($link) . "\" style=\"text-decoration: none;\">{$itemHtml}</a>";
            } else {
                $html .= $itemHtml;
            }
        }

        $html .= '</div>';

        return $html;
    }

    protected function renderTextPath(array $settings): string
    {
        $text = e($settings['text'] ?? 'Curved Text');
        $color = $settings['text_color'] ?? '#1f2937';
        $fontSize = $settings['font_size'] ?? 24;
        $fontWeight = $settings['font_weight'] ?? '400';
        $pathType = $settings['path_type'] ?? 'wave';

        $paths = [
            'wave' => 'M 0 50 Q 125 20, 250 50 T 500 50',
            'circle' => 'M 50 50 m -40 0 a 40 40 0 1 1 80 0 a 40 40 0 1 1 -80 0',
            'arch' => 'M 50 80 Q 250 20, 450 80',
        ];

        $path = $paths[$pathType] ?? $paths['wave'];

        return "<div style=\"text-align: center;\">
            <svg viewBox=\"0 0 500 100\" style=\"max-width: 500px; margin: 0 auto; width: 100%;\">
                <defs>
                    <path id=\"textPath\" d=\"{$path}\" />
                </defs>
                <text style=\"font-size: {$fontSize}px; fill: {$color}; font-weight: {$fontWeight};\">
                    <textPath href=\"#textPath\" startOffset=\"50%\" text-anchor=\"middle\">{$text}</textPath>
                </text>
            </svg>
        </div>";
    }

    protected function renderImageCarousel(array $settings): string
    {
        $slidesToShow = $settings['slides_to_show'] ?? 3;
        $spacing = $settings['image_spacing'] ?? 10;
        $borderRadius = $settings['border_radius'] ?? 8;
        $autoplay = $settings['autoplay'] ?? true;
        $autoplaySpeed = $settings['autoplay_speed'] ?? 3000;
        $infiniteLoop = $settings['infinite_loop'] ?? true;
        $showArrows = $settings['show_arrows'] ?? true;
        $showDots = $settings['show_dots'] ?? true;
        $arrowColor = $settings['arrow_color'] ?? '#ffffff';
        $dotColor = $settings['dot_color'] ?? '#4f46e5';

        // Generate unique ID for this carousel widget
        $carouselId = 'carousel-' . uniqid();

        // Collect all images
        $images = [];
        for ($i = 1; $i <= 4; $i++) {
            if (!empty($settings["image{$i}"])) {
                $images[] = e($settings["image{$i}"]);
            }
        }

        if (empty($images)) {
            return '';
        }

        // CSS for carousel
        $css = "<style>
            #{$carouselId} {
                position: relative;
                overflow: hidden;
            }
            #{$carouselId} .carousel-track {
                display: flex;
                gap: {$spacing}px;
                transition: transform 0.5s ease;
            }
            #{$carouselId} .carousel-slide {
                flex-shrink: 0;
                width: calc((100% - " . ($spacing * ($slidesToShow - 1)) . "px) / {$slidesToShow});
            }
            #{$carouselId} .carousel-slide img {
                width: 100%;
                height: 12rem;
                object-fit: cover;
                border-radius: {$borderRadius}px;
            }
            #{$carouselId} .carousel-arrow {
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
                width: 2rem;
                height: 2rem;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                background-color: rgba(0,0,0,0.5);
                color: {$arrowColor};
                border: none;
                cursor: pointer;
                font-size: 1rem;
                z-index: 10;
            }
            #{$carouselId} .carousel-arrow:hover {
                background-color: rgba(0,0,0,0.7);
            }
            #{$carouselId} .carousel-arrow-prev {
                left: 0.5rem;
            }
            #{$carouselId} .carousel-arrow-next {
                right: 0.5rem;
            }
            #{$carouselId} .carousel-dots {
                display: flex;
                justify-content: center;
                gap: 0.5rem;
                margin-top: 1rem;
            }
            #{$carouselId} .carousel-dot {
                width: 0.5rem;
                height: 0.5rem;
                border-radius: 50%;
                background-color: {$dotColor};
                opacity: 0.3;
                cursor: pointer;
                border: none;
            }
            #{$carouselId} .carousel-dot.active {
                opacity: 1;
            }
        </style>";

        $html = $css;
        $html .= "<div id=\"{$carouselId}\" class=\"image-carousel-widget\" data-widget-type=\"image-carousel\" data-slides-to-show=\"{$slidesToShow}\" data-autoplay=\"" . ($autoplay ? 'true' : 'false') . "\" data-autoplay-speed=\"{$autoplaySpeed}\" data-infinite=\"" . ($infiniteLoop ? 'true' : 'false') . "\">";

        // Carousel track with all images
        $html .= '<div class="carousel-track">';
        foreach ($images as $index => $imageUrl) {
            $html .= "<div class=\"carousel-slide\" data-slide=\"{$index}\"><img src=\"{$imageUrl}\" alt=\"Carousel image " . ($index + 1) . "\" /></div>";
        }
        $html .= '</div>';

        // Navigation arrows
        if ($showArrows) {
            $html .= '<button class="carousel-arrow carousel-arrow-prev" aria-label="Previous">‹</button>';
            $html .= '<button class="carousel-arrow carousel-arrow-next" aria-label="Next">›</button>';
        }

        // Dots navigation
        if ($showDots) {
            $html .= '<div class="carousel-dots">';
            $numDots = max(1, count($images) - $slidesToShow + 1);
            for ($i = 0; $i < $numDots; $i++) {
                $activeClass = $i === 0 ? ' active' : '';
                $html .= "<button class=\"carousel-dot{$activeClass}\" data-index=\"{$i}\" aria-label=\"Go to slide " . ($i + 1) . "\"></button>";
            }
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    protected function renderBasicGallery(array $settings): string
    {
        $columns = $settings['columns'] ?? 3;
        $gap = $settings['gap'] ?? 10;
        $borderRadius = $settings['border_radius'] ?? 8;
        $hoverEffect = $settings['hover_effect'] ?? 'zoom';
        $enableLightbox = $settings['enable_lightbox'] ?? true;

        // Generate unique ID for this gallery widget
        $galleryId = 'gallery-' . uniqid();

        // CSS for gallery
        $hoverCss = match ($hoverEffect) {
            'zoom' => 'transform: scale(1.1);',
            'grayscale' => 'filter: grayscale(100%);',
            'blur' => 'filter: blur(3px);',
            'brightness' => 'filter: brightness(130%);',
            default => '',
        };

        $css = "<style>
            #{$galleryId} {
                display: grid;
                grid-template-columns: repeat({$columns}, 1fr);
                gap: {$gap}px;
            }
            #{$galleryId} .gallery-item {
                position: relative;
                overflow: hidden;
                border-radius: {$borderRadius}px;
                cursor: " . ($enableLightbox ? 'pointer' : 'default') . ";
            }
            #{$galleryId} .gallery-item img {
                width: 100%;
                height: 10rem;
                object-fit: cover;
                transition: all 0.3s ease;
            }
            #{$galleryId} .gallery-item:hover img {
                {$hoverCss}
            }
        </style>";

        $html = $css;
        $html .= "<div id=\"{$galleryId}\" class=\"basic-gallery-widget\" data-widget-type=\"basic-gallery\" data-lightbox=\"" . ($enableLightbox ? 'true' : 'false') . "\">";

        for ($i = 1; $i <= 6; $i++) {
            if (!empty($settings["image{$i}"])) {
                $imageUrl = e($settings["image{$i}"]);
                $html .= "<div class=\"gallery-item\" data-src=\"{$imageUrl}\"><img src=\"{$imageUrl}\" alt=\"Gallery image {$i}\" /></div>";
            }
        }

        $html .= '</div>';

        return $html;
    }

    protected function renderSoundCloud(array $settings): string
    {
        $url = $settings['url'] ?? '';
        if (empty($url)) {
            return '';
        }

        $height = $settings['height'] ?? 166;
        $visual = $settings['visual'] ? 'true' : 'false';
        $autoPlay = $settings['auto_play'] ? 'true' : 'false';
        $buying = $settings['buying'] ? 'true' : 'false';
        $sharing = $settings['sharing'] ? 'true' : 'false';
        $download = $settings['download'] ? 'true' : 'false';

        $embedUrl = "https://w.soundcloud.com/player/?url=" . urlencode($url) . "&visual={$visual}&auto_play={$autoPlay}&buying={$buying}&sharing={$sharing}&download={$download}";

        return "<iframe width=\"100%\" height=\"{$height}\" scrolling=\"no\" frameborder=\"no\" allow=\"autoplay\" src=\"{$embedUrl}\"></iframe>";
    }

    protected function renderContainer(array $settings): string
    {
        $htmlTag = $settings['html_tag'] ?? 'div';
        $contentWidth = $settings['content_width'] ?? 'boxed';
        $width = $settings['width'] ?? 1140;
        $minHeight = $settings['min_height'] ?? 0;

        // Flexbox settings
        $flexDirection = $settings['flex_direction'] ?? 'row';
        $justifyContent = $settings['justify_content'] ?? 'flex-start';
        $alignItems = $settings['align_items'] ?? 'flex-start';
        $flexWrap = $settings['flex_wrap'] ?? 'nowrap';

        // Gaps
        $gaps = $settings['gaps'] ?? ['column' => 20, 'row' => 20];
        $columnGap = is_array($gaps) ? ($gaps['column'] ?? 20) : 20;
        $rowGap = is_array($gaps) ? ($gaps['row'] ?? 20) : 20;

        $containerClass = $contentWidth === 'boxed' ? "max-width: {$width}px; margin: 0 auto; padding: 0 1rem;" : 'width: 100%;';

        $flexStyles = "display: flex; flex-direction: {$flexDirection}; justify-content: {$justifyContent}; align-items: {$alignItems}; flex-wrap: {$flexWrap}; column-gap: {$columnGap}px; row-gap: {$rowGap}px;";

        return "<{$htmlTag} style=\"min-height: {$minHeight}px; {$containerClass} {$flexStyles}\">
            <!-- Container content goes here -->
        </{$htmlTag}>";
    }

    protected function renderInnerSection(array $settings): string
    {
        $columns = $settings['columns'] ?? 2;
        $gap = $settings['column_gap'] ?? 20;

        return "<div style=\"display: grid; grid-template-columns: repeat({$columns}, 1fr); gap: {$gap}px;\">
            <!-- Inner section columns go here -->
        </div>";
    }

    protected function renderMenuAnchor(array $settings): string
    {
        $anchorId = e($settings['anchor_id'] ?? 'anchor');
        return "<div id=\"{$anchorId}\" style=\"height: 0;\"></div>";
    }

    protected function renderSidebar(array $settings): string
    {
        $sidebarId = $settings['sidebar_id'] ?? 'primary';
        return "<!-- Sidebar: {$sidebarId} -->";
    }

    protected function renderHtml(array $settings): string
    {
        $htmlCode = $settings['html_code'] ?? '<div class="custom-html"><p>Add your custom HTML here</p></div>';

        // ✅ Sanitize custom HTML to prevent XSS
        return $this->htmlSanitizer->sanitize($htmlCode);
    }

    protected function renderShortcode(array $settings): string
    {
        $shortcode = e($settings['shortcode'] ?? '[shortcode]');
        // In a real implementation, you would process the shortcode here
        return "<!-- Shortcode: {$shortcode} -->";
    }

    // Pro widget renderers (fully implemented)

    protected function renderImageBox(array $settings): string
    {
        $imageUrl = $settings['image_url'] ?? '';
        $title = e($settings['title'] ?? 'Image Box');
        $description = e($settings['description'] ?? 'Click here to add your own text.');
        $titleColor = $settings['title_color'] ?? '#1f2937';
        $hoverTitleColor = $settings['hover_title_color'] ?? '';
        $descColor = $settings['description_color'] ?? '#6b7280';
        $alignment = $settings['alignment'] ?? 'center';
        $imagePosition = $settings['image_position'] ?? 'top';
        $imageSpacing = $settings['image_spacing'] ?? 15;
        $imageWidth = $settings['image_width'] ?? 100;
        $imageHeight = $settings['image_height'] ?? 160;
        $hoverAnimation = $settings['hover_animation'] ?? 'none';
        $verticalAlign = $settings['content_vertical_alignment'] ?? 'top';
        $link = $settings['link'] ?? '';

        // Vertical alignment mapping
        $alignItemsMap = [
            'top' => 'flex-start',
            'center' => 'center',
            'bottom' => 'flex-end',
        ];
        $alignItems = $alignItemsMap[$verticalAlign] ?? 'flex-start';

        // Generate unique ID for hover effects
        $boxId = 'image-box-' . uniqid();

        // Build hover CSS
        $hoverCss = '<style>';
        if ($hoverTitleColor) {
            $hoverCss .= "#{$boxId}:hover .image-box-title{color:{$hoverTitleColor}!important;}";
        }
        if ($hoverAnimation !== 'none') {
            $hoverStyles = match ($hoverAnimation) {
                'zoom' => 'transform: scale(1.1);',
                'zoom_out' => 'transform: scale(0.9);',
                'grayscale' => 'filter: grayscale(100%);',
                'blur' => 'filter: blur(3px);',
                default => '',
            };
            $hoverCss .= "#{$boxId}:hover .image-box-img{{$hoverStyles}}";
        }
        $hoverCss .= '</style>';

        // Build image HTML
        $imgHtml = '';
        if ($imageUrl) {
            $imageUrl = e($imageUrl);
            $imgHtml = "<img class=\"image-box-img\" src=\"{$imageUrl}\" style=\"width: {$imageWidth}%; height: {$imageHeight}px; object-fit: cover; border-radius: 0.5rem; transition: all 0.3s ease;\" alt=\"\" />";
        } else {
            $imgHtml = '<div class="image-box-img" style="width: 100%; height: ' . $imageHeight . 'px; background-color: #e5e7eb; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; color: #9ca3af; font-size: 2.25rem; transition: all 0.3s ease;">🖼️</div>';
        }

        $contentHtml = "<div>
            <h4 class=\"image-box-title\" style=\"color: {$titleColor}; font-weight: 600; font-size: 1.125rem; margin: 0; transition: color 0.3s ease;\">{$title}</h4>
            <p style=\"color: {$descColor}; margin-top: 0.5rem;\">{$description}</p>
        </div>";

        // Build layout based on position
        $html = $hoverCss;

        if ($imagePosition === 'top') {
            $html .= "<div id=\"{$boxId}\" style=\"text-align: {$alignment};\">";
            $html .= "<div style=\"margin-bottom: {$imageSpacing}px;\">{$imgHtml}</div>";
            $html .= $contentHtml;
            $html .= "</div>";
        } elseif ($imagePosition === 'left') {
            $html .= "<div id=\"{$boxId}\" style=\"display: flex; align-items: {$alignItems}; text-align: left;\">";
            $html .= "<div style=\"margin-right: {$imageSpacing}px; flex-shrink: 0; width: 40%;\">{$imgHtml}</div>";
            $html .= "<div style=\"flex: 1;\">{$contentHtml}</div>";
            $html .= "</div>";
        } else {
            // right
            $html .= "<div id=\"{$boxId}\" style=\"display: flex; align-items: {$alignItems}; text-align: right; flex-direction: row-reverse;\">";
            $html .= "<div style=\"margin-left: {$imageSpacing}px; flex-shrink: 0; width: 40%;\">{$imgHtml}</div>";
            $html .= "<div style=\"flex: 1;\">{$contentHtml}</div>";
            $html .= "</div>";
        }

        // Wrap with link if provided
        if (!empty($link)) {
            $linkUrl = e($link);
            $html = "<a href=\"{$linkUrl}\" style=\"text-decoration: none; color: inherit; display: block;\">{$html}</a>";
        }

        return $html;
    }

    protected function renderStarRating(array $settings): string
    {
        $rating = $settings['rating'] ?? 4;
        $size = $settings['size'] ?? 24;
        $color = $settings['color'] ?? '#fbbf24';
        $unmarkedColor = $settings['unmarked_color'] ?? '#d1d5db';
        $title = e($settings['title'] ?? '');
        $alignment = $settings['alignment'] ?? 'left';

        $html = "<div style=\"text-align: {$alignment};\">";
        $html .= "<div style=\"font-size: {$size}px;\">";

        for ($i = 1; $i <= 5; $i++) {
            $starColor = $i <= $rating ? $color : $unmarkedColor;
            $html .= "<span style=\"color: {$starColor};\">★</span>";
        }

        $html .= "</div>";

        if ($title) {
            $html .= "<div style=\"font-size: 0.875rem; color: #6b7280; margin-top: 0.25rem;\">{$title}</div>";
        }

        $html .= "</div>";

        return $html;
    }

    protected function renderTabs(array $settings): string
    {
        $tabColor = $settings['tab_color'] ?? '#4f46e5';
        $contentColor = $settings['content_color'] ?? '#1f2937';

        $tab1Title = e($settings['tab1_title'] ?? 'Tab 1');
        $tab2Title = e($settings['tab2_title'] ?? 'Tab 2');
        $tab3Title = e($settings['tab3_title'] ?? 'Tab 3');
        $tab1Content = $settings['tab1_content'] ?? '<p>Tab 1 content goes here.</p>';
        $tab2Content = $settings['tab2_content'] ?? '<p>Tab 2 content goes here.</p>';
        $tab3Content = $settings['tab3_content'] ?? '<p>Tab 3 content goes here.</p>';

        // ✅ Sanitize HTML content
        $tab1Content = $this->htmlSanitizer->sanitize($tab1Content);
        $tab2Content = $this->htmlSanitizer->sanitize($tab2Content);
        $tab3Content = $this->htmlSanitizer->sanitize($tab3Content);

        // Generate unique ID for this tabs widget
        $tabsId = 'tabs-' . uniqid();

        // CSS for tabs
        $css = "<style>
            #{$tabsId} .tabs-nav button {
                padding: 0.5rem 1rem;
                border: none;
                background: none;
                cursor: pointer;
                color: #6b7280;
                border-bottom: 2px solid transparent;
                transition: all 0.3s ease;
            }
            #{$tabsId} .tabs-nav button.active {
                color: {$tabColor};
                border-bottom-color: {$tabColor};
                font-weight: 500;
            }
            #{$tabsId} .tab-content {
                display: none;
                padding: 1rem;
                color: {$contentColor};
            }
            #{$tabsId} .tab-content.active {
                display: block;
            }
        </style>";

        $html = $css;
        $html .= "<div id=\"{$tabsId}\" class=\"tabs-widget\" data-widget-type=\"tabs\">";
        $html .= '<div class="tabs-nav" style="display: flex; border-bottom: 1px solid #e5e7eb;">';
        $html .= "<button class=\"active\" data-tab=\"1\">{$tab1Title}</button>";
        $html .= "<button data-tab=\"2\">{$tab2Title}</button>";
        $html .= "<button data-tab=\"3\">{$tab3Title}</button>";
        $html .= '</div>';
        $html .= "<div class=\"tab-content active\" data-tab-content=\"1\">{$tab1Content}</div>";
        $html .= "<div class=\"tab-content\" data-tab-content=\"2\">{$tab2Content}</div>";
        $html .= "<div class=\"tab-content\" data-tab-content=\"3\">{$tab3Content}</div>";
        $html .= '</div>';

        return $html;
    }

    protected function renderAccordion(array $settings): string
    {
        $titleBg = $settings['title_background'] ?? '#f3f4f6';
        $titleColor = $settings['title_color'] ?? '#1f2937';
        $contentColor = $settings['content_color'] ?? '#4b5563';
        $firstOpen = $settings['first_open'] ?? true;

        // Generate unique ID for this accordion widget
        $accordionId = 'accordion-' . uniqid();

        // CSS for accordion
        $css = "<style>
            #{$accordionId} .accordion-item {
                border-bottom: 1px solid #e5e7eb;
            }
            #{$accordionId} .accordion-header {
                background-color: {$titleBg};
                color: {$titleColor};
                padding: 0.75rem 1rem;
                font-weight: 500;
                display: flex;
                justify-content: space-between;
                align-items: center;
                cursor: pointer;
                transition: background-color 0.3s ease;
            }
            #{$accordionId} .accordion-header:hover {
                background-color: #e5e7eb;
            }
            #{$accordionId} .accordion-content {
                display: none;
                padding: 0.75rem 1rem;
                color: {$contentColor};
            }
            #{$accordionId} .accordion-content.active {
                display: block;
            }
            #{$accordionId} .accordion-icon {
                transition: transform 0.3s ease;
            }
            #{$accordionId} .accordion-header.active .accordion-icon {
                transform: rotate(180deg);
            }
        </style>";

        $html = $css;
        $html .= "<div id=\"{$accordionId}\" class=\"accordion-widget\" data-widget-type=\"accordion\" style=\"border: 1px solid #e5e7eb; border-radius: 0.5rem; overflow: hidden;\">";

        for ($i = 1; $i <= 3; $i++) {
            $title = e($settings["item{$i}_title"] ?? "Accordion Item {$i}");
            $content = $settings["item{$i}_content"] ?? "<p>Content for accordion item {$i}.</p>";

            // ✅ Sanitize HTML content
            $content = $this->htmlSanitizer->sanitize($content);

            $isActive = ($i === 1 && $firstOpen);
            $activeClass = $isActive ? ' active' : '';

            $html .= '<div class="accordion-item">';
            $html .= "<div class=\"accordion-header{$activeClass}\" data-accordion-index=\"{$i}\">";
            $html .= "<span>{$title}</span>";
            $html .= '<span class="accordion-icon">▼</span>';
            $html .= '</div>';
            $html .= "<div class=\"accordion-content{$activeClass}\" data-accordion-content=\"{$i}\">{$content}</div>";
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    protected function renderCountdown(array $settings): string
    {
        $numberSize = $settings['number_size'] ?? 48;
        $numberColor = $settings['number_color'] ?? '#1f2937';
        $labelColor = $settings['label_color'] ?? '#6b7280';
        $showDays = $settings['show_days'] ?? true;
        $showHours = $settings['show_hours'] ?? true;
        $showMinutes = $settings['show_minutes'] ?? true;
        $showSeconds = $settings['show_seconds'] ?? true;
        $showLabels = $settings['show_labels'] ?? true;
        $dueDate = $settings['due_date'] ?? '2025-12-31';
        $dueTime = $settings['due_time'] ?? '23:59';

        // Generate unique ID for this countdown widget
        $countdownId = 'countdown-' . uniqid();

        $html = "<div id=\"{$countdownId}\" class=\"countdown-widget\" data-widget-type=\"countdown\" data-due-date=\"{$dueDate}\" data-due-time=\"{$dueTime}\" style=\"display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;\">";

        $units = [
            ['show' => $showDays, 'label' => 'Days', 'class' => 'days'],
            ['show' => $showHours, 'label' => 'Hours', 'class' => 'hours'],
            ['show' => $showMinutes, 'label' => 'Minutes', 'class' => 'minutes'],
            ['show' => $showSeconds, 'label' => 'Seconds', 'class' => 'seconds'],
        ];

        foreach ($units as $unit) {
            if ($unit['show']) {
                $html .= '<div style="text-align: center;">';
                $html .= "<div class=\"countdown-{$unit['class']}\" style=\"font-size: {$numberSize}px; color: {$numberColor}; font-weight: bold; line-height: 1;\">00</div>";

                if ($showLabels) {
                    $html .= "<div style=\"color: {$labelColor}; font-size: 0.875rem; margin-top: 0.25rem;\">{$unit['label']}</div>";
                }

                $html .= '</div>';
            }
        }

        $html .= '</div>';

        return $html;
    }

    protected function renderGoogleMaps(array $settings): string
    {
        $address = $settings['address'] ?? 'New York, USA';
        $zoom = $settings['zoom'] ?? 14;
        $height = $settings['height'] ?? 400;

        // URL encode the address for the embed
        $encodedAddress = urlencode($address);

        // Use Google Maps embed URL (no API key required for basic embed)
        $embedUrl = "https://www.google.com/maps/embed/v1/place?key=&q={$encodedAddress}&zoom={$zoom}";

        // Fallback to OpenStreetMap iframe (no API key needed)
        $osmUrl = "https://www.openstreetmap.org/export/embed.html?bbox=-180%2C-85%2C180%2C85&layer=mapnik&marker=" . urlencode($address);

        // Use Google Maps static embed (works without API key)
        $googleEmbedUrl = "https://maps.google.com/maps?q=" . $encodedAddress . "&t=&z={$zoom}&ie=UTF8&iwloc=&output=embed";

        $html = "<div style=\"height: {$height}px; border-radius: 0.5rem; overflow: hidden;\">";
        $html .= "<iframe
            src=\"{$googleEmbedUrl}\"
            width=\"100%\"
            height=\"100%\"
            style=\"border: 0;\"
            allowfullscreen=\"\"
            loading=\"lazy\"
            referrerpolicy=\"no-referrer-when-downgrade\"
            title=\"Map showing {$address}\">
        </iframe>";
        $html .= '</div>';

        return $html;
    }

    protected function renderCallToAction(array $settings): string
    {
        $title = e($settings['title'] ?? 'This is the heading');
        $description = e($settings['description'] ?? 'Click here to add your own text and edit me.');
        $buttonText = e($settings['button_text'] ?? 'Click Here');
        $buttonLink = e($settings['button_link'] ?? '#');
        $titleColor = $settings['title_color'] ?? '#1f2937';
        $descColor = $settings['description_color'] ?? '#4b5563';
        $buttonBg = $settings['button_background'] ?? '#4f46e5';
        $buttonColor = $settings['button_color'] ?? '#ffffff';
        $ribbonText = e($settings['ribbon_text'] ?? '');
        $ribbonColor = $settings['ribbon_color'] ?? '#ef4444';

        // Generate unique ID for hover effects
        $ctaId = 'cta-' . uniqid();

        $css = "<style>
            #{$ctaId} .cta-button {
                background-color: {$buttonBg};
                color: {$buttonColor};
                padding: 0.5rem 1.5rem;
                border-radius: 0.375rem;
                font-weight: 500;
                border: none;
                cursor: pointer;
                text-decoration: none;
                display: inline-block;
                transition: all 0.3s ease;
            }
            #{$ctaId} .cta-button:hover {
                opacity: 0.9;
                transform: translateY(-1px);
            }
        </style>";

        $html = $css;
        $html .= "<div id=\"{$ctaId}\" style=\"position: relative; padding: 2rem; border-radius: 0.5rem;\">";

        if ($ribbonText) {
            $html .= "<div style=\"position: absolute; top: 0; right: 0; background-color: {$ribbonColor}; color: white; padding: 0.25rem 0.75rem; font-size: 0.875rem; font-weight: 500;\">{$ribbonText}</div>";
        }

        $html .= "<h3 style=\"color: {$titleColor}; font-size: 1.5rem; font-weight: bold; margin: 0 0 0.5rem 0;\">{$title}</h3>";
        $html .= "<p style=\"color: {$descColor}; margin: 0 0 1rem 0;\">{$description}</p>";
        $html .= "<a href=\"{$buttonLink}\" class=\"cta-button\">{$buttonText}</a>";
        $html .= '</div>';

        return $html;
    }

    protected function renderFlipBox(array $settings): string
    {
        $height = $settings['height'] ?? 300;
        $flipDirection = $settings['flip_direction'] ?? 'horizontal';

        // Front side
        $frontBg = $settings['front_background'] ?? '#ffffff';
        $frontColor = $settings['front_color'] ?? '#1f2937';
        $frontIcon = e($settings['front_icon'] ?? '⚡');
        $frontTitle = e($settings['front_title'] ?? 'Front Title');
        $frontDesc = e($settings['front_description'] ?? 'This is the front content.');

        // Back side
        $backBg = $settings['back_background'] ?? '#4f46e5';
        $backColor = $settings['back_color'] ?? '#ffffff';
        $backIcon = e($settings['back_icon'] ?? '✨');
        $backTitle = e($settings['back_title'] ?? 'Back Title');
        $backDesc = e($settings['back_description'] ?? 'This is the back content.');
        $buttonText = e($settings['button_text'] ?? 'Click Here');
        $buttonLink = e($settings['button_link'] ?? '#');

        // Generate unique ID for this flip box
        $flipId = 'flip-box-' . uniqid();

        // Determine flip transform based on direction
        $flipTransform = $flipDirection === 'vertical' ? 'rotateX(180deg)' : 'rotateY(180deg)';
        $backFlipTransform = $flipDirection === 'vertical' ? 'rotateX(180deg)' : 'rotateY(180deg)';

        // Build CSS for flip animation
        $css = "<style>
            #{$flipId} {
                perspective: 1000px;
                height: {$height}px;
            }
            #{$flipId} .flip-box-inner {
                position: relative;
                width: 100%;
                height: 100%;
                transition: transform 0.6s;
                transform-style: preserve-3d;
            }
            #{$flipId}:hover .flip-box-inner {
                transform: {$flipTransform};
            }
            #{$flipId} .flip-box-front,
            #{$flipId} .flip-box-back {
                position: absolute;
                width: 100%;
                height: 100%;
                backface-visibility: hidden;
                border-radius: 0.5rem;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                text-align: center;
                padding: 1.5rem;
                box-sizing: border-box;
            }
            #{$flipId} .flip-box-front {
                background-color: {$frontBg};
                color: {$frontColor};
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            #{$flipId} .flip-box-back {
                background-color: {$backBg};
                color: {$backColor};
                transform: {$backFlipTransform};
            }
        </style>";

        $html = $css;
        $html .= "<div id=\"{$flipId}\">";
        $html .= '<div class="flip-box-inner">';

        // Front side
        $html .= '<div class="flip-box-front">';
        $html .= "<div style=\"font-size: 2.25rem; margin-bottom: 1rem;\">{$frontIcon}</div>";
        $html .= "<h4 style=\"font-weight: 600; font-size: 1.125rem; margin: 0 0 0.5rem 0;\">{$frontTitle}</h4>";
        $html .= "<p style=\"margin: 0; font-size: 0.875rem; opacity: 0.8;\">{$frontDesc}</p>";
        $html .= '</div>';

        // Back side
        $html .= '<div class="flip-box-back">';
        $html .= "<div style=\"font-size: 2.25rem; margin-bottom: 1rem;\">{$backIcon}</div>";
        $html .= "<h4 style=\"font-weight: 600; font-size: 1.125rem; margin: 0 0 0.5rem 0;\">{$backTitle}</h4>";
        $html .= "<p style=\"margin: 0 0 1rem 0; font-size: 0.875rem; opacity: 0.8;\">{$backDesc}</p>";
        if ($buttonText) {
            $html .= "<a href=\"{$buttonLink}\" style=\"display: inline-block; background-color: rgba(255,255,255,0.2); color: inherit; padding: 0.5rem 1.5rem; border-radius: 0.375rem; text-decoration: none; font-weight: 500;\">{$buttonText}</a>";
        }
        $html .= '</div>';

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    protected function renderPriceTable(array $settings): string
    {
        $title = e($settings['title'] ?? 'Pro');
        $price = e($settings['price'] ?? '$49');
        $period = e($settings['period'] ?? '/month');
        $features = $settings['features'] ?? "10 Projects\n50GB Storage\nPriority Support\nCustom Domain";
        $buttonText = e($settings['button_text'] ?? 'Get Started');
        $headerBg = $settings['header_background'] ?? '#4f46e5';
        $headerColor = $settings['header_color'] ?? '#ffffff';
        $priceColor = $settings['price_color'] ?? '#1f2937';
        $featuresColor = $settings['features_color'] ?? '#4b5563';
        $buttonBg = $settings['button_background'] ?? '#4f46e5';
        $buttonColor = $settings['button_color'] ?? '#ffffff';
        $featured = $settings['featured'] ?? false;
        $ribbonText = e($settings['ribbon_text'] ?? 'Popular');

        $html = '<div style="position: relative; border: 1px solid #e5e7eb; border-radius: 0.5rem; overflow: hidden;">';

        if ($featured && $ribbonText) {
            $html .= "<div style=\"position: absolute; top: 1rem; right: -0.5rem; background-color: #4f46e5; color: white; padding: 0.25rem 0.75rem; font-size: 0.75rem; font-weight: 500; box-shadow: 0 2px 4px rgba(0,0,0,0.1);\">{$ribbonText}</div>";
        }

        $html .= "<div style=\"background-color: {$headerBg}; color: {$headerColor}; padding: 1.5rem; text-align: center;\">";
        $html .= "<h3 style=\"font-size: 1.25rem; font-weight: bold; margin: 0;\">{$title}</h3>";
        $html .= '</div>';

        $html .= '<div style="padding: 1.5rem;">';
        $html .= "<div style=\"text-align: center; margin-bottom: 1.5rem;\">";
        $html .= "<div style=\"font-size: 2.25rem; font-weight: bold; color: {$priceColor};\">{$price}<span style=\"font-size: 1.125rem; font-weight: normal;\">{$period}</span></div>";
        $html .= "</div>";

        $html .= "<ul style=\"list-style: none; padding: 0; margin: 0 0 1.5rem 0; color: {$featuresColor};\">";
        foreach (explode("\n", $features) as $feature) {
            if (trim($feature)) {
                $html .= "<li style=\"display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;\"><span style=\"color: #10b981; font-size: 1.125rem;\">✓</span> <span>" . e(trim($feature)) . "</span></li>";
            }
        }
        $html .= '</ul>';

        // Button with link
        $buttonLink = e($settings['button_link'] ?? '#');
        $html .= "<a href=\"{$buttonLink}\" style=\"display: block; background-color: {$buttonBg}; color: {$buttonColor}; width: 100%; padding: 0.75rem; border-radius: 0.375rem; font-weight: 500; text-decoration: none; text-align: center; box-sizing: border-box; transition: opacity 0.3s ease;\">{$buttonText}</a>";
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    protected function renderForm(array $settings): string
    {
        $formName = e($settings['form_name'] ?? 'Contact Form');
        $showLabels = $settings['show_labels'] ?? true;
        $nameField = $settings['name_field'] ?? true;
        $emailField = $settings['email_field'] ?? true;
        $messageField = $settings['message_field'] ?? true;
        $buttonText = e($settings['button_text'] ?? 'Send Message');
        $successMessage = e($settings['success_message'] ?? 'Thank you! Your message has been sent.');
        $fieldBg = $settings['field_background'] ?? '#ffffff';
        $fieldBorder = $settings['field_border'] ?? '#d1d5db';
        $fieldText = $settings['field_text'] ?? '#1f2937';
        $buttonBg = $settings['button_background'] ?? '#4f46e5';
        $buttonTextColor = $settings['button_text'] ?? '#ffffff';
        $spacing = $settings['spacing'] ?? 16;

        // Generate unique ID for this form widget
        $formId = 'form-' . uniqid();

        // CSS for form
        $css = "<style>
            #{$formId} .form-message {
                display: none;
                padding: 1rem;
                border-radius: 0.375rem;
                margin-bottom: 1rem;
            }
            #{$formId} .form-message.success {
                display: block;
                background-color: #d1fae5;
                color: #065f46;
            }
            #{$formId} .form-message.error {
                display: block;
                background-color: #fee2e2;
                color: #991b1b;
            }
            #{$formId} input, #{$formId} textarea {
                width: 100%;
                padding: 0.5rem 1rem;
                border: 1px solid {$fieldBorder};
                background-color: {$fieldBg};
                color: {$fieldText};
                border-radius: 0.375rem;
                margin-bottom: {$spacing}px;
                box-sizing: border-box;
            }
            #{$formId} input:focus, #{$formId} textarea:focus {
                outline: none;
                border-color: {$buttonBg};
                box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.2);
            }
            #{$formId} button[type=\"submit\"] {
                background-color: {$buttonBg};
                color: {$buttonTextColor};
                padding: 0.75rem 1.5rem;
                border-radius: 0.375rem;
                font-weight: 500;
                margin-top: 1rem;
                border: none;
                cursor: pointer;
                transition: background-color 0.3s ease;
            }
            #{$formId} button[type=\"submit\"]:hover {
                opacity: 0.9;
            }
            #{$formId} button[type=\"submit\"]:disabled {
                opacity: 0.6;
                cursor: not-allowed;
            }
        </style>";

        $html = $css;
        $html .= "<div id=\"{$formId}\" class=\"form-widget\" data-widget-type=\"form\" data-success-message=\"{$successMessage}\">";

        if ($formName) {
            $html .= "<h3 style=\"font-size: 1.25rem; font-weight: 600; margin: 0 0 1rem 0;\">{$formName}</h3>";
        }

        // Form with action URL pointing to form submission endpoint
        $html .= '<div class="form-message"></div>';
        $html .= '<form class="widget-form" method="POST" action="/api/forms/submit">';
        $html .= '<input type="hidden" name="form_name" value="' . $formName . '" />';

        if ($nameField) {
            if ($showLabels) {
                $html .= '<label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem; color: #374151;">Name</label>';
            }
            $html .= '<input type="text" name="name" placeholder="Your Name" required />';
        }

        if ($emailField) {
            if ($showLabels) {
                $html .= '<label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem; color: #374151;">Email</label>';
            }
            $html .= '<input type="email" name="email" placeholder="your@email.com" required />';
        }

        if ($messageField) {
            if ($showLabels) {
                $html .= '<label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem; color: #374151;">Message</label>';
            }
            $html .= '<textarea name="message" placeholder="Your Message" rows="4" style="resize: vertical;" required></textarea>';
        }

        $html .= "<button type=\"submit\">{$buttonText}</button>";
        $html .= '</form>';
        $html .= '</div>';

        return $html;
    }

    protected function renderSlider(array $settings): string
    {
        $height = $settings['height'] ?? 500;
        $titleColor = $settings['title_color'] ?? '#ffffff';
        $descColor = $settings['description_color'] ?? '#f3f4f6';
        $buttonBg = $settings['button_background'] ?? '#4f46e5';
        $buttonColor = $settings['button_color'] ?? '#ffffff';
        $overlayColor = $settings['overlay_color'] ?? 'rgba(0,0,0,0.3)';
        $showArrows = $settings['show_arrows'] ?? true;
        $showDots = $settings['show_dots'] ?? true;
        $arrowsColor = $settings['arrows_color'] ?? '#ffffff';
        $dotsColor = $settings['dots_color'] ?? '#ffffff';
        $autoplay = $settings['autoplay'] ?? true;
        $autoplaySpeed = $settings['autoplay_speed'] ?? 3000;

        // Generate unique ID for this slider widget
        $sliderId = 'slider-' . uniqid();

        // Collect all slides
        $slides = [];
        for ($i = 1; $i <= 3; $i++) {
            $slides[] = [
                'image' => $settings["slide{$i}_image"] ?? '',
                'title' => e($settings["slide{$i}_title"] ?? "Slide {$i}"),
                'description' => e($settings["slide{$i}_description"] ?? "This is slide {$i} content."),
                'button' => e($settings["slide{$i}_button"] ?? ''),
                'link' => e($settings["slide{$i}_link"] ?? '#'),
            ];
        }

        // CSS for slider
        $css = "<style>
            #{$sliderId} {
                position: relative;
                overflow: hidden;
                border-radius: 0.5rem;
                height: {$height}px;
            }
            #{$sliderId} .slider-track {
                display: flex;
                transition: transform 0.5s ease;
                height: 100%;
            }
            #{$sliderId} .slider-slide {
                flex-shrink: 0;
                width: 100%;
                height: 100%;
                position: relative;
                display: flex;
                align-items: center;
                justify-content: center;
                text-align: center;
                color: white;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                background-size: cover;
                background-position: center;
            }
            #{$sliderId} .slider-overlay {
                position: absolute;
                inset: 0;
                background-color: {$overlayColor};
            }
            #{$sliderId} .slider-content {
                position: relative;
                z-index: 10;
                padding: 0 2rem;
                max-width: 48rem;
            }
            #{$sliderId} .slider-arrow {
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
                width: 2.5rem;
                height: 2.5rem;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                background-color: rgba(0,0,0,0.3);
                color: {$arrowsColor};
                border: none;
                cursor: pointer;
                font-size: 1.5rem;
                z-index: 20;
            }
            #{$sliderId} .slider-arrow:hover {
                background-color: rgba(0,0,0,0.5);
            }
            #{$sliderId} .slider-arrow-prev {
                left: 1rem;
            }
            #{$sliderId} .slider-arrow-next {
                right: 1rem;
            }
            #{$sliderId} .slider-dots {
                position: absolute;
                bottom: 1rem;
                left: 50%;
                transform: translateX(-50%);
                display: flex;
                gap: 0.5rem;
                z-index: 20;
            }
            #{$sliderId} .slider-dot {
                width: 0.5rem;
                height: 0.5rem;
                border-radius: 50%;
                background-color: {$dotsColor};
                opacity: 0.5;
                cursor: pointer;
                border: none;
            }
            #{$sliderId} .slider-dot.active {
                opacity: 1;
            }
        </style>";

        $html = $css;
        $html .= "<div id=\"{$sliderId}\" class=\"slider-widget\" data-widget-type=\"slider\" data-autoplay=\"" . ($autoplay ? 'true' : 'false') . "\" data-autoplay-speed=\"{$autoplaySpeed}\">";

        // Slider track with all slides
        $html .= '<div class="slider-track">';
        foreach ($slides as $index => $slide) {
            $bgImage = !empty($slide['image']) ? "background-image: url('{$slide['image']}');" : '';
            $html .= "<div class=\"slider-slide\" style=\"{$bgImage}\">";
            $html .= '<div class="slider-overlay"></div>';
            $html .= '<div class="slider-content">';
            $html .= "<h2 style=\"font-size: 2.25rem; font-weight: bold; margin: 0 0 1rem 0; color: {$titleColor};\">{$slide['title']}</h2>";
            $html .= "<p style=\"font-size: 1.125rem; margin: 0 0 1.5rem 0; color: {$descColor};\">{$slide['description']}</p>";
            if ($slide['button']) {
                $html .= "<a href=\"{$slide['link']}\" style=\"display: inline-block; background-color: {$buttonBg}; color: {$buttonColor}; padding: 0.75rem 1.5rem; border-radius: 0.375rem; font-weight: 500; text-decoration: none;\">{$slide['button']}</a>";
            }
            $html .= '</div>';
            $html .= '</div>';
        }
        $html .= '</div>';

        // Navigation arrows
        if ($showArrows) {
            $html .= '<button class="slider-arrow slider-arrow-prev" aria-label="Previous slide">‹</button>';
            $html .= '<button class="slider-arrow slider-arrow-next" aria-label="Next slide">›</button>';
        }

        // Dots navigation
        if ($showDots) {
            $html .= '<div class="slider-dots">';
            foreach ($slides as $index => $slide) {
                $activeClass = $index === 0 ? ' active' : '';
                $html .= "<button class=\"slider-dot{$activeClass}\" data-slide=\"{$index}\" aria-label=\"Go to slide " . ($index + 1) . "\"></button>";
            }
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    protected function renderUnknown(string $type): string
    {
        return "<!-- Unknown widget type: {$type} -->";
    }
}
