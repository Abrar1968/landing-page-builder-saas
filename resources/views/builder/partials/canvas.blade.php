<div class="max-w-4xl mx-auto bg-white shadow-lg min-h-full"
     x-ref="canvas"
     @click.self="deselectElement()">

    {{-- Rendered Elements --}}
    <template x-for="(element, index) in elements" :key="element.id">
        <div :class="{'ring-2 ring-indigo-500': selectedElement === element.id}"
             @click.stop="selectElement(element.id)"
             class="relative group">

            {{-- Element Controls --}}
            <div x-show="selectedElement === element.id"
                 x-cloak
                 class="absolute -top-8 left-0 flex items-center gap-1 bg-indigo-500 text-white text-xs rounded px-2 py-1 z-10">
                <span x-text="element.type" class="capitalize"></span>
                <button @click.stop="moveElementUp(index)" :disabled="index === 0" class="hover:bg-indigo-600 p-0.5 rounded disabled:opacity-50">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                </button>
                <button @click.stop="moveElementDown(index)" :disabled="index === elements.length - 1" class="hover:bg-indigo-600 p-0.5 rounded disabled:opacity-50">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <button @click.stop="duplicateElement(element.id)" class="hover:bg-indigo-600 p-0.5 rounded">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                </button>
                <button @click.stop="deleteElement(element.id)" class="hover:bg-red-600 p-0.5 rounded">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>

            {{-- Drag Handle --}}
            <div class="drag-handle absolute left-0 top-0 bottom-0 w-6 bg-gray-100 opacity-0 group-hover:opacity-100 cursor-move flex items-center justify-center">
                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M8 6a2 2 0 1 1-4 0 2 2 0 0 1 4 0zM8 12a2 2 0 1 1-4 0 2 2 0 0 1 4 0zM6 20a2 2 0 1 0 0-4 2 2 0 0 0 0 4zM14 6a2 2 0 1 1-4 0 2 2 0 0 1 4 0zM12 14a2 2 0 1 0 0-4 2 2 0 0 0 0 4zM14 20a2 2 0 1 1-4 0 2 2 0 0 1 4 0z"/></svg>
            </div>

            {{-- Element Content --}}
            <div x-html="renderElement(element)" class="pl-6"></div>
        </div>
    </template>

    {{-- Empty State --}}
    <div x-show="elements.length === 0" class="p-12 text-center text-gray-500">
        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        </svg>
        <p class="text-lg mb-2">Start building your page</p>
        <p class="text-sm">Drag elements from the left panel or click to add</p>
    </div>
</div>
