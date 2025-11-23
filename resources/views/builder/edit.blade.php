@extends('layouts.builder')

@section('content')
<div x-data="builderApp()" x-init="init()" class="h-screen flex flex-col">
    {{-- Page Data --}}
    @php
        $pageData = [
            'id' => $page->id,
            'title' => $page->title,
            'slug' => $page->slug,
            'elements' => $page->content ?? [],
            'settings' => $page->settings ?? [],
            'status' => $page->status,
        ];
    @endphp
    <script id="page-data" type="application/json">
        {!! json_encode($pageData) !!}
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

    {{-- Delete Confirmation Modal --}}
    <div x-show="showDeleteConfirm"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showDeleteConfirm"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                 @click="showDeleteConfirm = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div x-show="showDeleteConfirm"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Delete Page</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Are you sure you want to delete this page? This action cannot be undone. All of your content will be permanently removed.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button type="button"
                            @click="deletePage()"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Delete
                    </button>
                    <button type="button"
                            @click="showDeleteConfirm = false"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
