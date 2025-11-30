<?php

namespace App\Services;

class WidgetRenderer
{
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
        $title = e($settings['title'] ?? 'Heading');
        $tag = $settings['size'] ?? 'h2';
        $color = $settings['text_color'] ?? '#1f2937';
        $alignment = $settings['alignment'] ?? 'left';

        return "<{$tag} style=\"color: {$color}; text-align: {$alignment};\">{$title}</{$tag}>";
    }

    protected function renderTextEditor(array $settings): string
    {
        $content = $settings['editor'] ?? '<p>Lorem ipsum dolor sit amet</p>';
        $color = $settings['text_color'] ?? '#4b5563';
        $alignment = $settings['alignment'] ?? 'left';

        return "<div style=\"color: {$color}; text-align: {$alignment};\">{$content}</div>";
    }

    protected function renderImage(array $settings): string
    {
        if (empty($settings['image_url'])) {
            return '';
        }

        $url = e($settings['image_url']);
        $alt = e($settings['alt_text'] ?? '');
        $width = $settings['width'] ?? 100;
        $alignment = $settings['alignment'] ?? 'left';

        $html = "<div style=\"text-align: {$alignment};\">";
        $html .= "<img src=\"{$url}\" alt=\"{$alt}\" style=\"width: {$width}%;\" />";
        
        if (!empty($settings['caption'])) {
            $caption = e($settings['caption']);
            $html .= "<p style=\"margin-top: 0.5rem; font-size: 0.875rem; color: #6b7280;\">{$caption}</p>";
        }
        
        $html .= "</div>";

        return $html;
    }

    protected function renderButton(array $settings): string
    {
        $text = e($settings['text'] ?? 'Button');
        $link = e($settings['link'] ?? '#');
        $bgColor = $settings['background_color'] ?? '#4f46e5';
        $textColor = $settings['text_color'] ?? '#ffffff';
        $borderRadius = $settings['border_radius'] ?? '6';
        $alignment = $settings['alignment'] ?? 'left';
        $target = $settings['target'] ? '_blank' : '_self';

        $styles = "background-color: {$bgColor}; color: {$textColor}; padding: 12px 24px; border-radius: {$borderRadius}px; text-decoration: none; display: inline-block;";

        return "<div style=\"text-align: {$alignment};\"><a href=\"{$link}\" target=\"{$target}\" style=\"{$styles}\">{$text}</a></div>";
    }

    protected function renderVideo(array $settings): string
    {
        $url = $settings['youtube_url'] ?? '';
        if (empty($url)) {
            return '';
        }

        // Extract video ID
        preg_match('/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([^&?]+)/', $url, $match);
        $videoId = $match[1] ?? '';

        if (empty($videoId)) {
            return '';
        }

        $embedUrl = "https://www.youtube.com/embed/{$videoId}";
        $aspectRatio = $settings['aspect_ratio'] ?? '16:9';
        [$w, $h] = explode(':', $aspectRatio);
        $paddingBottom = ($h / $w) * 100;

        return "<div style=\"position: relative; padding-bottom: {$paddingBottom}%; height: 0;\"><iframe src=\"{$embedUrl}\" frameborder=\"0\" allowfullscreen style=\"position: absolute; top: 0; left: 0; width: 100%; height: 100%;\"></iframe></div>";
    }

    protected function renderDivider(array $settings): string
    {
        $style = $settings['style'] ?? 'solid';
        $color = $settings['color'] ?? '#e5e7eb';
        $weight = $settings['weight'] ?? 1;
        $width = $settings['width'] ?? 100;
        $alignment = $settings['alignment'] ?? 'center';

        return "<div style=\"text-align: {$alignment};\"><hr style=\"border-style: {$style}; border-color: {$color}; border-width: {$weight}px; width: {$width}%; display: inline-block;\" /></div>";
    }

    protected function renderSpacer(array $settings): string
    {
        $space = $settings['space'] ?? 50;
        return "<div style=\"height: {$space}px;\"></div>";
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
        $titleColor = $settings['title_color'] ?? '#1f2937';
        $alignment = $settings['alignment'] ?? 'center';

        return "<div style=\"text-align: {$alignment};\">
            <div style=\"font-size: {$iconSize}px; color: {$iconColor};\">{$icon}</div>
            <h4 style=\"color: {$titleColor}; font-weight: 600; margin-top: 1rem;\">{$title}</h4>
            <p style=\"color: #6b7280; margin-top: 0.5rem;\">{$description}</p>
        </div>";
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

        return "<div style=\"text-align: {$alignment};\">
            <div style=\"font-size: {$numberSize}px; color: {$numberColor}; font-weight: bold;\">{$prefix}{$number}{$suffix}</div>
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

        $html = '';
        if ($title || $displayPercent) {
            $html .= '<div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">';
            if ($title) {
                $html .= "<span>{$title}</span>";
            }
            if ($displayPercent) {
                $html .= "<span>{$percent}%</span>";
            }
            $html .= '</div>';
        }

        $html .= "<div style=\"background-color: {$bgColor}; height: {$height}px; border-radius: 9999px;\">
            <div style=\"background-color: {$barColor}; width: {$percent}%; height: 100%; border-radius: 9999px; transition: width 0.3s;\"></div>
        </div>";

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
        $iconSize = $settings['icon_size'] ?? 24;
        $alignment = $settings['alignment'] ?? 'center';

        $html = "<div style=\"text-align: {$alignment};\">";
        
        $platforms = ['facebook' => 'f', 'twitter' => '𝕏', 'instagram' => '📷', 'linkedin' => 'in'];
        foreach ($platforms as $platform => $icon) {
            if (!empty($settings[$platform])) {
                $url = e($settings[$platform]);
                $html .= "<a href=\"{$url}\" style=\"color: {$iconColor}; font-size: {$iconSize}px; margin: 0 0.5rem; text-decoration: none;\">{$icon}</a>";
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

        $html = '<div style="border: 1px solid #e5e7eb; border-radius: 0.5rem; overflow: hidden;">';
        
        for ($i = 1; $i <= 3; $i++) {
            $title = e($settings["item{$i}_title"] ?? "Toggle Item {$i}");
            $content = $settings["item{$i}_content"] ?? "<p>Content for toggle item {$i}.</p>";
            
            $html .= "<div style=\"border-bottom: 1px solid #e5e7eb;\">";
            $html .= "<div style=\"background-color: {$titleBg}; color: {$titleColor}; padding: 0.75rem 1rem; font-weight: 500; cursor: pointer;\">{$title}</div>";
            $html .= "</div>";
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

        $html = '<div style="position: relative;"><div style="display: flex; gap: ' . $spacing . 'px; overflow: hidden;">';
        
        for ($i = 1; $i <= $slidesToShow; $i++) {
            $width = (100 / $slidesToShow) - (($spacing * ($slidesToShow - 1)) / $slidesToShow);
            
            if (!empty($settings["image{$i}"])) {
                $imageUrl = e($settings["image{$i}"]);
                $html .= "<div style=\"flex-shrink: 0; width: {$width}%;\"><img src=\"{$imageUrl}\" style=\"width: 100%; height: 12rem; object-fit: cover; border-radius: {$borderRadius}px;\" /></div>";
            }
        }
        
        $html .= '</div></div>';

        return $html;
    }

    protected function renderBasicGallery(array $settings): string
    {
        $columns = $settings['columns'] ?? 3;
        $gap = $settings['gap'] ?? 10;
        $borderRadius = $settings['border_radius'] ?? 8;

        $html = "<div style=\"display: grid; grid-template-columns: repeat({$columns}, 1fr); gap: {$gap}px;\">";
        
        for ($i = 1; $i <= 6; $i++) {
            if (!empty($settings["image{$i}"])) {
                $imageUrl = e($settings["image{$i}"]);
                $html .= "<div><img src=\"{$imageUrl}\" style=\"width: 100%; height: 10rem; object-fit: cover; border-radius: {$borderRadius}px;\" /></div>";
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
        $minHeight = $settings['min_height'] ?? 0;

        $containerClass = $contentWidth === 'boxed' ? 'max-width: 1280px; margin: 0 auto; padding: 0 1rem;' : 'width: 100%;';

        return "<{$htmlTag} style=\"min-height: {$minHeight}px;\">
            <div style=\"{$containerClass}\">
                <!-- Container content goes here -->
            </div>
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
        return $settings['html_code'] ?? '<div class="custom-html"><p>Add your custom HTML here</p></div>';
    }

    protected function renderShortcode(array $settings): string
    {
        $shortcode = e($settings['shortcode'] ?? '[shortcode]');
        // In a real implementation, you would process the shortcode here
        return "<!-- Shortcode: {$shortcode} -->";
    }

    // Existing widget renderers (stubs for widgets already implemented)
    protected function renderImageBox(array $settings): string { return '<!-- Image Box -->'; }
    protected function renderStarRating(array $settings): string { return '<!-- Star Rating -->'; }
    protected function renderTabs(array $settings): string { return '<!-- Tabs -->'; }
    protected function renderAccordion(array $settings): string { return '<!-- Accordion -->'; }
    protected function renderCountdown(array $settings): string { return '<!-- Countdown -->'; }
    protected function renderGoogleMaps(array $settings): string { return '<!-- Google Maps -->'; }
    protected function renderCallToAction(array $settings): string { return '<!-- Call to Action -->'; }
    protected function renderFlipBox(array $settings): string { return '<!-- Flip Box -->'; }
    protected function renderPriceTable(array $settings): string { return '<!-- Price Table -->'; }
    protected function renderForm(array $settings): string { return '<!-- Form -->'; }
    protected function renderSlider(array $settings): string { return '<!-- Slider -->'; }

    protected function renderUnknown(string $type): string
    {
        return "<!-- Unknown widget type: {$type} -->";
    }
}
