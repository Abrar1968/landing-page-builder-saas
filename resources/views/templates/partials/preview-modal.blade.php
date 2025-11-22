<div
    x-show="showPreviewModal"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="preview-modal-title"
    role="dialog"
    aria-modal="true"
>
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        {{-- Backdrop --}}
        <div
            x-show="showPreviewModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="showPreviewModal = false"
            class="fixed inset-0 bg-black/60 transition-opacity"
        ></div>

        {{-- Modal Panel --}}
        <div
            x-show="showPreviewModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative inline-block w-full max-w-4xl overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl"
        >
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <div>
                    <h3 x-text="selectedTemplate?.name" class="text-lg font-semibold text-gray-900"></h3>
                    <p class="text-sm text-gray-500">
                        <span x-text="selectedTemplate?.category"></span>
                        <span class="mx-2">·</span>
                        <span x-text="selectedTemplate?.usage_count?.toLocaleString()"></span> uses
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <button
                        @click="openApplyModal(selectedTemplate); showPreviewModal = false"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors"
                    >
                        Use This Template
                    </button>
                    <button
                        @click="showPreviewModal = false"
                        class="p-2 text-gray-400 hover:text-gray-500 rounded-lg hover:bg-gray-100"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Preview Content --}}
            <div class="p-6">
                {{-- Description --}}
                <div class="mb-4">
                    <p x-text="selectedTemplate?.description || 'No description provided.'" class="text-sm text-gray-600"></p>
                </div>

                {{-- Preview Frame --}}
                <div class="bg-gray-100 rounded-lg overflow-hidden" style="height: 400px;">
                    <iframe
                        x-bind:src="selectedTemplate ? `/templates/${selectedTemplate.id}/preview` : ''"
                        class="w-full h-full border-0"
                        title="Template Preview"
                    ></iframe>
                </div>

                {{-- Rating and Tags --}}
                <div class="mt-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="flex">
                            <template x-for="i in 5">
                                <svg
                                    :class="i <= Math.round(selectedTemplate?.rating || 0) ? 'text-amber-400' : 'text-gray-200'"
                                    class="w-5 h-5"
                                    fill="currentColor"
                                    viewBox="0 0 20 20"
                                >
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </template>
                        </div>
                        <span class="text-sm text-gray-600">
                            <span x-text="selectedTemplate?.rating"></span>
                            (<span x-text="selectedTemplate?.rating_count"></span> reviews)
                        </span>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="tag in selectedTemplate?.tags || []">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800" x-text="tag"></span>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
