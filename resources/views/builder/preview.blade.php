<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->title }} - Preview</title>
    @vite(['resources/css/app.css'])
    <style>
        body { margin: 0; padding: 0; }
    </style>
</head>
<body>
    <div class="max-w-4xl mx-auto bg-white min-h-screen">
        @foreach($page->content ?? [] as $element)
            @switch($element['type'] ?? '')
                @case('heading')
                    @php
                        $tag = $element['props']['level'] ?? 'h2';
                        $style = "color: " . ($element['props']['color'] ?? '#000') . "; font-size: " . ($element['props']['fontSize'] ?? '32px') . "; font-weight: " . ($element['props']['fontWeight'] ?? '700') . "; margin: 0; padding: 16px;";
                    @endphp
                    <{{ $tag }} style="{{ $style }}">{{ $element['props']['text'] ?? '' }}</{{ $tag }}>
                    @break

                @case('paragraph')
                    <p style="color: {{ $element['props']['color'] ?? '#333' }}; font-size: {{ $element['props']['fontSize'] ?? '16px' }}; line-height: {{ $element['props']['lineHeight'] ?? '1.6' }}; margin: 0; padding: 16px;">
                        {{ $element['props']['text'] ?? '' }}
                    </p>
                    @break

                @case('image')
                    @if(!empty($element['props']['src']))
                        <img src="{{ $element['props']['src'] }}" alt="{{ $element['props']['alt'] ?? '' }}" style="width: {{ $element['props']['width'] ?? '100%' }}; display: block;">
                    @endif
                    @break

                @case('button')
                    <div style="padding: 16px; text-align: center;">
                        <a href="{{ $element['props']['url'] ?? '#' }}" style="display: inline-block; background: {{ $element['props']['backgroundColor'] ?? '#3b82f6' }}; color: {{ $element['props']['textColor'] ?? '#fff' }}; padding: 12px 24px; border-radius: {{ $element['props']['borderRadius'] ?? '6px' }}; text-decoration: none;">
                            {{ $element['props']['text'] ?? 'Button' }}
                        </a>
                    </div>
                    @break

                @case('divider')
                    <hr style="border: none; border-top: {{ $element['props']['thickness'] ?? '1px' }} solid {{ $element['props']['color'] ?? '#e5e7eb' }}; margin: 24px 0;">
                    @break

                @case('spacer')
                    <div style="height: {{ $element['props']['height'] ?? '40px' }};"></div>
                    @break

                @case('video')
                    @if(!empty($element['props']['src']))
                        @php
                            $provider = $element['props']['provider'] ?? 'youtube';
                            $url = $element['props']['src'];
                            if ($provider === 'youtube') {
                                preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&]+)/', $url, $matches);
                                $embedUrl = isset($matches[1]) ? "https://www.youtube.com/embed/{$matches[1]}" : '';
                            } else {
                                preg_match('/vimeo\.com\/(\d+)/', $url, $matches);
                                $embedUrl = isset($matches[1]) ? "https://player.vimeo.com/video/{$matches[1]}" : '';
                            }
                        @endphp
                        @if($embedUrl)
                            <div style="aspect-ratio: 16/9; width: 100%;">
                                <iframe src="{{ $embedUrl }}" style="width: 100%; height: 100%; border: none;" allowfullscreen></iframe>
                            </div>
                        @endif
                    @endif
                    @break

                @default
                    {{-- Unknown element type --}}
            @endswitch
        @endforeach
    </div>
</body>
</html>
