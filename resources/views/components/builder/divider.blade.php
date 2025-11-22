@props([
    'style' => 'solid',
    'color' => '#E5E7EB',
    'thickness' => '1px',
    'width' => '100%',
    'margin' => '24px 0'
])

<hr style="border: none; border-top: {{ $thickness }} {{ $style }} {{ $color }}; width: {{ $width }}; margin: {{ $margin }};">
