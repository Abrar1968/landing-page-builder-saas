<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->settings['metaTitle'] ?? $page->title }}</title>
    @if(!empty($page->settings['metaDescription']))
        <meta name="description" content="{{ $page->settings['metaDescription'] }}">
    @endif
    @if(!empty($page->settings['ogImage']))
        <meta property="og:image" content="{{ $page->settings['ogImage'] }}">
    @endif
    <meta property="og:title" content="{{ $page->settings['metaTitle'] ?? $page->title }}">
    <meta property="og:type" content="website">
    @vite(['resources/css/app.css'])
    
    {{-- Widget-generated CSS for hover states and responsive breakpoints --}}
    @if(!empty($widgetCss))
    <style>
        {!! $widgetCss !!}
    </style>
    @endif
</head>
<body class="min-h-screen">
    {!! $html !!}

    @if($page->settings['analytics_id'] ?? false)
        <!-- Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $page->settings['analytics_id'] }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ $page->settings['analytics_id'] }}');
        </script>
    @endif
</body>
</html>
