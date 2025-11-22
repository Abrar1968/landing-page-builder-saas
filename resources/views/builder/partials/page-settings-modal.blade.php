<div x-show="showPageSettings"
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     aria-labelledby="modal-title"
     role="dialog"
     aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4">
        {{-- Backdrop --}}
        <div x-show="showPageSettings"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="showPageSettings = false"
             class="fixed inset-0 bg-black/50 transition-opacity"></div>

        {{-- Modal --}}
        <div x-show="showPageSettings"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative w-full max-w-lg bg-white rounded-lg shadow-xl">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Page Settings</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Page Title</label>
                        <input type="text" x-model="pageSettings.title" @input="markDirty()" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">URL Slug</label>
                        <input type="text" x-model="pageSettings.slug" @input="markDirty()" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Meta Title</label>
                        <input type="text" x-model="pageSettings.metaTitle" @input="markDirty()" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Meta Description</label>
                        <textarea x-model="pageSettings.metaDescription" @input="markDirty()" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button @click="showPageSettings = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        Cancel
                    </button>
                    <button @click="showPageSettings = false; markDirty()" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700">
                        Save Settings
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
