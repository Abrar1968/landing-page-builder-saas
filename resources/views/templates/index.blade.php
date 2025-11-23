@extends('layouts.dashboard')

@section('title', 'Template Gallery')

@section('content')
<div x-data="templateGallery()" x-init="init()" class="min-h-screen bg-gray-50">
    {{-- Header --}}
    <div class="bg-white border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="text-center">
                <h1 class="text-3xl sm:text-4xl font-bold text-gray-900">Template Gallery</h1>
                <p class="mt-4 text-lg text-gray-600 max-w-2xl mx-auto">Choose from our collection of professionally designed templates to kickstart your landing page</p>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col lg:flex-row gap-8">
            {{-- Sidebar Filters --}}
            <aside class="w-full lg:w-72 shrink-0">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-8">
                    {{-- Search --}}
                    <div class="mb-6">
                        <label for="search" class="block text-sm font-semibold text-gray-900 mb-3">Search</label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input
                                type="text"
                                id="search"
                                x-model.debounce.300ms="filters.search"
                                @input="filterTemplates()"
                                placeholder="Search templates..."
                                class="w-full pl-11 pr-4 py-3 border-0 bg-gray-50 rounded-xl text-sm text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-500"
                            >
                        </div>
                    </div>

                    {{-- Categories --}}
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-900 mb-3">Categories</h3>
                        <div class="space-y-1">
                            <button
                                @click="filters.category = null; filterTemplates()"
                                :class="filters.category === null ? 'bg-gradient-to-r from-indigo-50 to-purple-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-gray-600 hover:bg-gray-50'"
                                class="w-full text-left px-4 py-2.5 rounded-xl text-sm font-medium transition-all"
                            >
                                <span class="flex items-center justify-between">
                                    All Templates
                                    <span class="text-xs text-gray-400">{{ $templates->total() }}</span>
                                </span>
                            </button>
                            @foreach($categories as $category)
                            <button
                                @click="filters.category = {{ $category->id }}; filterTemplates()"
                                :class="filters.category === {{ $category->id }} ? 'bg-gradient-to-r from-indigo-50 to-purple-50 text-indigo-700 ring-1 ring-indigo-100' : 'text-gray-600 hover:bg-gray-50'"
                                class="w-full text-left px-4 py-2.5 rounded-xl text-sm font-medium transition-all"
                            >
                                <span class="flex items-center justify-between">
                                    <span class="flex items-center gap-2">
                                        @if($category->icon)
                                            <span class="text-gray-400">{!! $category->icon !!}</span>
                                        @endif
                                        {{ $category->name }}
                                    </span>
                                    <span class="text-xs text-gray-400">{{ $category->templates_count }}</span>
                                </span>
                            </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Sort --}}
                    <div>
                        <label for="sort" class="block text-sm font-semibold text-gray-900 mb-3">Sort By</label>
                        <select
                            id="sort"
                            x-model="filters.sort"
                            @change="filterTemplates()"
                            class="w-full border-0 bg-gray-50 rounded-xl py-3 px-4 text-sm text-gray-900 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-indigo-500"
                        >
                            <option value="popular">Most Popular</option>
                            <option value="rating">Highest Rated</option>
                            <option value="newest">Newest First</option>
                            <option value="name">Name A-Z</option>
                        </select>
                    </div>
                </div>
            </aside>

            {{-- Main Content --}}
            <main class="flex-1">
                {{-- Results Header --}}
                <div class="flex items-center justify-between mb-6">
                    <p class="text-sm text-gray-600">
                        <span class="font-semibold text-gray-900" x-text="totalResults"></span> templates found
                    </p>
                </div>

                {{-- Template Grid --}}
                <div
                    id="template-grid"
                    class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6"
                >
                    @include('templates.partials.grid', ['templates' => $templates])
                </div>

                {{-- Load More --}}
                <div x-show="hasMore" class="mt-8 text-center">
                    <button
                        @click="loadMore()"
                        :disabled="loading"
                        class="inline-flex items-center px-6 py-3 rounded-xl text-sm font-semibold text-gray-700 bg-white shadow-sm border border-gray-200 hover:bg-gray-50 disabled:opacity-50 transition-colors"
                    >
                        <span x-show="!loading">Load More Templates</span>
                        <span x-show="loading" class="inline-flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                            </svg>
                            Loading...
                        </span>
                    </button>
                </div>

                {{-- Empty State --}}
                <div x-show="totalResults === 0" class="text-center py-16 bg-white rounded-2xl shadow-sm border border-gray-100">
                    <div class="w-16 h-16 mx-auto bg-gray-100 rounded-2xl flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900">No templates found</h3>
                    <p class="mt-1 text-sm text-gray-500">Try adjusting your search or filter criteria.</p>
                </div>
            </main>
        </div>
    </div>

    {{-- Preview Modal --}}
    @include('templates.partials.preview-modal')

    {{-- Apply Template Modal --}}
    @include('templates.partials.apply-modal')
</div>

@push('scripts')
<script>
function templateGallery() {
    return {
        filters: {
            category: null,
            search: '',
            sort: 'popular',
            tags: []
        },
        loading: false,
        hasMore: {{ $templates->hasMorePages() ? 'true' : 'false' }},
        currentPage: 1,
        totalResults: {{ $templates->total() }},
        selectedTemplate: null,
        showPreviewModal: false,
        showApplyModal: false,
        newPageName: '',
        applying: false,

        init() {
            const params = new URLSearchParams(window.location.search);
            if (params.get('category')) this.filters.category = parseInt(params.get('category'));
            if (params.get('search')) this.filters.search = params.get('search');
            if (params.get('sort')) this.filters.sort = params.get('sort');
        },

        async filterTemplates() {
            this.loading = true;
            this.currentPage = 1;

            try {
                const response = await fetch('/templates/filter?' + this.buildQueryString());
                const data = await response.json();

                document.getElementById('template-grid').innerHTML = data.html;
                this.hasMore = data.hasMore;
                this.totalResults = data.total;
                this.updateUrl();
            } catch (error) {
                console.error('Filter error:', error);
            } finally {
                this.loading = false;
            }
        },

        async loadMore() {
            this.loading = true;
            this.currentPage++;

            try {
                const response = await fetch('/templates/filter?' + this.buildQueryString() + '&page=' + this.currentPage);
                const data = await response.json();

                document.getElementById('template-grid').insertAdjacentHTML('beforeend', data.html);
                this.hasMore = data.hasMore;
            } catch (error) {
                console.error('Load more error:', error);
                this.currentPage--;
            } finally {
                this.loading = false;
            }
        },

        buildQueryString() {
            const params = new URLSearchParams();
            if (this.filters.category) params.set('category', this.filters.category);
            if (this.filters.search) params.set('search', this.filters.search);
            if (this.filters.sort) params.set('sort', this.filters.sort);
            return params.toString();
        },

        updateUrl() {
            const url = new URL(window.location);
            url.search = this.buildQueryString();
            window.history.replaceState({}, '', url);
        },

        async openPreview(templateId) {
            try {
                const response = await fetch(`/templates/${templateId}`);
                this.selectedTemplate = (await response.json()).template;
                this.showPreviewModal = true;
            } catch (error) {
                console.error('Preview error:', error);
            }
        },

        openApplyModal(template) {
            this.selectedTemplate = template;
            this.newPageName = template.name;
            this.showApplyModal = true;
        },

        async applyTemplate() {
            if (!this.newPageName.trim() || this.applying) return;

            this.applying = true;

            try {
                const response = await fetch(`/templates/${this.selectedTemplate.id}/apply`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        name: this.newPageName
                    })
                });

                const data = await response.json();

                if (data.success) {
                    window.location.href = data.page.edit_url;
                }
            } catch (error) {
                console.error('Apply error:', error);
            } finally {
                this.applying = false;
            }
        }
    }
}
</script>
@endpush
@endsection
