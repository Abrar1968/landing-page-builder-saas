@props([
    'text' => 'New Heading',
    'level' => 'h2',
    'alignment' => 'left',
    'color' => '#000000',
    'fontSize' => '2rem',
    'fontWeight' => 'bold'
])

@php
    $tag = in_array($level, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']) ? $level : 'h2';
@endphp

<{{ $tag }} style="text-align: {{ $alignment }}; color: {{ $color }}; font-size: {{ $fontSize }}; font-weight: {{ $fontWeight }}; margin: 0; padding: 16px;">
    {{ $text }}
</{{ $tag }}>
