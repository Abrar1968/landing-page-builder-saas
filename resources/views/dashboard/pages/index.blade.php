@extends('layouts.dashboard')

@section('title', 'Pages')

@section('content')
<div x-data="pagesManager()">
    <!-- Header -->
    <div class="md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Pages</h2>
            <p class="mt-1 text-sm text-gray-500">Manage all your landing pages in one place.</p>
        </div>
        <div class="mt-4 flex md:ml-4 md:mt-0">
            <button @click="$dispatch('open-create-modal')"
                    class="inline-flex items-center rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 hover:from-indigo-700 hover:to-purple-700 transition-all">
                <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Create Page
            </button>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="mt-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="relative flex-1 max-w-md">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
            </div>
            <input type="text" placeholder="Search pages..." x-model="searchQuery"
                   class="block w-full rounded-xl border-0 bg-white py-3 pl-11 pr-4 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm">
        </div>

        <div class="flex items-center gap-3">
            <select x-model="statusFilter"
                    class="rounded-xl border-0 bg-white py-3 pl-4 pr-10 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-indigo-500">
                <option value="all">All Status</option>
                <option value="published">Published</option>
                <option value="draft">Draft</option>
            </select>

            <div class="flex rounded-xl shadow-sm">
                <button @click="viewMode = 'grid'"
                        :class="viewMode === 'grid' ? 'bg-indigo-600 text-white ring-indigo-600' : 'bg-white text-gray-600 hover:bg-gray-50 ring-gray-200'"
                        class="relative inline-flex items-center rounded-l-xl px-3 py-3 text-sm font-medium ring-1 ring-inset transition-colors">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                    </svg>
                </button>
                <button @click="viewMode = 'list'"
                        :class="viewMode === 'list' ? 'bg-indigo-600 text-white ring-indigo-600' : 'bg-white text-gray-600 hover:bg-gray-50 ring-gray-200'"
                        class="relative -ml-px inline-flex items-center rounded-r-xl px-3 py-3 text-sm font-medium ring-1 ring-inset transition-colors">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Grid View -->
    <div x-show="viewMode === 'grid'" class="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <template x-for="page in filteredPages" :key="page.id">
            <div class="group relative overflow-hidden rounded-2xl bg-white shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300">
                <div class="aspect-[16/9] bg-gradient-to-br from-gray-50 to-gray-100 overflow-hidden">
                    <div class="h-full w-full flex items-center justify-center">
                        <div class="text-center">
                            <div class="w-12 h-12 mx-auto bg-gray-200 rounded-xl flex items-center justify-center mb-2">
                                <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                                </svg>
                            </div>
                            <p class="text-xs text-gray-400">Preview</p>
                        </div>
                    </div>
                </div>

                <div class="p-4">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-semibold text-gray-900 truncate" x-text="page.name"></h3>
                            <p class="mt-1 text-xs text-gray-500 truncate" x-text="'/' + page.url"></p>
                        </div>
                        <div x-data="{ menuOpen: false }" class="relative ml-2">
                            <button @click="menuOpen = !menuOpen" class="flex items-center rounded-lg p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 12.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 18.75a.75.75 0 110-1.5.75.75 0 010 1.5z" />
                                </svg>
                            </button>
                            <div x-show="menuOpen" @click.outside="menuOpen = false"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-xl bg-white shadow-lg ring-1 ring-black ring-opacity-5 py-1 divide-y divide-gray-100" x-cloak>
                                <div class="py-1">
                                    <a :href="'/builder/' + page.id + '/edit'" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        <svg class="mr-3 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Edit
                                    </a>
                                    <button @click="duplicatePage(page.id); menuOpen = false" class="flex w-full items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        <svg class="mr-3 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                        Duplicate
                                    </button>
                                </div>
                                <div class="py-1">
                                    <button @click="deletePage(page.id); menuOpen = false" class="flex w-full items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                        <svg class="mr-3 h-4 w-4 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-between">
                        <span :class="page.status === 'published' ? 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20' : 'bg-yellow-50 text-yellow-700 ring-1 ring-inset ring-yellow-600/20'"
                              class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium capitalize"
                              x-text="page.status"></span>
                        <span class="text-xs text-gray-400" x-text="page.lastModified"></span>
                    </div>
                </div>

                <!-- Hover Overlay -->
                <div class="absolute inset-0 flex items-center justify-center bg-gray-900/60 opacity-0 group-hover:opacity-100 transition-all duration-300">
                    <div class="flex gap-2">
                        <a :href="'/builder/' + page.id + '/edit'" class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm hover:bg-gray-50 transition-colors">
                            Edit
                        </a>
                        <template x-if="page.status === 'published'">
                            <a :href="'/' + page.url" target="_blank" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                                View
                            </a>
                        </template>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- List View -->
    <div x-show="viewMode === 'list'" class="mt-8 overflow-hidden bg-white shadow-sm ring-1 ring-gray-200 rounded-2xl">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-4 pl-6 pr-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Page</th>
                    <th class="px-3 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                    <th class="px-3 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Last Modified</th>
                    <th class="relative py-4 pl-3 pr-6"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                <template x-for="page in filteredPages" :key="page.id">
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="whitespace-nowrap py-4 pl-6 pr-3 text-sm">
                            <div class="font-medium text-gray-900" x-text="page.name"></div>
                            <div class="text-gray-500" x-text="'/' + page.url"></div>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                            <span :class="page.status === 'published' ? 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20' : 'bg-yellow-50 text-yellow-700 ring-1 ring-inset ring-yellow-600/20'"
                                  class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium capitalize"
                                  x-text="page.status"></span>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500" x-text="page.lastModified"></td>
                        <td class="relative whitespace-nowrap py-4 pl-3 pr-6 text-right text-sm font-medium">
                            <a :href="'/builder/' + page.id + '/edit'" class="text-indigo-600 hover:text-indigo-900 font-semibold">Edit</a>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <!-- Empty State -->
    <div x-show="filteredPages.length === 0" class="mt-8 text-center py-16 bg-white rounded-2xl shadow-sm border border-gray-100">
        <div class="w-16 h-16 mx-auto bg-gray-100 rounded-2xl flex items-center justify-center mb-4">
            <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
        </div>
        <h3 class="text-sm font-semibold text-gray-900">No pages found</h3>
        <p class="mt-1 text-sm text-gray-500">Get started by creating your first landing page.</p>
        <button type="button"
                class="mt-4 inline-flex items-center rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 hover:from-indigo-700 hover:to-purple-700 transition-all"
                @click="$dispatch('open-create-modal')">
            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Create Page
        </button>
    </div>
</div>

<script>
function pagesManager() {
    return {
        viewMode: 'grid',
        searchQuery: '',
        statusFilter: 'all',
        pages: @json($pages),

        get filteredPages() {
            return this.pages.filter(page => {
                const matchesSearch = page.name.toLowerCase().includes(this.searchQuery.toLowerCase());
                const matchesStatus = this.statusFilter === 'all' || page.status === this.statusFilter;
                return matchesSearch && matchesStatus;
            });
        },

        duplicatePage(id) {
            fetch(`/pages/${id}/duplicate`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            }).then(() => window.location.reload());
        },

        deletePage(id) {
            if (confirm('Are you sure you want to delete this page?')) {
                fetch(`/pages/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                }).then(() => window.location.reload());
            }
        }
    }
}
</script>
@endsection

@push('modals')
    @include('components.dashboard.create-page-modal')
@endpush
