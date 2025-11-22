@props([
    'text' => 'Click Me',
    'url' => '#',
    'target' => '_self',
    'bgColor' => '#3B82F6',
    'textColor' => '#FFFFFF',
    'padding' => '12px 24px',
    'borderRadius' => '6px',
    'fontSize' => '1rem'
])

<div style="padding: 16px; text-align: center;">
    <a href="{{ $url }}"
       target="{{ $target }}"
       style="display: inline-block; background: {{ $bgColor }}; color: {{ $textColor }}; padding: {{ $padding }}; border-radius: {{ $borderRadius }}; font-size: {{ $fontSize }}; text-decoration: none;">
        {{ $text }}
    </a>
</div>
