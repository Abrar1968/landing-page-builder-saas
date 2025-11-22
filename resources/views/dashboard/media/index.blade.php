@extends('layouts.dashboard')

@section('title', 'Media Library')

@section('content')
<div x-data="mediaLibrary()">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Media Library</h1>
            <p class="mt-1 text-sm text-gray-500">
                <span x-text="formatFileSize(storageUsed)"></span> of
                <span x-text="formatFileSize(storageLimit)"></span> used
            </p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button @click="$refs.fileInput.click()"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                </svg>
                Upload Files
            </button>
            <input type="file" x-ref="fileInput" @change="handleFileSelect($event)" multiple accept="image/*,video/*,audio/*,.pdf" class="hidden">
        </div>
    </div>

    <div class="mt-4 h-2 w-full rounded-full bg-gray-200">
        <div class="h-2 rounded-full bg-indigo-600 transition-all" :style="`width: ${Math.min((storageUsed / storageLimit) * 100, 100)}%`"></div>
    </div>

    <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="relative flex-1 max-w-md">
            <input type="text" placeholder="Search files..." x-model="search" @input.debounce.300ms="loadMedia()"
                   class="block w-full rounded-md border-0 py-2 pl-10 pr-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm">
            <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
            </svg>
        </div>

        <div class="flex items-center gap-4">
            <select x-model="filterType" @change="loadMedia()"
                    class="rounded-md border-0 py-2 pl-3 pr-8 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
                <option value="">All Types</option>
                <option value="image">Images</option>
                <option value="video">Videos</option>
                <option value="document">Documents</option>
            </select>

            <div class="flex rounded-md shadow-sm">
                <button @click="viewMode = 'grid'" :class="viewMode === 'grid' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-900 hover:bg-gray-50'"
                        class="relative inline-flex items-center rounded-l-md px-3 py-2 text-sm font-semibold ring-1 ring-inset ring-gray-300">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6z" />
                    </svg>
                </button>
                <button @click="viewMode = 'list'" :class="viewMode === 'list' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-900 hover:bg-gray-50'"
                        class="relative -ml-px inline-flex items-center rounded-r-md px-3 py-2 text-sm font-semibold ring-1 ring-inset ring-gray-300">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div x-show="loading" class="mt-8 flex justify-center">
        <svg class="animate-spin h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </div>

    <div x-show="!loading && viewMode === 'grid'" class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
        <template x-for="file in files" :key="file.id">
            <div class="group relative overflow-hidden rounded-lg bg-gray-100 cursor-pointer hover:shadow-lg transition-shadow"
                 @click="selectFile(file)">
                <div class="aspect-square">
                    <template x-if="file.type === 'image'">
                        <img :src="file.thumbnail_url || file.url" :alt="file.alt_text || file.name" class="h-full w-full object-cover">
                    </template>
                    <template x-if="file.type !== 'image'">
                        <div class="flex h-full w-full items-center justify-center">
                            <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        </div>
                    </template>
                </div>
                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 to-transparent p-2">
                    <p class="truncate text-xs text-white" x-text="file.name"></p>
                    <p class="text-xs text-white/70" x-text="formatFileSize(file.size)"></p>
                </div>
                <div class="absolute right-2 top-2 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button @click.stop="deleteFile(file)" class="rounded-full bg-red-500 p-1 text-white hover:bg-red-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </template>
    </div>

    <div x-show="!loading && viewMode === 'list'" class="mt-6 overflow-hidden bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">File</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Type</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Size</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Uploaded</th>
                    <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                <template x-for="file in files" :key="file.id">
                    <tr class="hover:bg-gray-50">
                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 flex-shrink-0 overflow-hidden rounded bg-gray-100">
                                    <template x-if="file.type === 'image'">
                                        <img :src="file.thumbnail_url || file.url" class="h-full w-full object-cover">
                                    </template>
                                </div>
                                <span class="font-medium text-gray-900" x-text="file.name"></span>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500" x-text="file.type"></td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500" x-text="formatFileSize(file.size)"></td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500" x-text="formatDate(file.created_at)"></td>
                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                            <button @click="copyUrl(file)" class="text-indigo-600 hover:text-indigo-900 mr-3">Copy URL</button>
                            <button @click="deleteFile(file)" class="text-red-600 hover:text-red-900">Delete</button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <div x-show="!loading && files.length === 0" class="mt-8 text-center py-12 bg-white rounded-lg shadow">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 19.5h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z" />
        </svg>
        <p class="mt-2 text-gray-500">No files uploaded yet</p>
        <button @click="$refs.fileInput.click()" class="mt-4 rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
            Upload your first file
        </button>
    </div>

    <div x-show="totalPages > 1" class="mt-6 flex items-center justify-center gap-2">
        <button @click="currentPage--; loadMedia()" :disabled="currentPage === 1"
                class="rounded-md border border-gray-300 px-3 py-2 text-sm disabled:opacity-50 hover:bg-gray-50">
            Previous
        </button>
        <span class="px-4 py-2 text-sm text-gray-600">
            Page <span x-text="currentPage"></span> of <span x-text="totalPages"></span>
        </span>
        <button @click="currentPage++; loadMedia()" :disabled="currentPage === totalPages"
                class="rounded-md border border-gray-300 px-3 py-2 text-sm disabled:opacity-50 hover:bg-gray-50">
            Next
        </button>
    </div>
</div>

<script>
function mediaLibrary() {
    return {
        loading: false,
        files: [],
        viewMode: 'grid',
        search: '',
        filterType: '',
        currentPage: 1,
        totalPages: 1,
        storageUsed: {{ $storageUsed ?? 0 }},
        storageLimit: {{ $storageLimit ?? 104857600 }},

        init() {
            this.loadMedia();
        },

        async loadMedia() {
            this.loading = true;
            const params = new URLSearchParams({
                page: this.currentPage,
                search: this.search,
                type: this.filterType,
            });

            try {
                const response = await fetch(`/api/media?${params}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();
                this.files = data.data;
                this.totalPages = data.meta.last_page;
            } catch (error) {
                console.error('Failed to load media:', error);
            } finally {
                this.loading = false;
            }
        },

        async handleFileSelect(event) {
            const files = Array.from(event.target.files);
            for (const file of files) {
                await this.uploadFile(file);
            }
            event.target.value = '';
            this.loadMedia();
        },

        async uploadFile(file) {
            const formData = new FormData();
            formData.append('file', file);

            try {
                await fetch('/api/media/upload', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    }
                });
            } catch (error) {
                console.error('Upload failed:', error);
            }
        },

        selectFile(file) {
            window.open(file.url, '_blank');
        },

        async copyUrl(file) {
            await navigator.clipboard.writeText(file.url);
            alert('URL copied to clipboard');
        },

        async deleteFile(file) {
            if (!confirm(`Delete "${file.name}"?`)) return;

            try {
                await fetch(`/api/media/${file.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    }
                });
                this.files = this.files.filter(f => f.id !== file.id);
            } catch (error) {
                console.error('Delete failed:', error);
            }
        },

        formatFileSize(bytes) {
            if (!bytes) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        },

        formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('en-US', {
                year: 'numeric', month: 'short', day: 'numeric'
            });
        }
    }
}
</script>
@endsection
