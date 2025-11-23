<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $page->title }} - Builder</title>

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
            media: "{{ route('api.media.index') }}",
            mediaUpload: "{{ route('api.media.store') }}"
        };
    </script>
</body>
</html>
