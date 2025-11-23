<header class="h-14 bg-white border-b border-gray-200 flex items-center justify-between px-4 shrink-0">
    <div class="flex items-center gap-4">
        <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <input type="text"
               x-model="pageSettings.title"
               @change="markDirty()"
               class="text-lg font-semibold bg-transparent border-none focus:ring-0 focus:outline-none w-64"
               placeholder="Page Title">
    </div>

    <div class="flex items-center gap-3">
        {{-- Save Status --}}
        <span x-show="isDirty && !isSaving" x-cloak class="text-sm text-amber-600">Unsaved changes</span>
        <span x-show="isSaving" x-cloak class="text-sm text-blue-600">
            <svg class="animate-spin inline w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
            </svg>
            Saving...
        </span>
        <span x-show="lastSaved && !isDirty && !isSaving" x-cloak class="text-sm text-gray-500" x-text="'Saved ' + lastSaved"></span>

        {{-- Undo/Redo --}}
        <div class="flex items-center border-r border-gray-200 pr-3 mr-1">
            <button @click="undo()" :disabled="historyIndex <= 0" class="p-1.5 text-gray-600 hover:bg-gray-100 rounded disabled:opacity-30" title="Undo (Ctrl+Z)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                </svg>
            </button>
            <button @click="redo()" :disabled="historyIndex >= history.length - 1" class="p-1.5 text-gray-600 hover:bg-gray-100 rounded disabled:opacity-30" title="Redo (Ctrl+Y)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10h-10a8 8 0 00-8 8v2M21 10l-6 6m6-6l-6-6"/>
                </svg>
            </button>
        </div>

        {{-- Responsive Preview --}}
        <div class="flex items-center border-r border-gray-200 pr-3 mr-1">
            <button @click="previewMode = 'desktop'" :class="previewMode === 'desktop' ? 'bg-gray-200' : ''" class="p-1.5 text-gray-600 hover:bg-gray-100 rounded" title="Desktop">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </button>
            <button @click="previewMode = 'tablet'" :class="previewMode === 'tablet' ? 'bg-gray-200' : ''" class="p-1.5 text-gray-600 hover:bg-gray-100 rounded" title="Tablet">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </button>
            <button @click="previewMode = 'mobile'" :class="previewMode === 'mobile' ? 'bg-gray-200' : ''" class="p-1.5 text-gray-600 hover:bg-gray-100 rounded" title="Mobile">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </button>
        </div>

        {{-- Actions --}}
        {{-- More Actions Dropdown --}}
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" class="px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-100 rounded flex items-center gap-1">
                More
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" @click.away="open = false" x-cloak
                 class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg border border-gray-200 py-1 z-50">
                <button @click="showPageSettings = true; open = false" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    Page Settings
                </button>
                <button @click="preview(); open = false" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    Preview Page
                </button>
                <hr class="my-1">
                <button @click="showDeleteConfirm = true; open = false" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                    Delete Page
                </button>
            </div>
        </div>

        <button @click="save()"
                :disabled="isSaving"
                class="px-4 py-1.5 text-sm bg-indigo-600 text-white rounded hover:bg-indigo-700 disabled:opacity-50">
            Save
        </button>
        <button @click="publish()"
                :disabled="isSaving"
                class="px-4 py-1.5 text-sm bg-green-600 text-white rounded hover:bg-green-700 disabled:opacity-50">
            Publish
        </button>
    </div>
</header>
