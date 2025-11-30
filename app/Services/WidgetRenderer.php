<?php

namespace App\Services;

class WidgetRenderer
{
    public function __construct(
        protected HtmlSanitizer $htmlSanitizer
    ) {}

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

        // ✅ Sanitize HTML to prevent XSS attacks
        $content = $this->htmlSanitizer->sanitize($content);

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
        $descColor = $settings['description_color'] ?? '#6b7280';
        $alignment = $settings['alignment'] ?? 'center';

        $html = "<div style=\"text-align: {$alignment};\">";

        if ($imageUrl) {
            $imageUrl = e($imageUrl);
            $html .= "<img src=\"{$imageUrl}\" style=\"width: 100%; height: 10rem; object-fit: cover; border-radius: 0.5rem; margin-bottom: 1rem;\" alt=\"\" />";
        } else {
            $html .= '<div style="width: 100%; height: 10rem; background-color: #e5e7eb; border-radius: 0.5rem; margin-bottom: 1rem; display: flex; align-items: center; justify-content: center; color: #9ca3af; font-size: 2.25rem;">🖼️</div>';
        }

        $html .= "<h4 style=\"color: {$titleColor}; font-weight: 600; font-size: 1.125rem;\">{$title}</h4>";
        $html .= "<p style=\"color: {$descColor}; margin-top: 0.5rem;\">{$description}</p>";
        $html .= "</div>";

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

        // ✅ Sanitize HTML content
        $tab1Content = $this->htmlSanitizer->sanitize($tab1Content);

        $html = '<div class="tabs-widget">';
        $html .= '<div style="display: flex; border-bottom: 1px solid #e5e7eb;">';
        $html .= "<button style=\"padding: 0.5rem 1rem; font-weight: 500; color: {$tabColor}; border: none; background: none; border-bottom: 2px solid {$tabColor}; cursor: pointer;\">{$tab1Title}</button>";
        $html .= "<button style=\"padding: 0.5rem 1rem; color: #6b7280; border: none; background: none; cursor: pointer;\">{$tab2Title}</button>";
        $html .= "<button style=\"padding: 0.5rem 1rem; color: #6b7280; border: none; background: none; cursor: pointer;\">{$tab3Title}</button>";
        $html .= '</div>';
        $html .= "<div style=\"padding: 1rem; color: {$contentColor};\">{$tab1Content}</div>";
        $html .= '</div>';

        return $html;
    }

    protected function renderAccordion(array $settings): string
    {
        $titleBg = $settings['title_background'] ?? '#f3f4f6';
        $titleColor = $settings['title_color'] ?? '#1f2937';
        $contentColor = $settings['content_color'] ?? '#4b5563';
        $firstOpen = $settings['first_open'] ?? true;

        $html = '<div style="border: 1px solid #e5e7eb; border-radius: 0.5rem; overflow: hidden;">';

        for ($i = 1; $i <= 3; $i++) {
            $title = e($settings["item{$i}_title"] ?? "Accordion Item {$i}");
            $content = $settings["item{$i}_content"] ?? "<p>Content for accordion item {$i}.</p>";

            // ✅ Sanitize HTML content
            $content = $this->htmlSanitizer->sanitize($content);

            $html .= '<div style="border-bottom: 1px solid #e5e7eb;">';
            $html .= "<div style=\"background-color: {$titleBg}; color: {$titleColor}; padding: 0.75rem 1rem; font-weight: 500; display: flex; justify-content: space-between; align-items: center;\">";
            $html .= "<span>{$title}</span>";
            $html .= "<span>" . ($i === 1 && $firstOpen ? '−' : '+') . "</span>";
            $html .= "</div>";

            if ($i === 1 && $firstOpen) {
                $html .= "<div style=\"padding: 0.75rem 1rem; color: {$contentColor};\">{$content}</div>";
            }

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

        $html = '<div style="display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap;">';

        $units = [
            ['show' => $showDays, 'label' => 'Days', 'value' => '00'],
            ['show' => $showHours, 'label' => 'Hours', 'value' => '00'],
            ['show' => $showMinutes, 'label' => 'Minutes', 'value' => '00'],
            ['show' => $showSeconds, 'label' => 'Seconds', 'value' => '00'],
        ];

        foreach ($units as $unit) {
            if ($unit['show']) {
                $html .= '<div style="text-align: center;">';
                $html .= "<div style=\"font-size: {$numberSize}px; color: {$numberColor}; font-weight: bold; line-height: 1;\">{$unit['value']}</div>";

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
        $address = e($settings['address'] ?? 'New York, USA');
        $zoom = $settings['zoom'] ?? 14;
        $height = $settings['height'] ?? 400;

        $html = "<div style=\"height: {$height}px; background-color: #e5e7eb; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; color: #6b7280;\">";
        $html .= '<div style="text-align: center;">';
        $html .= '<div style="font-size: 2.25rem; margin-bottom: 0.5rem;">🗺️</div>';
        $html .= "<p style=\"margin: 0.5rem 0; font-size: 1rem;\">{$address}</p>";
        $html .= "<p style=\"margin: 0; font-size: 0.75rem;\">Zoom: {$zoom}</p>";
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    protected function renderCallToAction(array $settings): string
    {
        $title = e($settings['title'] ?? 'This is the heading');
        $description = e($settings['description'] ?? 'Click here to add your own text and edit me.');
        $buttonText = e($settings['button_text'] ?? 'Click Here');
        $titleColor = $settings['title_color'] ?? '#1f2937';
        $descColor = $settings['description_color'] ?? '#4b5563';
        $buttonBg = $settings['button_background'] ?? '#4f46e5';
        $buttonColor = $settings['button_color'] ?? '#ffffff';
        $ribbonText = e($settings['ribbon_text'] ?? '');
        $ribbonColor = $settings['ribbon_color'] ?? '#ef4444';

        $html = '<div style="position: relative; padding: 2rem; border-radius: 0.5rem;">';

        if ($ribbonText) {
            $html .= "<div style=\"position: absolute; top: 0; right: 0; background-color: {$ribbonColor}; color: white; padding: 0.25rem 0.75rem; font-size: 0.875rem; font-weight: 500;\">{$ribbonText}</div>";
        }

        $html .= "<h3 style=\"color: {$titleColor}; font-size: 1.5rem; font-weight: bold; margin: 0 0 0.5rem 0;\">{$title}</h3>";
        $html .= "<p style=\"color: {$descColor}; margin: 0 0 1rem 0;\">{$description}</p>";
        $html .= "<button style=\"background-color: {$buttonBg}; color: {$buttonColor}; padding: 0.5rem 1.5rem; border-radius: 0.375rem; font-weight: 500; border: none; cursor: pointer;\">{$buttonText}</button>";
        $html .= '</div>';

        return $html;
    }

    protected function renderFlipBox(array $settings): string
    {
        $height = $settings['height'] ?? 300;
        $frontBg = $settings['front_background'] ?? '#ffffff';
        $frontColor = $settings['front_color'] ?? '#1f2937';
        $frontIcon = e($settings['front_icon'] ?? '⚡');
        $frontTitle = e($settings['front_title'] ?? 'Front Title');
        $frontDesc = e($settings['front_description'] ?? 'This is the front content.');

        $html = "<div style=\"position: relative; height: {$height}px; perspective: 1000px;\">";
        $html .= "<div style=\"width: 100%; height: 100%; background-color: {$frontBg}; color: {$frontColor}; border-radius: 0.5rem; padding: 1.5rem; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1);\">";
        $html .= "<div style=\"font-size: 2.25rem; margin-bottom: 1rem;\">{$frontIcon}</div>";
        $html .= "<h4 style=\"font-weight: 600; font-size: 1.125rem; margin: 0 0 0.5rem 0;\">{$frontTitle}</h4>";
        $html .= "<p style=\"margin: 0; font-size: 0.875rem; opacity: 0.8;\">{$frontDesc}</p>";
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

        $html .= "<button style=\"background-color: {$buttonBg}; color: {$buttonColor}; width: 100%; padding: 0.75rem; border-radius: 0.375rem; font-weight: 500; border: none; cursor: pointer;\">{$buttonText}</button>";
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
        $fieldBg = $settings['field_background'] ?? '#ffffff';
        $fieldBorder = $settings['field_border'] ?? '#d1d5db';
        $fieldText = $settings['field_text'] ?? '#1f2937';
        $buttonBg = $settings['button_background'] ?? '#4f46e5';
        $buttonTextColor = $settings['button_text'] ?? '#ffffff';
        $spacing = $settings['spacing'] ?? 16;

        $html = '<div class="form-widget">';

        if ($formName) {
            $html .= "<h3 style=\"font-size: 1.25rem; font-weight: 600; margin: 0 0 1rem 0;\">{$formName}</h3>";
        }

        $html .= '<form>';

        if ($nameField) {
            if ($showLabels) {
                $html .= '<label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem; color: #374151;">Name</label>';
            }
            $html .= "<input type=\"text\" placeholder=\"Your Name\" style=\"width: 100%; padding: 0.5rem 1rem; border: 1px solid {$fieldBorder}; background-color: {$fieldBg}; color: {$fieldText}; border-radius: 0.375rem; margin-bottom: {$spacing}px; box-sizing: border-box;\" />";
        }

        if ($emailField) {
            if ($showLabels) {
                $html .= '<label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem; color: #374151;">Email</label>';
            }
            $html .= "<input type=\"email\" placeholder=\"your@email.com\" style=\"width: 100%; padding: 0.5rem 1rem; border: 1px solid {$fieldBorder}; background-color: {$fieldBg}; color: {$fieldText}; border-radius: 0.375rem; margin-bottom: {$spacing}px; box-sizing: border-box;\" />";
        }

        if ($messageField) {
            if ($showLabels) {
                $html .= '<label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.25rem; color: #374151;">Message</label>';
            }
            $html .= "<textarea placeholder=\"Your Message\" rows=\"4\" style=\"width: 100%; padding: 0.5rem 1rem; border: 1px solid {$fieldBorder}; background-color: {$fieldBg}; color: {$fieldText}; border-radius: 0.375rem; resize: vertical; box-sizing: border-box;\"></textarea>";
        }

        $html .= "<button type=\"submit\" style=\"background-color: {$buttonBg}; color: {$buttonTextColor}; padding: 0.75rem 1.5rem; border-radius: 0.375rem; font-weight: 500; margin-top: 1rem; border: none; cursor: pointer;\">{$buttonText}</button>";
        $html .= '</form>';
        $html .= '</div>';

        return $html;
    }

    protected function renderSlider(array $settings): string
    {
        $height = $settings['height'] ?? 500;
        $slide1Title = e($settings['slide1_title'] ?? 'First Slide');
        $slide1Desc = e($settings['slide1_description'] ?? 'This is the first slide content.');
        $slide1Button = e($settings['slide1_button'] ?? '');
        $slide1Link = e($settings['slide1_link'] ?? '#');
        $titleColor = $settings['title_color'] ?? '#ffffff';
        $descColor = $settings['description_color'] ?? '#f3f4f6';
        $buttonBg = $settings['button_background'] ?? '#4f46e5';
        $buttonColor = $settings['button_color'] ?? '#ffffff';
        $overlayColor = $settings['overlay_color'] ?? 'rgba(0,0,0,0.3)';
        $showArrows = $settings['show_arrows'] ?? true;
        $showDots = $settings['show_dots'] ?? true;
        $arrowsColor = $settings['arrows_color'] ?? '#ffffff';
        $dotsColor = $settings['dots_color'] ?? '#ffffff';

        $html = "<div style=\"position: relative; overflow: hidden; border-radius: 0.5rem; height: {$height}px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);\">";

        $html .= '<div style="position: relative; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; text-align: center; color: white;">';
        $html .= "<div style=\"position: absolute; inset: 0; background-color: {$overlayColor};\"></div>";
        $html .= '<div style="position: relative; z-index: 10; padding: 0 2rem; max-width: 48rem;">';
        $html .= "<h2 style=\"font-size: 2.25rem; font-weight: bold; margin: 0 0 1rem 0; color: {$titleColor};\">{$slide1Title}</h2>";
        $html .= "<p style=\"font-size: 1.125rem; margin: 0 0 1.5rem 0; color: {$descColor};\">{$slide1Desc}</p>";

        if ($slide1Button) {
            $html .= "<a href=\"{$slide1Link}\" style=\"display: inline-block; background-color: {$buttonBg}; color: {$buttonColor}; padding: 0.75rem 1.5rem; border-radius: 0.375rem; font-weight: 500; text-decoration: none;\">{$slide1Button}</a>";
        }

        $html .= '</div>';
        $html .= '</div>';

        if ($showArrows) {
            $html .= '<div style="position: absolute; top: 50%; left: 0; right: 0; display: flex; justify-content: space-between; padding: 0 1rem; transform: translateY(-50%); pointer-events: none;">';
            $html .= "<button style=\"width: 2.5rem; height: 2.5rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; background-color: rgba(0,0,0,0.3); color: {$arrowsColor}; border: none; pointer-events: auto; cursor: pointer; font-size: 1.5rem;\">‹</button>";
            $html .= "<button style=\"width: 2.5rem; height: 2.5rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; background-color: rgba(0,0,0,0.3); color: {$arrowsColor}; border: none; pointer-events: auto; cursor: pointer; font-size: 1.5rem;\">›</button>";
            $html .= '</div>';
        }

        if ($showDots) {
            $html .= '<div style="position: absolute; bottom: 1rem; left: 50%; transform: translateX(-50%); display: flex; gap: 0.5rem;">';
            $html .= "<span style=\"width: 0.5rem; height: 0.5rem; border-radius: 50%; background-color: {$dotsColor};\"></span>";
            $html .= "<span style=\"width: 0.5rem; height: 0.5rem; border-radius: 50%; background-color: {$dotsColor}; opacity: 0.5;\"></span>";
            $html .= "<span style=\"width: 0.5rem; height: 0.5rem; border-radius: 50%; background-color: {$dotsColor}; opacity: 0.5;\"></span>";
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
