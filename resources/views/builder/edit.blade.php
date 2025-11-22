@extends('layouts.builder')

@section('content')
<div x-data="builderApp()" x-init="init()" class="h-screen flex flex-col">
    {{-- Page Data --}}
    <script id="page-data" type="application/json">
        @json([
            'id' => $page->id,
            'title' => $page->title,
            'slug' => $page->slug,
            'elements' => $page->content ?? [],
            'settings' => $page->settings ?? [],
            'status' => $page->status,
        ])
    </script>

    {{-- Toolbar --}}
    @include('builder.partials.toolbar')

    {{-- Main Builder Area --}}
    <div class="flex flex-1 overflow-hidden">
        {{-- Left Sidebar - Elements Panel --}}
        <aside class="w-64 bg-white border-r border-gray-200 flex flex-col shrink-0">
            <div class="p-4 border-b border-gray-200">
                <h3 class="font-semibold text-gray-900">Elements</h3>
            </div>
            <div class="flex-1 overflow-y-auto p-4">
                @include('builder.partials.elements-panel')
            </div>
        </aside>

        {{-- Canvas Area --}}
        <main class="flex-1 overflow-auto p-8 bg-gray-200">
            @include('builder.partials.canvas')
        </main>

        {{-- Right Sidebar - Properties Panel --}}
        <aside x-show="selectedElement"
               x-transition:enter="transition ease-out duration-200"
               x-transition:enter-start="translate-x-full"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transition ease-in duration-200"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="translate-x-full"
               x-cloak
               class="w-80 bg-white border-l border-gray-200 flex flex-col shrink-0 overflow-y-auto">
            @include('builder.partials.properties-panel')
        </aside>
    </div>

    {{-- Page Settings Modal --}}
    @include('builder.partials.page-settings-modal')
</div>
@endsection
