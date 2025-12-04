<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidWidgetStructure implements Rule
{
    protected string $message = '';
    protected int $maxDepth = 10;
    protected array $allowedWidgetTypes = [
        // Basic widgets (20)
        'heading', 'text-editor', 'image', 'button', 'video', 'divider',
        'spacer', 'icon', 'icon-box', 'counter', 'progress-bar',
        'testimonial', 'social-icons', 'alert', 'toggle', 'icon-list',
        'text-path', 'image-carousel', 'basic-gallery', 'soundcloud',
        // General widgets (6)
        'image-box', 'star-rating', 'tabs', 'accordion', 'countdown', 'google-maps',
        // Marketing widgets (3)
        'call-to-action', 'flip-box', 'price-table',
        // Pro widgets (2)
        'form', 'slider',
        // Layout widgets (4)
        'container', 'inner-section', 'menu-anchor', 'sidebar',
        // Advanced widgets (2)
        'html', 'shortcode',
    ];

    public function passes($attribute, $value): bool
    {
        if (!is_array($value)) {
            $this->message = 'Content must be an array.';
            return false;
        }

        // Check array depth to prevent DoS
        if ($this->getArrayDepth($value) > $this->maxDepth) {
            $this->message = 'Content structure is too deeply nested (max depth: ' . $this->maxDepth . ').';
            return false;
        }

        // Validate each section
        foreach ($value as $section) {
            if (!$this->validateSection($section)) {
                return false;
            }
        }

        return true;
    }

    protected function validateSection(array $section): bool
    {
        if (!isset($section['elType']) || $section['elType'] !== 'section') {
            $this->message = 'Invalid section structure. Expected elType: "section".';
            return false;
        }

        if (!isset($section['id'])) {
            $this->message = 'Section must have an ID.';
            return false;
        }

        if (!isset($section['elements']) || !is_array($section['elements'])) {
            $this->message = 'Section must have elements array.';
            return false;
        }

        foreach ($section['elements'] as $element) {
            // Support both new 3-level (container) and old 2-level (column) structure
            if (isset($element['elType'])) {
                if ($element['elType'] === 'container') {
                    if (!$this->validateContainer($element)) {
                        return false;
                    }
                } elseif ($element['elType'] === 'column') {
                    if (!$this->validateColumn($element)) {
                        return false;
                    }
                } else {
                    $this->message = 'Section elements must be containers or columns.';
                    return false;
                }
            }
        }

        return true;
    }

    protected function validateContainer(array $container): bool
    {
        if (!isset($container['elType']) || $container['elType'] !== 'container') {
            $this->message = 'Invalid container structure. Expected elType: "container".';
            return false;
        }

        if (!isset($container['id'])) {
            $this->message = 'Container must have an ID.';
            return false;
        }

        if (!isset($container['elements']) || !is_array($container['elements'])) {
            $this->message = 'Container must have elements array.';
            return false;
        }

        foreach ($container['elements'] as $column) {
            if (!$this->validateColumn($column)) {
                return false;
            }
        }

        return true;
    }

    protected function validateColumn(array $column): bool
    {
        if (!isset($column['elType']) || $column['elType'] !== 'column') {
            $this->message = 'Invalid column structure. Expected elType: "column".';
            return false;
        }

        if (!isset($column['id'])) {
            $this->message = 'Column must have an ID.';
            return false;
        }

        if (!isset($column['elements']) || !is_array($column['elements'])) {
            $this->message = 'Column must have elements array.';
            return false;
        }

        foreach ($column['elements'] as $widget) {
            if (!$this->validateWidget($widget)) {
                return false;
            }
        }

        return true;
    }

    protected function validateWidget(array $widget): bool
    {
        if (!isset($widget['elType']) || $widget['elType'] !== 'widget') {
            $this->message = 'Invalid widget structure. Expected elType: "widget".';
            return false;
        }

        if (!isset($widget['id'])) {
            $this->message = 'Widget must have an ID.';
            return false;
        }

        if (!isset($widget['widgetType'])) {
            $this->message = 'Widget must have widgetType.';
            return false;
        }

        if (!in_array($widget['widgetType'], $this->allowedWidgetTypes)) {
            $this->message = "Invalid widget type: '{$widget['widgetType']}'. Allowed types: " . implode(', ', $this->allowedWidgetTypes);
            return false;
        }

        if (!isset($widget['settings']) || !is_array($widget['settings'])) {
            $this->message = 'Widget must have settings array.';
            return false;
        }

        return true;
    }

    protected function getArrayDepth(array $array, int $depth = 0): int
    {
        if ($depth > $this->maxDepth) {
            return $depth;
        }

        $maxDepth = $depth;

        foreach ($array as $value) {
            if (is_array($value)) {
                $currentDepth = $this->getArrayDepth($value, $depth + 1);
                $maxDepth = max($maxDepth, $currentDepth);
            }
        }

        return $maxDepth;
    }

    public function message(): string
    {
        return $this->message ?: 'The :attribute has invalid widget structure.';
    }
}
