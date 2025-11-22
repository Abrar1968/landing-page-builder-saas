# Media Library Feature

Complete media management system for the Landing Page Builder with upload, organization, and integration capabilities.

## Table of Contents

1. [Media Library Modal](#media-library-modal)
2. [File Upload](#file-upload)
3. [Image Features](#image-features)
4. [File Management](#file-management)
5. [Storage Configuration](#storage-configuration)
6. [Integration with Builder](#integration-with-builder)
7. [MediaController](#mediacontroller)
8. [MediaService](#mediaservice)
9. [Security](#security)

---

## Media Library Modal

### Blade Component

```blade
{{-- resources/views/components/media-library-modal.blade.php --}}
<div
    x-data="mediaLibrary()"
    x-show="open"
    x-on:open-media-library.window="open = true; loadMedia()"
    x-on:keydown.escape.window="open = false"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
>
    {{-- Backdrop --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/50"
        @click="open = false"
    ></div>

    {{-- Modal Content --}}
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative bg-white rounded-xl shadow-2xl w-full max-w-6xl max-h-[90vh] flex flex-col"
            @click.stop
        >
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Media Library</h2>
                <button @click="open = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Toolbar --}}
            <div class="px-6 py-4 border-b border-gray-200 space-y-4">
                <div class="flex flex-wrap items-center gap-4">
                    {{-- Search --}}
                    <div class="flex-1 min-w-64">
                        <div class="relative">
                            <input
                                type="text"
                                x-model.debounce.300ms="search"
                                @input="loadMedia()"
                                placeholder="Search files..."
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            >
                            <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>

                    {{-- Filter by Type --}}
                    <select
                        x-model="filterType"
                        @change="loadMedia()"
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">All Types</option>
                        <option value="image">Images</option>
                        <option value="video">Videos</option>
                        <option value="document">Documents</option>
                        <option value="audio">Audio</option>
                    </select>

                    {{-- Sort --}}
                    <select
                        x-model="sortBy"
                        @change="loadMedia()"
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="created_at:desc">Newest First</option>
                        <option value="created_at:asc">Oldest First</option>
                        <option value="name:asc">Name A-Z</option>
                        <option value="name:desc">Name Z-A</option>
                        <option value="size:desc">Largest First</option>
                        <option value="size:asc">Smallest First</option>
                    </select>

                    {{-- View Toggle --}}
                    <div class="flex items-center bg-gray-100 rounded-lg p-1">
                        <button
                            @click="viewMode = 'grid'"
                            :class="viewMode === 'grid' ? 'bg-white shadow-sm' : ''"
                            class="p-2 rounded-md transition-all"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                        </button>
                        <button
                            @click="viewMode = 'list'"
                            :class="viewMode === 'list' ? 'bg-white shadow-sm' : ''"
                            class="p-2 rounded-md transition-all"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Upload Button --}}
                    <button
                        @click="$refs.fileInput.click()"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        Upload
                    </button>
                    <input
                        type="file"
                        x-ref="fileInput"
                        @change="handleFileSelect($event)"
                        multiple
                        accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx"
                        class="hidden"
                    >
                </div>
            </div>

            {{-- Content Area --}}
            <div class="flex-1 overflow-y-auto p-6">
                {{-- Drop Zone --}}
                <div
                    x-show="isDragging"
                    class="absolute inset-0 bg-blue-50 border-2 border-dashed border-blue-400 rounded-xl flex items-center justify-center z-10"
                >
                    <div class="text-center">
                        <svg class="w-16 h-16 mx-auto text-blue-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <p class="text-lg font-medium text-blue-600">Drop files here to upload</p>
                    </div>
                </div>

                {{-- Loading State --}}
                <div x-show="loading" class="flex items-center justify-center py-12">
                    <svg class="animate-spin w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                    </svg>
                </div>

                {{-- Grid View --}}
                <div
                    x-show="!loading && viewMode === 'grid'"
                    class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4"
                >
                    <template x-for="file in files" :key="file.id">
                        <div
                            @click="selectFile(file)"
                            @dblclick="insertFile(file)"
                            :class="selectedFile?.id === file.id ? 'ring-2 ring-blue-500' : ''"
                            class="group relative bg-gray-100 rounded-lg overflow-hidden cursor-pointer hover:shadow-lg transition-all"
                        >
                            {{-- Thumbnail --}}
                            <div class="aspect-square">
                                <template x-if="file.type === 'image'">
                                    <img
                                        :src="file.thumbnail_url"
                                        :alt="file.alt_text || file.name"
                                        class="w-full h-full object-cover"
                                    >
                                </template>
                                <template x-if="file.type !== 'image'">
                                    <div class="w-full h-full flex items-center justify-center">
                                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                </template>
                            </div>

                            {{-- File Info Overlay --}}
                            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 to-transparent p-2">
                                <p class="text-white text-xs truncate" x-text="file.name"></p>
                                <p class="text-white/70 text-xs" x-text="formatFileSize(file.size)"></p>
                            </div>

                            {{-- Selection Indicator --}}
                            <div
                                x-show="selectedFile?.id === file.id"
                                class="absolute top-2 right-2 w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center"
                            >
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- List View --}}
                <div x-show="!loading && viewMode === 'list'" class="space-y-2">
                    <template x-for="file in files" :key="file.id">
                        <div
                            @click="selectFile(file)"
                            @dblclick="insertFile(file)"
                            :class="selectedFile?.id === file.id ? 'bg-blue-50 border-blue-200' : 'bg-white border-gray-200'"
                            class="flex items-center gap-4 p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors"
                        >
                            {{-- Thumbnail --}}
                            <div class="w-12 h-12 flex-shrink-0 bg-gray-100 rounded-lg overflow-hidden">
                                <template x-if="file.type === 'image'">
                                    <img :src="file.thumbnail_url" :alt="file.name" class="w-full h-full object-cover">
                                </template>
                                <template x-if="file.type !== 'image'">
                                    <div class="w-full h-full flex items-center justify-center">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                </template>
                            </div>

                            {{-- File Info --}}
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-900 truncate" x-text="file.name"></p>
                                <p class="text-sm text-gray-500">
                                    <span x-text="formatFileSize(file.size)"></span>
                                    <span class="mx-1">-</span>
                                    <span x-text="formatDate(file.created_at)"></span>
                                </p>
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center gap-2">
                                <button
                                    @click.stop="copyUrl(file)"
                                    class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100"
                                    title="Copy URL"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                                    </svg>
                                </button>
                                <button
                                    @click.stop="deleteFile(file)"
                                    class="p-2 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50"
                                    title="Delete"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Empty State --}}
                <div x-show="!loading && files.length === 0" class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-gray-500 mb-4">No files found</p>
                    <button
                        @click="$refs.fileInput.click()"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                    >
                        Upload your first file
                    </button>
                </div>

                {{-- Pagination --}}
                <div x-show="totalPages > 1" class="flex items-center justify-center gap-2 mt-6">
                    <button
                        @click="currentPage--; loadMedia()"
                        :disabled="currentPage === 1"
                        class="px-3 py-2 border border-gray-300 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                    >
                        Previous
                    </button>
                    <span class="px-4 py-2 text-gray-600">
                        Page <span x-text="currentPage"></span> of <span x-text="totalPages"></span>
                    </span>
                    <button
                        @click="currentPage++; loadMedia()"
                        :disabled="currentPage === totalPages"
                        class="px-3 py-2 border border-gray-300 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
                    >
                        Next
                    </button>
                </div>
            </div>

            {{-- Footer with Selected File Details --}}
            <div x-show="selectedFile" class="border-t border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden">
                            <template x-if="selectedFile?.type === 'image'">
                                <img :src="selectedFile?.thumbnail_url" class="w-full h-full object-cover">
                            </template>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900" x-text="selectedFile?.name"></p>
                            <p class="text-sm text-gray-500">
                                <span x-text="selectedFile?.dimensions"></span>
                                <span class="mx-1">-</span>
                                <span x-text="formatFileSize(selectedFile?.size)"></span>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <button
                            @click="editFile(selectedFile)"
                            class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            Edit Details
                        </button>
                        <button
                            @click="insertFile(selectedFile)"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                        >
                            Insert
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
```

### AlpineJS Component

```javascript
// resources/js/components/media-library.js
function mediaLibrary() {
    return {
        open: false,
        loading: false,
        files: [],
        selectedFile: null,
        viewMode: 'grid',
        search: '',
        filterType: '',
        sortBy: 'created_at:desc',
        currentPage: 1,
        totalPages: 1,
        perPage: 24,
        isDragging: false,
        uploadQueue: [],
        callback: null,

        init() {
            // Setup drag and drop
            this.$el.addEventListener('dragenter', (e) => {
                e.preventDefault();
                this.isDragging = true;
            });

            this.$el.addEventListener('dragleave', (e) => {
                e.preventDefault();
                if (!this.$el.contains(e.relatedTarget)) {
                    this.isDragging = false;
                }
            });

            this.$el.addEventListener('dragover', (e) => {
                e.preventDefault();
            });

            this.$el.addEventListener('drop', (e) => {
                e.preventDefault();
                this.isDragging = false;
                this.handleFileDrop(e.dataTransfer.files);
            });

            // Listen for callback setup
            window.addEventListener('set-media-callback', (e) => {
                this.callback = e.detail;
            });
        },

        async loadMedia() {
            this.loading = true;

            const [sortField, sortDirection] = this.sortBy.split(':');

            const params = new URLSearchParams({
                page: this.currentPage,
                per_page: this.perPage,
                search: this.search,
                type: this.filterType,
                sort_by: sortField,
                sort_direction: sortDirection,
            });

            try {
                const response = await fetch(`/api/media?${params}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const data = await response.json();
                this.files = data.data;
                this.totalPages = data.meta.last_page;
                this.currentPage = data.meta.current_page;
            } catch (error) {
                console.error('Failed to load media:', error);
                this.showNotification('Failed to load media files', 'error');
            } finally {
                this.loading = false;
            }
        },

        selectFile(file) {
            this.selectedFile = file;
        },

        insertFile(file) {
            if (this.callback) {
                this.callback(file);
            }

            window.dispatchEvent(new CustomEvent('media-selected', {
                detail: file
            }));

            this.open = false;
            this.selectedFile = null;
        },

        handleFileSelect(event) {
            this.handleFileDrop(event.target.files);
            event.target.value = '';
        },

        async handleFileDrop(fileList) {
            const files = Array.from(fileList);

            for (const file of files) {
                await this.uploadFile(file);
            }

            this.loadMedia();
        },

        async uploadFile(file) {
            const formData = new FormData();
            formData.append('file', file);

            const uploadItem = {
                id: Date.now(),
                name: file.name,
                progress: 0,
                status: 'uploading',
            };

            this.uploadQueue.push(uploadItem);

            try {
                const response = await fetch('/api/media/upload', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });

                if (!response.ok) {
                    const error = await response.json();
                    throw new Error(error.message || 'Upload failed');
                }

                uploadItem.status = 'complete';
                this.showNotification(`${file.name} uploaded successfully`, 'success');
            } catch (error) {
                uploadItem.status = 'error';
                this.showNotification(error.message, 'error');
            } finally {
                setTimeout(() => {
                    this.uploadQueue = this.uploadQueue.filter(item => item.id !== uploadItem.id);
                }, 3000);
            }
        },

        async deleteFile(file) {
            if (!confirm(`Are you sure you want to delete "${file.name}"?`)) {
                return;
            }

            try {
                await fetch(`/api/media/${file.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });

                this.files = this.files.filter(f => f.id !== file.id);

                if (this.selectedFile?.id === file.id) {
                    this.selectedFile = null;
                }

                this.showNotification('File deleted successfully', 'success');
            } catch (error) {
                this.showNotification('Failed to delete file', 'error');
            }
        },

        async copyUrl(file) {
            try {
                await navigator.clipboard.writeText(file.url);
                this.showNotification('URL copied to clipboard', 'success');
            } catch (error) {
                this.showNotification('Failed to copy URL', 'error');
            }
        },

        editFile(file) {
            window.dispatchEvent(new CustomEvent('edit-media-file', {
                detail: file
            }));
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
                year: 'numeric',
                month: 'short',
                day: 'numeric',
            });
        },

        showNotification(message, type = 'info') {
            window.dispatchEvent(new CustomEvent('notification', {
                detail: { message, type }
            }));
        },
    };
}

window.mediaLibrary = mediaLibrary;
```

---

## File Upload

### Upload Component with Progress

```blade
{{-- resources/views/components/media-uploader.blade.php --}}
<div x-data="mediaUploader()" class="space-y-4">
    {{-- Drop Zone --}}
    <div
        @dragenter.prevent="isDragging = true"
        @dragleave.prevent="isDragging = false"
        @dragover.prevent
        @drop.prevent="handleDrop($event)"
        :class="isDragging ? 'border-blue-500 bg-blue-50' : 'border-gray-300'"
        class="border-2 border-dashed rounded-xl p-8 text-center transition-colors"
    >
        <input
            type="file"
            x-ref="fileInput"
            @change="handleFileSelect($event)"
            multiple
            :accept="allowedTypes.join(',')"
            class="hidden"
        >

        <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
        </svg>

        <p class="text-gray-600 mb-2">
            Drag and drop files here, or
            <button @click="$refs.fileInput.click()" class="text-blue-600 hover:underline">browse</button>
        </p>
        <p class="text-sm text-gray-400">
            Max file size: <span x-text="formatFileSize(maxFileSize)"></span>
        </p>
    </div>

    {{-- Upload Queue --}}
    <div x-show="uploads.length > 0" class="space-y-3">
        <template x-for="upload in uploads" :key="upload.id">
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <div class="flex items-center gap-3">
                    {{-- Preview --}}
                    <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center overflow-hidden">
                        <template x-if="upload.preview">
                            <img :src="upload.preview" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!upload.preview">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </template>
                    </div>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate" x-text="upload.name"></p>
                        <p class="text-xs text-gray-500" x-text="formatFileSize(upload.size)"></p>
                    </div>

                    {{-- Status --}}
                    <div class="flex items-center gap-2">
                        <template x-if="upload.status === 'uploading'">
                            <span class="text-sm text-blue-600" x-text="upload.progress + '%'"></span>
                        </template>
                        <template x-if="upload.status === 'complete'">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </template>
                        <template x-if="upload.status === 'error'">
                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </template>
                        <button
                            @click="cancelUpload(upload)"
                            x-show="upload.status === 'uploading'"
                            class="p-1 text-gray-400 hover:text-red-500"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Progress Bar --}}
                <div x-show="upload.status === 'uploading'" class="mt-3">
                    <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                        <div
                            class="h-full bg-blue-600 transition-all duration-300"
                            :style="`width: ${upload.progress}%`"
                        ></div>
                    </div>
                </div>

                {{-- Error Message --}}
                <p
                    x-show="upload.error"
                    class="mt-2 text-sm text-red-600"
                    x-text="upload.error"
                ></p>
            </div>
        </template>
    </div>
</div>
```

### Uploader AlpineJS Component

```javascript
// resources/js/components/media-uploader.js
function mediaUploader() {
    return {
        uploads: [],
        isDragging: false,
        maxFileSize: 10 * 1024 * 1024, // 10MB
        allowedTypes: [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/svg+xml',
            'video/mp4',
            'video/webm',
            'audio/mpeg',
            'audio/wav',
            'application/pdf',
        ],

        handleFileSelect(event) {
            this.processFiles(event.target.files);
            event.target.value = '';
        },

        handleDrop(event) {
            this.isDragging = false;
            this.processFiles(event.dataTransfer.files);
        },

        processFiles(fileList) {
            Array.from(fileList).forEach(file => {
                // Validate file
                const validation = this.validateFile(file);
                if (!validation.valid) {
                    this.addUpload(file, 'error', validation.error);
                    return;
                }

                this.uploadFile(file);
            });
        },

        validateFile(file) {
            // Check file type
            if (!this.allowedTypes.includes(file.type)) {
                return {
                    valid: false,
                    error: `File type "${file.type}" is not allowed`
                };
            }

            // Check file size
            if (file.size > this.maxFileSize) {
                return {
                    valid: false,
                    error: `File size exceeds ${this.formatFileSize(this.maxFileSize)} limit`
                };
            }

            return { valid: true };
        },

        addUpload(file, status = 'pending', error = null) {
            const upload = {
                id: Date.now() + Math.random(),
                name: file.name,
                size: file.size,
                type: file.type,
                status: status,
                progress: 0,
                error: error,
                preview: null,
                xhr: null,
            };

            // Generate preview for images
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    upload.preview = e.target.result;
                };
                reader.readAsDataURL(file);
            }

            this.uploads.push(upload);
            return upload;
        },

        async uploadFile(file) {
            const upload = this.addUpload(file, 'uploading');

            const formData = new FormData();
            formData.append('file', file);

            const xhr = new XMLHttpRequest();
            upload.xhr = xhr;

            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable) {
                    upload.progress = Math.round((e.loaded / e.total) * 100);
                }
            });

            xhr.addEventListener('load', () => {
                if (xhr.status >= 200 && xhr.status < 300) {
                    upload.status = 'complete';
                    upload.progress = 100;

                    const response = JSON.parse(xhr.responseText);
                    this.$dispatch('file-uploaded', response.data);

                    // Remove completed upload after delay
                    setTimeout(() => {
                        this.uploads = this.uploads.filter(u => u.id !== upload.id);
                    }, 3000);
                } else {
                    upload.status = 'error';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        upload.error = response.message || 'Upload failed';
                    } catch {
                        upload.error = 'Upload failed';
                    }
                }
            });

            xhr.addEventListener('error', () => {
                upload.status = 'error';
                upload.error = 'Network error occurred';
            });

            xhr.open('POST', '/api/media/upload');
            xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.send(formData);
        },

        cancelUpload(upload) {
            if (upload.xhr) {
                upload.xhr.abort();
            }
            this.uploads = this.uploads.filter(u => u.id !== upload.id);
        },

        formatFileSize(bytes) {
            if (!bytes) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        },
    };
}

window.mediaUploader = mediaUploader;
```

---

## Image Features

### Image Preview Modal

```blade
{{-- resources/views/components/image-preview-modal.blade.php --}}
<div
    x-data="imagePreview()"
    x-show="open"
    x-on:preview-image.window="showPreview($event.detail)"
    x-on:keydown.escape.window="open = false"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/90"
>
    {{-- Close Button --}}
    <button
        @click="open = false"
        class="absolute top-4 right-4 text-white/70 hover:text-white"
    >
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>

    {{-- Image --}}
    <img
        x-show="image"
        :src="image?.url"
        :alt="image?.alt_text || image?.name"
        class="max-w-full max-h-full object-contain"
    >

    {{-- Info Panel --}}
    <div class="absolute bottom-4 left-4 right-4 bg-black/50 rounded-lg p-4 text-white">
        <p class="font-medium" x-text="image?.name"></p>
        <p class="text-sm text-white/70">
            <span x-text="image?.dimensions"></span>
            <span class="mx-2">|</span>
            <span x-text="formatFileSize(image?.size)"></span>
        </p>
    </div>
</div>
```

### Image Cropper Component

```blade
{{-- resources/views/components/image-cropper.blade.php --}}
<div
    x-data="imageCropper()"
    x-show="open"
    x-on:crop-image.window="openCropper($event.detail)"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80"
>
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl">
        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Crop Image</h3>
            <button @click="close()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Cropper Area --}}
        <div class="p-6">
            <div class="relative bg-gray-100 rounded-lg overflow-hidden" style="height: 400px;">
                <img
                    x-ref="cropImage"
                    :src="imageUrl"
                    class="max-w-full max-h-full"
                >
            </div>

            {{-- Aspect Ratio Options --}}
            <div class="flex items-center gap-4 mt-4">
                <span class="text-sm font-medium text-gray-700">Aspect Ratio:</span>
                <div class="flex gap-2">
                    <button
                        @click="setAspectRatio(null)"
                        :class="aspectRatio === null ? 'bg-blue-100 text-blue-700' : 'bg-gray-100'"
                        class="px-3 py-1 rounded text-sm"
                    >
                        Free
                    </button>
                    <button
                        @click="setAspectRatio(1)"
                        :class="aspectRatio === 1 ? 'bg-blue-100 text-blue-700' : 'bg-gray-100'"
                        class="px-3 py-1 rounded text-sm"
                    >
                        1:1
                    </button>
                    <button
                        @click="setAspectRatio(16/9)"
                        :class="aspectRatio === 16/9 ? 'bg-blue-100 text-blue-700' : 'bg-gray-100'"
                        class="px-3 py-1 rounded text-sm"
                    >
                        16:9
                    </button>
                    <button
                        @click="setAspectRatio(4/3)"
                        :class="aspectRatio === 4/3 ? 'bg-blue-100 text-blue-700' : 'bg-gray-100'"
                        class="px-3 py-1 rounded text-sm"
                    >
                        4:3
                    </button>
                    <button
                        @click="setAspectRatio(3/2)"
                        :class="aspectRatio === 3/2 ? 'bg-blue-100 text-blue-700' : 'bg-gray-100'"
                        class="px-3 py-1 rounded text-sm"
                    >
                        3:2
                    </button>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200">
            <button
                @click="close()"
                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
            >
                Cancel
            </button>
            <button
                @click="applyCrop()"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
            >
                Apply Crop
            </button>
        </div>
    </div>
</div>
```

### Cropper AlpineJS Component

```javascript
// resources/js/components/image-cropper.js
import Cropper from 'cropperjs';

function imageCropper() {
    return {
        open: false,
        cropper: null,
        imageUrl: null,
        mediaId: null,
        aspectRatio: null,
        callback: null,

        openCropper(data) {
            this.imageUrl = data.url;
            this.mediaId = data.id;
            this.callback = data.callback;
            this.open = true;

            this.$nextTick(() => {
                this.initCropper();
            });
        },

        initCropper() {
            if (this.cropper) {
                this.cropper.destroy();
            }

            this.cropper = new Cropper(this.$refs.cropImage, {
                aspectRatio: this.aspectRatio,
                viewMode: 2,
                autoCropArea: 1,
                responsive: true,
                restore: false,
            });
        },

        setAspectRatio(ratio) {
            this.aspectRatio = ratio;
            if (this.cropper) {
                this.cropper.setAspectRatio(ratio);
            }
        },

        async applyCrop() {
            if (!this.cropper) return;

            const canvas = this.cropper.getCroppedCanvas({
                maxWidth: 2000,
                maxHeight: 2000,
            });

            canvas.toBlob(async (blob) => {
                const formData = new FormData();
                formData.append('file', blob, 'cropped-image.jpg');
                formData.append('media_id', this.mediaId);

                try {
                    const response = await fetch('/api/media/crop', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                    });

                    const data = await response.json();

                    if (this.callback) {
                        this.callback(data.data);
                    }

                    window.dispatchEvent(new CustomEvent('image-cropped', {
                        detail: data.data
                    }));

                    this.close();
                } catch (error) {
                    console.error('Crop failed:', error);
                }
            }, 'image/jpeg', 0.9);
        },

        close() {
            if (this.cropper) {
                this.cropper.destroy();
                this.cropper = null;
            }
            this.open = false;
            this.imageUrl = null;
            this.mediaId = null;
            this.callback = null;
        },
    };
}

window.imageCropper = imageCropper;
```

### Alt Text Editor

```blade
{{-- resources/views/components/alt-text-editor.blade.php --}}
<div
    x-data="altTextEditor()"
    x-show="open"
    x-on:edit-alt-text.window="openEditor($event.detail)"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
>
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Edit Alt Text</h3>
        </div>

        <div class="p-6 space-y-4">
            {{-- Preview --}}
            <div class="flex items-center gap-4">
                <div class="w-20 h-20 bg-gray-100 rounded-lg overflow-hidden">
                    <img :src="image?.thumbnail_url" class="w-full h-full object-cover">
                </div>
                <div>
                    <p class="font-medium text-gray-900" x-text="image?.name"></p>
                    <p class="text-sm text-gray-500" x-text="image?.dimensions"></p>
                </div>
            </div>

            {{-- Alt Text Input --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Alternative Text
                </label>
                <textarea
                    x-model="altText"
                    rows="3"
                    placeholder="Describe this image for accessibility..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                ></textarea>
                <p class="mt-1 text-xs text-gray-500">
                    Alt text helps screen readers describe images to visually impaired users.
                </p>
            </div>

            {{-- AI Generate Button --}}
            <button
                @click="generateAltText()"
                :disabled="generating"
                class="flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700 disabled:opacity-50"
            >
                <svg x-show="!generating" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <svg x-show="generating" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span x-text="generating ? 'Generating...' : 'Generate with AI'"></span>
            </button>
        </div>

        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200">
            <button @click="open = false" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                Cancel
            </button>
            <button @click="save()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Save
            </button>
        </div>
    </div>
</div>
```

---

## File Management

### File Details/Edit Modal

```blade
{{-- resources/views/components/media-edit-modal.blade.php --}}
<div
    x-data="mediaEdit()"
    x-show="open"
    x-on:edit-media-file.window="openEditor($event.detail)"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
>
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">File Details</h3>
            <button @click="open = false" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-2 gap-6">
                {{-- Preview --}}
                <div>
                    <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden">
                        <template x-if="file?.type === 'image'">
                            <img :src="file?.url" class="w-full h-full object-contain">
                        </template>
                        <template x-if="file?.type !== 'image'">
                            <div class="w-full h-full flex items-center justify-center">
                                <svg class="w-20 h-20 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        </template>
                    </div>

                    {{-- Quick Actions --}}
                    <div class="flex items-center gap-2 mt-4">
                        <button
                            @click="copyUrl()"
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm"
                        >
                            Copy URL
                        </button>
                        <template x-if="file?.type === 'image'">
                            <button
                                @click="openCropper()"
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm"
                            >
                                Crop
                            </button>
                        </template>
                        <button
                            @click="downloadFile()"
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm"
                        >
                            Download
                        </button>
                    </div>
                </div>

                {{-- Form --}}
                <div class="space-y-4">
                    {{-- File Name --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            File Name
                        </label>
                        <input
                            type="text"
                            x-model="form.name"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                    </div>

                    {{-- Alt Text (for images) --}}
                    <template x-if="file?.type === 'image'">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Alt Text
                            </label>
                            <textarea
                                x-model="form.alt_text"
                                rows="2"
                                placeholder="Describe this image..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            ></textarea>
                        </div>
                    </template>

                    {{-- Caption --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Caption
                        </label>
                        <input
                            type="text"
                            x-model="form.caption"
                            placeholder="Optional caption..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                    </div>

                    {{-- File Info --}}
                    <div class="bg-gray-50 rounded-lg p-4 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Type:</span>
                            <span class="text-gray-900" x-text="file?.mime_type"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Size:</span>
                            <span class="text-gray-900" x-text="formatFileSize(file?.size)"></span>
                        </div>
                        <template x-if="file?.dimensions">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Dimensions:</span>
                                <span class="text-gray-900" x-text="file?.dimensions"></span>
                            </div>
                        </template>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Uploaded:</span>
                            <span class="text-gray-900" x-text="formatDate(file?.created_at)"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between px-6 py-4 border-t border-gray-200">
            <button
                @click="deleteFile()"
                class="px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg"
            >
                Delete File
            </button>
            <div class="flex items-center gap-3">
                <button
                    @click="open = false"
                    class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                >
                    Cancel
                </button>
                <button
                    @click="save()"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                >
                    Save Changes
                </button>
            </div>
        </div>
    </div>
</div>
```

### Media Edit AlpineJS Component

```javascript
// resources/js/components/media-edit.js
function mediaEdit() {
    return {
        open: false,
        file: null,
        form: {
            name: '',
            alt_text: '',
            caption: '',
        },

        openEditor(file) {
            this.file = file;
            this.form = {
                name: file.name,
                alt_text: file.alt_text || '',
                caption: file.caption || '',
            };
            this.open = true;
        },

        async save() {
            try {
                const response = await fetch(`/api/media/${this.file.id}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(this.form),
                });

                const data = await response.json();

                window.dispatchEvent(new CustomEvent('media-updated', {
                    detail: data.data
                }));

                this.showNotification('File updated successfully', 'success');
                this.open = false;
            } catch (error) {
                this.showNotification('Failed to update file', 'error');
            }
        },

        async deleteFile() {
            if (!confirm('Are you sure you want to delete this file? This action cannot be undone.')) {
                return;
            }

            try {
                await fetch(`/api/media/${this.file.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });

                window.dispatchEvent(new CustomEvent('media-deleted', {
                    detail: { id: this.file.id }
                }));

                this.showNotification('File deleted successfully', 'success');
                this.open = false;
            } catch (error) {
                this.showNotification('Failed to delete file', 'error');
            }
        },

        async copyUrl() {
            try {
                await navigator.clipboard.writeText(this.file.url);
                this.showNotification('URL copied to clipboard', 'success');
            } catch (error) {
                this.showNotification('Failed to copy URL', 'error');
            }
        },

        downloadFile() {
            const link = document.createElement('a');
            link.href = this.file.url;
            link.download = this.file.name;
            link.click();
        },

        openCropper() {
            window.dispatchEvent(new CustomEvent('crop-image', {
                detail: {
                    id: this.file.id,
                    url: this.file.url,
                }
            }));
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
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            });
        },

        showNotification(message, type) {
            window.dispatchEvent(new CustomEvent('notification', {
                detail: { message, type }
            }));
        },
    };
}

window.mediaEdit = mediaEdit;
```

---

## Storage Configuration

### Config File

```php
<?php
// config/media.php

return [
    /*
    |--------------------------------------------------------------------------
    | Storage Disk
    |--------------------------------------------------------------------------
    |
    | The disk where media files will be stored.
    |
    */
    'disk' => env('MEDIA_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Storage Path
    |--------------------------------------------------------------------------
    |
    | The path within the disk where files will be stored.
    |
    */
    'path' => env('MEDIA_PATH', 'media'),

    /*
    |--------------------------------------------------------------------------
    | User Folders
    |--------------------------------------------------------------------------
    |
    | Store files in user-specific folders for isolation.
    |
    */
    'user_folders' => env('MEDIA_USER_FOLDERS', true),

    /*
    |--------------------------------------------------------------------------
    | Maximum File Size
    |--------------------------------------------------------------------------
    |
    | Maximum file size in bytes. Default: 10MB
    |
    */
    'max_file_size' => env('MEDIA_MAX_FILE_SIZE', 10 * 1024 * 1024),

    /*
    |--------------------------------------------------------------------------
    | Allowed File Types
    |--------------------------------------------------------------------------
    |
    | MIME types allowed for upload.
    |
    */
    'allowed_types' => [
        // Images
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/svg+xml',

        // Videos
        'video/mp4',
        'video/webm',
        'video/ogg',

        // Audio
        'audio/mpeg',
        'audio/wav',
        'audio/ogg',

        // Documents
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ],

    /*
    |--------------------------------------------------------------------------
    | Thumbnail Settings
    |--------------------------------------------------------------------------
    |
    | Settings for image thumbnail generation.
    |
    */
    'thumbnails' => [
        'enabled' => true,
        'width' => 300,
        'height' => 300,
        'quality' => 80,
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Optimization
    |--------------------------------------------------------------------------
    |
    | Settings for image optimization.
    |
    */
    'optimization' => [
        'enabled' => true,
        'max_width' => 2000,
        'max_height' => 2000,
        'quality' => 85,
    ],
];
```

### Filesystem Configuration

```php
<?php
// config/filesystems.php - Add S3 disk for production

'disks' => [
    // ... existing disks

    's3-media' => [
        'driver' => 's3',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'bucket' => env('AWS_MEDIA_BUCKET'),
        'url' => env('AWS_URL'),
        'endpoint' => env('AWS_ENDPOINT'),
        'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
        'throw' => false,
        'visibility' => 'public',
    ],
],
```

---

## Integration with Builder

### Builder Image Component

```blade
{{-- resources/views/components/builder/image-element.blade.php --}}
<div
    x-data="imageElement(@js($element))"
    class="builder-element relative group"
>
    {{-- Image Display --}}
    <div class="relative">
        <img
            :src="element.src || '/images/placeholder.png'"
            :alt="element.alt"
            :class="element.classes"
            :style="element.styles"
            class="max-w-full h-auto"
        >

        {{-- Overlay Controls --}}
        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
            <button
                @click="openMediaLibrary()"
                class="px-3 py-2 bg-white rounded-lg text-sm font-medium hover:bg-gray-100"
            >
                Change Image
            </button>
            <button
                @click="editProperties()"
                class="px-3 py-2 bg-white rounded-lg text-sm font-medium hover:bg-gray-100"
            >
                Edit
            </button>
        </div>
    </div>
</div>
```

### Builder Integration Script

```javascript
// resources/js/components/builder/image-element.js
function imageElement(initialElement) {
    return {
        element: initialElement,

        openMediaLibrary() {
            // Set callback for when media is selected
            window.dispatchEvent(new CustomEvent('set-media-callback', {
                detail: (file) => {
                    this.element.src = file.url;
                    this.element.alt = file.alt_text || file.name;
                    this.element.media_id = file.id;

                    // Notify builder of change
                    this.$dispatch('element-updated', this.element);
                }
            }));

            // Open media library
            window.dispatchEvent(new CustomEvent('open-media-library'));
        },

        editProperties() {
            window.dispatchEvent(new CustomEvent('edit-element', {
                detail: this.element
            }));
        },
    };
}

window.imageElement = imageElement;
```

### Usage in Builder Templates

```blade
{{-- Example: Using media in builder templates --}}

{{-- Image Block --}}
<x-builder.block type="image">
    <x-builder.image-element
        :element="[
            'id' => 'img-1',
            'src' => $block['content']['src'] ?? '',
            'alt' => $block['content']['alt'] ?? '',
            'classes' => 'rounded-lg shadow-lg',
        ]"
    />
</x-builder.block>

{{-- Background Image Section --}}
<section
    x-data="{ backgroundUrl: '{{ $section['background'] ?? '' }}' }"
    :style="`background-image: url(${backgroundUrl})`"
    class="bg-cover bg-center py-20"
>
    <button
        @click="$dispatch('open-media-library'); $dispatch('set-media-callback', (file) => backgroundUrl = file.url)"
        class="absolute top-2 right-2 bg-white px-3 py-1 rounded text-sm"
    >
        Change Background
    </button>

    {{-- Section content --}}
</section>
```

---

## MediaController

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Media\StoreMediaRequest;
use App\Http\Requests\Media\UpdateMediaRequest;
use App\Http\Resources\MediaResource;
use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MediaController extends Controller
{
    public function __construct(
        private MediaService $mediaService
    ) {}

    /**
     * Display a listing of media files.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Media::query()
            ->where('user_id', auth()->id());

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('alt_text', 'like', "%{$search}%")
                  ->orWhere('caption', 'like', "%{$search}%");
            });
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // Sort
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        $media = $query->paginate($request->input('per_page', 24));

        return MediaResource::collection($media);
    }

    /**
     * Store a newly uploaded media file.
     */
    public function store(StoreMediaRequest $request): JsonResponse
    {
        $file = $request->file('file');

        $media = $this->mediaService->upload(
            $file,
            auth()->user()
        );

        return response()->json([
            'message' => 'File uploaded successfully',
            'data' => new MediaResource($media),
        ], 201);
    }

    /**
     * Display the specified media file.
     */
    public function show(Media $media): MediaResource
    {
        $this->authorize('view', $media);

        return new MediaResource($media);
    }

    /**
     * Update the specified media file.
     */
    public function update(UpdateMediaRequest $request, Media $media): JsonResponse
    {
        $this->authorize('update', $media);

        $media->update($request->validated());

        return response()->json([
            'message' => 'File updated successfully',
            'data' => new MediaResource($media),
        ]);
    }

    /**
     * Remove the specified media file.
     */
    public function destroy(Media $media): JsonResponse
    {
        $this->authorize('delete', $media);

        $this->mediaService->delete($media);

        return response()->json([
            'message' => 'File deleted successfully',
        ]);
    }

    /**
     * Crop an image.
     */
    public function crop(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|image',
            'media_id' => 'required|exists:media,id',
        ]);

        $originalMedia = Media::findOrFail($request->input('media_id'));
        $this->authorize('update', $originalMedia);

        $media = $this->mediaService->cropImage(
            $request->file('file'),
            $originalMedia,
            auth()->user()
        );

        return response()->json([
            'message' => 'Image cropped successfully',
            'data' => new MediaResource($media),
        ]);
    }

    /**
     * Generate alt text using AI.
     */
    public function generateAltText(Media $media): JsonResponse
    {
        $this->authorize('update', $media);

        $altText = $this->mediaService->generateAltText($media);

        $media->update(['alt_text' => $altText]);

        return response()->json([
            'message' => 'Alt text generated successfully',
            'data' => [
                'alt_text' => $altText,
            ],
        ]);
    }

    /**
     * Bulk delete media files.
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:media,id',
        ]);

        $media = Media::whereIn('id', $request->input('ids'))
            ->where('user_id', auth()->id())
            ->get();

        foreach ($media as $item) {
            $this->mediaService->delete($item);
        }

        return response()->json([
            'message' => count($media) . ' files deleted successfully',
        ]);
    }
}
```

### Form Requests

```php
<?php

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class StoreMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxSize = config('media.max_file_size') / 1024; // Convert to KB
        $allowedTypes = implode(',', config('media.allowed_types'));

        return [
            'file' => [
                'required',
                'file',
                "max:{$maxSize}",
                "mimetypes:{$allowedTypes}",
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.max' => 'File size exceeds the maximum allowed size of ' .
                          $this->formatBytes(config('media.max_file_size')),
            'file.mimetypes' => 'File type is not allowed',
        ];
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes, 1024));
        return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
    }
}
```

```php
<?php

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'alt_text' => 'nullable|string|max:500',
            'caption' => 'nullable|string|max:1000',
        ];
    }
}
```

### Media Resource

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'dimensions' => $this->dimensions,
            'url' => $this->url,
            'thumbnail_url' => $this->thumbnail_url,
            'alt_text' => $this->alt_text,
            'caption' => $this->caption,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
```

---

## MediaService

```php
<?php

namespace App\Services;

use App\Models\Media;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class MediaService
{
    /**
     * Upload a file to storage.
     */
    public function upload(UploadedFile $file, User $user): Media
    {
        $disk = config('media.disk');
        $basePath = config('media.path');

        // Create user-specific path if enabled
        $path = config('media.user_folders')
            ? "{$basePath}/{$user->id}"
            : $basePath;

        // Generate unique filename
        $filename = $this->generateFilename($file);

        // Store original file
        $filePath = $file->storeAs($path, $filename, $disk);

        // Get file info
        $mimeType = $file->getMimeType();
        $size = $file->getSize();
        $type = $this->determineType($mimeType);
        $dimensions = null;
        $thumbnailPath = null;

        // Process images
        if ($type === 'image' && $this->isProcessableImage($mimeType)) {
            // Optimize image
            if (config('media.optimization.enabled')) {
                $this->optimizeImage($disk, $filePath);
            }

            // Get dimensions
            $dimensions = $this->getImageDimensions($disk, $filePath);

            // Generate thumbnail
            if (config('media.thumbnails.enabled')) {
                $thumbnailPath = $this->generateThumbnail($disk, $filePath, $path, $filename);
            }
        }

        // Create media record
        return Media::create([
            'user_id' => $user->id,
            'name' => $file->getClientOriginalName(),
            'filename' => $filename,
            'path' => $filePath,
            'thumbnail_path' => $thumbnailPath,
            'disk' => $disk,
            'mime_type' => $mimeType,
            'type' => $type,
            'size' => $size,
            'dimensions' => $dimensions,
        ]);
    }

    /**
     * Delete a media file.
     */
    public function delete(Media $media): void
    {
        // Delete files from storage
        Storage::disk($media->disk)->delete($media->path);

        if ($media->thumbnail_path) {
            Storage::disk($media->disk)->delete($media->thumbnail_path);
        }

        // Delete database record
        $media->delete();
    }

    /**
     * Crop an image and save as new file.
     */
    public function cropImage(UploadedFile $croppedFile, Media $original, User $user): Media
    {
        return $this->upload($croppedFile, $user);
    }

    /**
     * Generate alt text using AI.
     */
    public function generateAltText(Media $media): string
    {
        // Integration with AI service (e.g., OpenAI Vision)
        // This is a placeholder - implement based on your AI provider

        $imageUrl = $media->url;

        // Example using OpenAI
        // $response = OpenAI::chat()->create([
        //     'model' => 'gpt-4-vision-preview',
        //     'messages' => [
        //         [
        //             'role' => 'user',
        //             'content' => [
        //                 ['type' => 'text', 'text' => 'Generate a concise alt text description for this image.'],
        //                 ['type' => 'image_url', 'image_url' => ['url' => $imageUrl]],
        //             ],
        //         ],
        //     ],
        //     'max_tokens' => 100,
        // ]);

        // return $response->choices[0]->message->content;

        return "Image: {$media->name}";
    }

    /**
     * Generate a unique filename.
     */
    private function generateFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        return Str::uuid() . '.' . $extension;
    }

    /**
     * Determine file type from MIME type.
     */
    private function determineType(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }
        if (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }
        if (str_starts_with($mimeType, 'audio/')) {
            return 'audio';
        }
        return 'document';
    }

    /**
     * Check if image can be processed.
     */
    private function isProcessableImage(string $mimeType): bool
    {
        return in_array($mimeType, [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
        ]);
    }

    /**
     * Optimize image dimensions and quality.
     */
    private function optimizeImage(string $disk, string $path): void
    {
        $maxWidth = config('media.optimization.max_width');
        $maxHeight = config('media.optimization.max_height');
        $quality = config('media.optimization.quality');

        $image = Image::read(Storage::disk($disk)->get($path));

        // Resize if necessary
        if ($image->width() > $maxWidth || $image->height() > $maxHeight) {
            $image->scaleDown($maxWidth, $maxHeight);
        }

        // Encode with quality
        $encoded = $image->toJpeg($quality);

        Storage::disk($disk)->put($path, $encoded);
    }

    /**
     * Get image dimensions.
     */
    private function getImageDimensions(string $disk, string $path): string
    {
        $image = Image::read(Storage::disk($disk)->get($path));
        return $image->width() . 'x' . $image->height();
    }

    /**
     * Generate thumbnail for image.
     */
    private function generateThumbnail(string $disk, string $originalPath, string $basePath, string $filename): string
    {
        $width = config('media.thumbnails.width');
        $height = config('media.thumbnails.height');
        $quality = config('media.thumbnails.quality');

        $image = Image::read(Storage::disk($disk)->get($originalPath));

        // Create thumbnail
        $image->cover($width, $height);
        $encoded = $image->toJpeg($quality);

        // Save thumbnail
        $thumbnailFilename = 'thumb_' . pathinfo($filename, PATHINFO_FILENAME) . '.jpg';
        $thumbnailPath = "{$basePath}/thumbnails/{$thumbnailFilename}";

        Storage::disk($disk)->put($thumbnailPath, $encoded);

        return $thumbnailPath;
    }
}
```

---

## Security

### Media Model with Security

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'filename',
        'path',
        'thumbnail_path',
        'disk',
        'mime_type',
        'type',
        'size',
        'dimensions',
        'alt_text',
        'caption',
    ];

    protected $appends = [
        'url',
        'thumbnail_url',
    ];

    /**
     * Get the user that owns the media.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the full URL to the file.
     */
    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    /**
     * Get the thumbnail URL.
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->thumbnail_path) {
            return $this->type === 'image' ? $this->url : null;
        }

        return Storage::disk($this->disk)->url($this->thumbnail_path);
    }
}
```

### Media Policy

```php
<?php

namespace App\Policies;

use App\Models\Media;
use App\Models\User;

class MediaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Media $media): bool
    {
        return $user->id === $media->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Media $media): bool
    {
        return $user->id === $media->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Media $media): bool
    {
        return $user->id === $media->user_id;
    }
}
```

### Upload Validation Middleware

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateMediaUpload
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');

            // Validate file extension matches content
            if (!$this->validateFileIntegrity($file)) {
                return response()->json([
                    'message' => 'File validation failed. Content does not match extension.',
                ], 422);
            }

            // Check for malicious content in SVG files
            if ($file->getMimeType() === 'image/svg+xml') {
                if ($this->containsMaliciousSvg($file)) {
                    return response()->json([
                        'message' => 'SVG file contains potentially malicious content.',
                    ], 422);
                }
            }
        }

        return $next($request);
    }

    /**
     * Validate that file content matches its extension.
     */
    private function validateFileIntegrity($file): bool
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = $file->getMimeType();

        $validCombinations = [
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
            'webp' => ['image/webp'],
            'svg' => ['image/svg+xml'],
            'mp4' => ['video/mp4'],
            'webm' => ['video/webm'],
            'mp3' => ['audio/mpeg'],
            'wav' => ['audio/wav', 'audio/x-wav'],
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'xls' => ['application/vnd.ms-excel'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        ];

        if (!isset($validCombinations[$extension])) {
            return false;
        }

        return in_array($mimeType, $validCombinations[$extension]);
    }

    /**
     * Check SVG for malicious content.
     */
    private function containsMaliciousSvg($file): bool
    {
        $content = file_get_contents($file->getRealPath());

        // Check for script tags and event handlers
        $dangerousPatterns = [
            '/<script/i',
            '/on\w+\s*=/i',
            '/javascript:/i',
            '/data:/i',
            '/<foreignObject/i',
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }
}
```

### Routes

```php
<?php
// routes/api.php

use App\Http\Controllers\Api\MediaController;

Route::middleware(['auth:sanctum', 'validate.media.upload'])->group(function () {
    Route::get('/media', [MediaController::class, 'index']);
    Route::post('/media/upload', [MediaController::class, 'store']);
    Route::get('/media/{media}', [MediaController::class, 'show']);
    Route::patch('/media/{media}', [MediaController::class, 'update']);
    Route::delete('/media/{media}', [MediaController::class, 'destroy']);
    Route::post('/media/crop', [MediaController::class, 'crop']);
    Route::post('/media/{media}/generate-alt', [MediaController::class, 'generateAltText']);
    Route::post('/media/bulk-delete', [MediaController::class, 'bulkDestroy']);
});
```

### Migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('filename');
            $table->string('path');
            $table->string('thumbnail_path')->nullable();
            $table->string('disk')->default('public');
            $table->string('mime_type');
            $table->string('type'); // image, video, audio, document
            $table->unsignedBigInteger('size');
            $table->string('dimensions')->nullable();
            $table->text('alt_text')->nullable();
            $table->text('caption')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
```

---

## Environment Variables

```env
# Media Storage Configuration
MEDIA_DISK=public
MEDIA_PATH=media
MEDIA_USER_FOLDERS=true
MEDIA_MAX_FILE_SIZE=10485760

# For S3 Storage (Production)
AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=us-east-1
AWS_MEDIA_BUCKET=your-media-bucket
AWS_URL=https://your-cdn.cloudfront.net
```

---

## Usage Examples

### Opening Media Library from Builder

```javascript
// Open media library with callback
function selectImage() {
    window.dispatchEvent(new CustomEvent('set-media-callback', {
        detail: (file) => {
            console.log('Selected file:', file);
            // Use the file URL, alt text, etc.
        }
    }));

    window.dispatchEvent(new CustomEvent('open-media-library'));
}
```

### Listening for Media Events

```javascript
// Listen for media selection
window.addEventListener('media-selected', (e) => {
    const file = e.detail;
    console.log('Media selected:', file.url);
});

// Listen for media updates
window.addEventListener('media-updated', (e) => {
    const file = e.detail;
    console.log('Media updated:', file.name);
});

// Listen for media deletion
window.addEventListener('media-deleted', (e) => {
    const { id } = e.detail;
    console.log('Media deleted:', id);
});
```

### Programmatic Upload

```javascript
// Upload file programmatically
async function uploadFile(file) {
    const formData = new FormData();
    formData.append('file', file);

    const response = await fetch('/api/media/upload', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    });

    return await response.json();
}
```
