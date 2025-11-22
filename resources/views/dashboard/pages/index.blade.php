@extends('layouts.dashboard')

@section('title', 'Pages')

@section('content')
<div x-data="pagesManager()">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Pages</h1>
            <p class="mt-1 text-sm text-gray-500">Manage all your landing pages in one place.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button @click="$dispatch('open-create-modal')"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Create Page
            </button>
        </div>
    </div>

    <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="relative flex-1 max-w-md">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
            </div>
            <input type="text" placeholder="Search pages..." x-model="searchQuery"
                   class="block w-full rounded-md border-0 py-2 pl-10 pr-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm">
        </div>

        <div class="flex items-center gap-4">
            <select x-model="statusFilter"
                    class="rounded-md border-0 py-2 pl-3 pr-8 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                <option value="all">All Status</option>
                <option value="published">Published</option>
                <option value="draft">Draft</option>
            </select>

            <div class="flex rounded-md shadow-sm">
                <button @click="viewMode = 'grid'"
                        :class="viewMode === 'grid' ? 'bg-indigo-600 text-white ring-indigo-600' : 'bg-white text-gray-900 hover:bg-gray-50'"
                        class="relative inline-flex items-center rounded-l-md px-3 py-2 text-sm font-semibold ring-1 ring-inset ring-gray-300">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6z" />
                    </svg>
                </button>
                <button @click="viewMode = 'list'"
                        :class="viewMode === 'list' ? 'bg-indigo-600 text-white ring-indigo-600' : 'bg-white text-gray-900 hover:bg-gray-50'"
                        class="relative -ml-px inline-flex items-center rounded-r-md px-3 py-2 text-sm font-semibold ring-1 ring-inset ring-gray-300">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div x-show="viewMode === 'grid'" class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <template x-for="page in filteredPages" :key="page.id">
            <div class="group relative overflow-hidden rounded-lg bg-white shadow hover:shadow-md transition-shadow">
                <div class="aspect-[16/9] bg-gray-100 overflow-hidden flex items-center justify-center text-gray-400">
                    Preview
                </div>

                <div class="p-4">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-semibold text-gray-900 truncate" x-text="page.name"></h3>
                            <p class="mt-1 text-xs text-gray-500 truncate" x-text="'/' + page.url"></p>
                        </div>
                        <div x-data="{ menuOpen: false }" class="relative">
                            <button @click="menuOpen = !menuOpen" class="-m-2 flex items-center rounded-full p-2 text-gray-400 hover:text-gray-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 12.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 18.75a.75.75 0 110-1.5.75.75 0 010 1.5z" />
                                </svg>
                            </button>
                            <div x-show="menuOpen" @click.outside="menuOpen = false" x-transition
                                 class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 py-1" x-cloak>
                                <a :href="'/builder/' + page.id + '/edit'" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Edit</a>
                                <button @click="duplicatePage(page.id)" class="flex w-full items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Duplicate</button>
                                <div class="border-t border-gray-100"></div>
                                <button @click="deletePage(page.id)" class="flex w-full items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50">Delete</button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 flex items-center justify-between">
                        <span :class="page.status === 'published' ? 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20' : 'bg-yellow-50 text-yellow-700 ring-1 ring-inset ring-yellow-600/20'"
                              class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium"
                              x-text="page.status"></span>
                        <span class="text-xs text-gray-500" x-text="page.lastModified"></span>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <div x-show="viewMode === 'list'" class="mt-6 overflow-hidden bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Page</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Last Modified</th>
                    <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                <template x-for="page in filteredPages" :key="page.id">
                    <tr class="hover:bg-gray-50">
                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                            <div class="font-medium text-gray-900" x-text="page.name"></div>
                            <div class="text-gray-500" x-text="'/' + page.url"></div>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                            <span :class="page.status === 'published' ? 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20' : 'bg-yellow-50 text-yellow-700 ring-1 ring-inset ring-yellow-600/20'"
                                  class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium"
                                  x-text="page.status"></span>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500" x-text="page.lastModified"></td>
                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                            <a :href="'/builder/' + page.id + '/edit'" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <div x-show="filteredPages.length === 0" class="mt-6 text-center py-12 bg-white rounded-lg shadow">
        <p class="text-gray-500">No pages found.</p>
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
