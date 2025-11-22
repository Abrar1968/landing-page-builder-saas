<div
    x-show="showApplyModal"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="apply-modal-title"
    role="dialog"
    aria-modal="true"
>
    <div class="flex items-center justify-center min-h-screen px-4">
        {{-- Backdrop --}}
        <div
            x-show="showApplyModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="showApplyModal = false"
            class="fixed inset-0 bg-black/50 transition-opacity"
        ></div>

        {{-- Modal Panel --}}
        <div
            x-show="showApplyModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative w-full max-w-md p-6 bg-white rounded-lg shadow-xl"
        >
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Create New Page</h3>

            <form @submit.prevent="applyTemplate()">
                <div class="mb-4">
                    <label for="page-name" class="block text-sm font-medium text-gray-700 mb-1">
                        Page Name
                    </label>
                    <input
                        type="text"
                        id="page-name"
                        x-model="newPageName"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="My Landing Page"
                    >
                </div>

                <div class="flex justify-end gap-3">
                    <button
                        type="button"
                        @click="showApplyModal = false"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="applying || !newPageName.trim()"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 disabled:opacity-50"
                    >
                        <span x-show="!applying">Create Page</span>
                        <span x-show="applying" class="inline-flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                            </svg>
                            Creating...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
