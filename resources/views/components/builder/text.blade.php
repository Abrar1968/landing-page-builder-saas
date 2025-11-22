@props([
    'text' => 'Enter your text here...',
    'alignment' => 'left',
    'color' => '#333333',
    'fontSize' => '1rem',
    'lineHeight' => '1.6'
])

<p style="text-align: {{ $alignment }}; color: {{ $color }}; font-size: {{ $fontSize }}; line-height: {{ $lineHeight }}; margin: 0; padding: 16px;">
    {{ $text }}
</p>
