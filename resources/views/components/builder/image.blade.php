@props([
    'src' => '',
    'alt' => '',
    'width' => '100%',
    'height' => 'auto',
    'objectFit' => 'cover',
    'borderRadius' => '0'
])

@if($src)
    <img src="{{ $src }}"
         alt="{{ $alt }}"
         style="width: {{ $width }}; height: {{ $height }}; object-fit: {{ $objectFit }}; border-radius: {{ $borderRadius }}; display: block;">
@else
    <div style="padding: 40px; background: #f3f4f6; text-align: center; color: #9ca3af;">
        Click to add image
    </div>
@endif
