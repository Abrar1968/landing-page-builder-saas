<?php

namespace App\Services;

class WidgetRegistry
{
    protected array $widgets = [];
    protected bool $initialized = false;

    public function __construct()
    {
        $this->registerWidgets();
    }

    /**
     * Register all widgets
     */
    protected function registerWidgets(): void
    {
        if ($this->initialized) {
            return;
        }

        // Basic Widgets (14)
        $this->register('heading', [
            'title' => 'Heading',
            'icon' => 'H',
            'category' => 'basic',
            'description' => 'Add eye-catching headlines',
        ]);

        $this->register('text-editor', [
            'title' => 'Text Editor',
            'icon' => '¶',
            'category' => 'basic',
            'description' => 'Add rich text content',
        ]);

        $this->register('image', [
            'title' => 'Image',
            'icon' => '🖼',
            'category' => 'basic',
            'description' => 'Add images with captions',
        ]);

        $this->register('button', [
            'title' => 'Button',
            'icon' => '▢',
            'category' => 'basic',
            'description' => 'Add call-to-action buttons',
        ]);

        $this->register('video', [
            'title' => 'Video',
            'icon' => '▶',
            'category' => 'basic',
            'description' => 'Embed YouTube or Vimeo videos',
        ]);

        $this->register('divider', [
            'title' => 'Divider',
            'icon' => '—',
            'category' => 'basic',
            'description' => 'Add visual separators',
        ]);

        $this->register('spacer', [
            'title' => 'Spacer',
            'icon' => '↕',
            'category' => 'basic',
            'description' => 'Add vertical spacing',
        ]);

        $this->register('icon', [
            'title' => 'Icon',
            'icon' => '★',
            'category' => 'basic',
            'description' => 'Add icon elements',
        ]);

        $this->register('icon-box', [
            'title' => 'Icon Box',
            'icon' => '◈',
            'category' => 'basic',
            'description' => 'Icon with title and description',
        ]);

        $this->register('counter', [
            'title' => 'Counter',
            'icon' => '123',
            'category' => 'basic',
            'description' => 'Animated number counter',
        ]);

        $this->register('progress-bar', [
            'title' => 'Progress Bar',
            'icon' => '█▒',
            'category' => 'basic',
            'description' => 'Show progress with bars',
        ]);

        $this->register('testimonial', [
            'title' => 'Testimonial',
            'icon' => '💬',
            'category' => 'basic',
            'description' => 'Customer testimonials',
        ]);

        $this->register('social-icons', [
            'title' => 'Social Icons',
            'icon' => '📱',
            'category' => 'basic',
            'description' => 'Social media links',
        ]);

        $this->register('alert', [
            'title' => 'Alert',
            'icon' => '⚠',
            'category' => 'basic',
            'description' => 'Alert messages',
        ]);

        // New Basic Widgets (6)
        $this->register('toggle', [
            'title' => 'Toggle',
            'icon' => '⊞',
            'category' => 'basic',
            'description' => 'Collapsible toggle items',
        ]);

        $this->register('icon-list', [
            'title' => 'Icon List',
            'icon' => '☰',
            'category' => 'basic',
            'description' => 'List with icons',
        ]);

        $this->register('text-path', [
            'title' => 'Text Path',
            'icon' => '⌇',
            'category' => 'basic',
            'description' => 'Text on curved paths',
        ]);

        $this->register('image-carousel', [
            'title' => 'Image Carousel',
            'icon' => '🎠',
            'category' => 'basic',
            'description' => 'Sliding image carousel',
        ]);

        $this->register('basic-gallery', [
            'title' => 'Basic Gallery',
            'icon' => '🖼️',
            'category' => 'basic',
            'description' => 'Image gallery grid',
        ]);

        $this->register('soundcloud', [
            'title' => 'SoundCloud',
            'icon' => '🎵',
            'category' => 'basic',
            'description' => 'Embed SoundCloud tracks',
        ]);

        // General Widgets (8)
        $this->register('image-box', [
            'title' => 'Image Box',
            'icon' => '🖼️',
            'category' => 'general',
            'description' => 'Image with title and description',
        ]);

        $this->register('star-rating', [
            'title' => 'Star Rating',
            'icon' => '⭐',
            'category' => 'general',
            'description' => 'Display star ratings',
        ]);

        $this->register('tabs', [
            'title' => 'Tabs',
            'icon' => '📑',
            'category' => 'general',
            'description' => 'Tabbed content sections',
        ]);

        $this->register('accordion', [
            'title' => 'Accordion',
            'icon' => '📋',
            'category' => 'general',
            'description' => 'Collapsible accordion',
        ]);

        $this->register('countdown', [
            'title' => 'Countdown',
            'icon' => '⏱️',
            'category' => 'general',
            'description' => 'Countdown timer',
        ]);

        $this->register('google-maps', [
            'title' => 'Google Maps',
            'icon' => '🗺️',
            'category' => 'general',
            'description' => 'Embed Google Maps',
        ]);

        // Marketing Widgets (3)
        $this->register('call-to-action', [
            'title' => 'Call to Action',
            'icon' => '📢',
            'category' => 'marketing',
            'description' => 'CTA boxes with buttons',
        ]);

        $this->register('flip-box', [
            'title' => 'Flip Box',
            'icon' => '🔄',
            'category' => 'marketing',
            'description' => 'Flip boxes with front/back',
        ]);

        $this->register('price-table', [
            'title' => 'Price Table',
            'icon' => '💰',
            'category' => 'marketing',
            'description' => 'Pricing tables',
        ]);

        // Pro Widgets (2)
        $this->register('form', [
            'title' => 'Form',
            'icon' => '📝',
            'category' => 'pro',
            'description' => 'Contact forms',
        ]);

        $this->register('slider', [
            'title' => 'Slider',
            'icon' => '🎠',
            'category' => 'pro',
            'description' => 'Image/content slider',
        ]);

        // Layout Widgets (4)
        $this->register('container', [
            'title' => 'Container',
            'icon' => '▭',
            'category' => 'layout',
            'description' => 'Flexbox container',
        ]);

        $this->register('inner-section', [
            'title' => 'Inner Section',
            'icon' => '▦',
            'category' => 'layout',
            'description' => 'Nested column layout',
        ]);

        $this->register('menu-anchor', [
            'title' => 'Menu Anchor',
            'icon' => '⚓',
            'category' => 'layout',
            'description' => 'Navigation anchor point',
        ]);

        $this->register('sidebar', [
            'title' => 'Sidebar',
            'icon' => '▐',
            'category' => 'layout',
            'description' => 'Sidebar widget area',
        ]);

        // Advanced Widgets (2)
        $this->register('html', [
            'title' => 'HTML',
            'icon' => '</>',
            'category' => 'advanced',
            'description' => 'Custom HTML code',
        ]);

        $this->register('shortcode', [
            'title' => 'Shortcode',
            'icon' => '[ ]',
            'category' => 'advanced',
            'description' => 'Shortcode processor',
        ]);

        $this->initialized = true;
    }

    /**
     * Register a widget
     */
    public function register(string $name, array $config): void
    {
        $this->widgets[$name] = $config;
    }

    /**
     * Get a specific widget by name
     */
    public function get(string $name): ?array
    {
        return $this->widgets[$name] ?? null;
    }

    /**
     * Get all widgets
     */
    public function all(): array
    {
        return $this->widgets;
    }

    /**
     * Get widgets by category
     */
    public function getByCategory(string $category): array
    {
        return array_filter($this->widgets, function ($widget) use ($category) {
            return ($widget['category'] ?? '') === $category;
        });
    }

    /**
     * Get all categories
     */
    public function getCategories(): array
    {
        $categories = [];
        foreach ($this->widgets as $widget) {
            if (isset($widget['category']) && !in_array($widget['category'], $categories)) {
                $categories[] = $widget['category'];
            }
        }
        return $categories;
    }

    /**
     * Check if widget exists
     */
    public function has(string $name): bool
    {
        return isset($this->widgets[$name]);
    }

    /**
     * Get widget count
     */
    public function count(): int
    {
        return count($this->widgets);
    }
}
