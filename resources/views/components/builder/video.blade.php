@props([
    'url' => '',
    'provider' => 'youtube',
    'aspectRatio' => '16/9'
])

@php
    $embedUrl = '';
    if ($url) {
        if ($provider === 'youtube') {
            preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&]+)/', $url, $matches);
            $embedUrl = isset($matches[1]) ? "https://www.youtube.com/embed/{$matches[1]}" : '';
        } elseif ($provider === 'vimeo') {
            preg_match('/vimeo\.com\/(\d+)/', $url, $matches);
            $embedUrl = isset($matches[1]) ? "https://player.vimeo.com/video/{$matches[1]}" : '';
        }
    }
@endphp

@if($embedUrl)
    <div style="aspect-ratio: {{ $aspectRatio }}; width: 100%;">
        <iframe src="{{ $embedUrl }}"
                style="width: 100%; height: 100%; border: none;"
                allowfullscreen></iframe>
    </div>
@else
    <div style="aspect-ratio: {{ $aspectRatio }}; width: 100%; background: #f3f4f6; display: flex; align-items: center; justify-content: center; color: #9ca3af;">
        Add video URL
    </div>
@endif
