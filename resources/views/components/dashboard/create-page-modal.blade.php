<div x-data="createPageModal()"
     x-show="isOpen"
     @open-create-modal.window="isOpen = true"
     @keydown.escape.window="isOpen = false"
     class="relative z-50"
     x-cloak>

    <div x-show="isOpen"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black bg-opacity-25"
         @click="isOpen = false"></div>

    <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center">
            <div x-show="isOpen"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all"
                 @click.outside="isOpen = false">

                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold leading-6 text-gray-900">Create New Page</h3>
                    <button type="button" class="rounded-md bg-white text-gray-400 hover:text-gray-500" @click="isOpen = false">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="handleCreate" class="mt-6">
                    <div>
                        <label for="page-name" class="block text-sm font-medium text-gray-700">Page Name</label>
                        <input type="text" id="page-name" x-model="pageName" placeholder="My Landing Page"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button"
                                class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                                @click="isOpen = false">
                            Cancel
                        </button>
                        <button type="submit" :disabled="!pageName.trim() || isLoading"
                                class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            <template x-if="isLoading">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </template>
                            <span x-text="isLoading ? 'Creating...' : 'Create Page'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function createPageModal() {
    return {
        isOpen: false,
        pageName: '',
        isLoading: false,

        async handleCreate() {
            if (!this.pageName.trim()) return;

            this.isLoading = true;

            try {
                const response = await fetch('{{ route("pages.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        name: this.pageName
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    window.location.href = `/builder/${data.id}/edit`;
                } else {
                    alert(data.message || 'Failed to create page');
                }
            } catch (error) {
                console.error('Failed to create page:', error);
                alert('Failed to create page. Please try again.');
            } finally {
                this.isLoading = false;
            }
        }
    }
}
</script>
