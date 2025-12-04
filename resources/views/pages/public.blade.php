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
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&family=Roboto:wght@100;300;400;500;700;900&family=Open+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700;800;900&family=Lato:wght@100;300;400;700;900&family=Poppins:wght@100;200;300;400;500;600;700;800;900&family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Animate.css for motion effects -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    @vite(['resources/css/app.css'])

    <style>
        body { margin: 0; padding: 0; }
        .builder-section { width: 100%; box-sizing: border-box; }
        .builder-container { width: 100%; box-sizing: border-box; }
        .builder-column { box-sizing: border-box; flex-shrink: 0; }
    </style>

    {{-- Widget-generated CSS for hover states and responsive breakpoints --}}
    @if(!empty($widgetCss))
    <style>
        {!! $widgetCss !!}
    </style>
    @endif
</head>
<body class="min-h-screen">
    {!! $html !!}

    {{-- Interactive widgets JavaScript --}}
    <script src="{{ asset('js/widgets.js') }}"></script>

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
