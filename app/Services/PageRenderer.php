<?php

namespace App\Services;

use Illuminate\Support\Facades\View;

class PageRenderer
{
    /**
     * Render all elements to HTML
     */
    public function render(array $elements): string
    {
        $html = '';

        foreach ($elements as $element) {
            $html .= $this->renderElement($element);
        }

        return $html;
    }

    /**
     * Render a single element
     */
    protected function renderElement(array $element): string
    {
        $type = $element['type'] ?? '';
        $props = $element['props'] ?? $element['content'] ?? [];

        return match ($type) {
            'heading' => $this->renderHeading($props),
            'paragraph', 'text' => $this->renderText($props),
            'image' => $this->renderImage($props),
            'button' => $this->renderButton($props),
            'divider' => $this->renderDivider($props),
            'spacer' => $this->renderSpacer($props),
            'video' => $this->renderVideo($props),
            'hero' => $this->renderHero($props),
            'features' => $this->renderFeatures($props),
            'testimonial' => $this->renderTestimonial($props),
            'pricing' => $this->renderPricing($props),
            'cta' => $this->renderCta($props),
            'footer' => $this->renderFooter($props),
            'form' => $this->renderForm($props),
            'newsletter' => $this->renderNewsletter($props),
            'columns' => $this->renderColumns($props),
            'html' => $this->renderHtml($props),
            default => '',
        };
    }

    protected function renderHeading(array $props): string
    {
        return View::make('components.builder.heading', $props)->render();
    }

    protected function renderText(array $props): string
    {
        return View::make('components.builder.text', $props)->render();
    }

    protected function renderImage(array $props): string
    {
        return View::make('components.builder.image', $props)->render();
    }

    protected function renderButton(array $props): string
    {
        return View::make('components.builder.button', $props)->render();
    }

    protected function renderDivider(array $props): string
    {
        return View::make('components.builder.divider', $props)->render();
    }

    protected function renderSpacer(array $props): string
    {
        return View::make('components.builder.spacer', $props)->render();
    }

    protected function renderVideo(array $props): string
    {
        return View::make('components.builder.video', $props)->render();
    }

    protected function renderHero(array $props): string
    {
        return View::make('components.builder.hero', $props)->render();
    }

    protected function renderFeatures(array $props): string
    {
        return View::make('components.builder.features', $props)->render();
    }

    protected function renderTestimonial(array $props): string
    {
        return View::make('components.builder.testimonial', $props)->render();
    }

    protected function renderPricing(array $props): string
    {
        return View::make('components.builder.pricing', $props)->render();
    }

    protected function renderCta(array $props): string
    {
        return View::make('components.builder.cta', $props)->render();
    }

    protected function renderFooter(array $props): string
    {
        return View::make('components.builder.footer', $props)->render();
    }

    protected function renderForm(array $props): string
    {
        return View::make('components.builder.form', $props)->render();
    }

    protected function renderNewsletter(array $props): string
    {
        return View::make('components.builder.newsletter', $props)->render();
    }

    protected function renderColumns(array $props): string
    {
        return View::make('components.builder.columns', $props)->render();
    }

    protected function renderHtml(array $props): string
    {
        return View::make('components.builder.html', $props)->render();
    }
}
