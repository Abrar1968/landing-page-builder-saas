<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $page->title }} - Builder</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&family=Roboto:wght@100;300;400;500;700;900&family=Open+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700;800;900&family=Lato:wght@100;300;400;700;900&family=Poppins:wght@100;200;300;400;500;600;700;800;900&family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Animate.css for motion effects -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    @vite(['resources/css/app.css', 'resources/js/builder/main.js'])
</head>
<body class="antialiased">
    {{-- Vue App Mount Point --}}
    <div id="builder-app"></div>

    {{-- Page Data for Vue --}}
    <script id="page-data" type="application/json">
        {!! json_encode([
            'id' => $page->id,
            'title' => $page->title,
            'slug' => $page->slug,
            'content' => $page->content ?? [],
            'settings' => $page->settings ?? [],
            'status' => $page->status,
        ]) !!}
    </script>

    {{-- API Routes for Vue --}}
    <script>
        window.builderRoutes = {
            save: "{{ route('builder.save', $page) }}",
            publish: "{{ route('builder.publish', $page) }}",
            preview: "{{ route('builder.preview', $page) }}",
            pages: "{{ route('pages.index') }}",
            media: "/api/media",
            mediaUpload: "/api/media/upload"
        };
    </script>
</body>
</html>
