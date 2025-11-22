<div x-data="{ activeCategory: 'all' }">
    {{-- Category Tabs --}}
    <div class="flex flex-wrap gap-1 mb-4">
        <button @click="activeCategory = 'all'"
                :class="activeCategory === 'all' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600'"
                class="px-2 py-1 text-xs rounded">
            All
        </button>
        @foreach(config('builder.elements') as $category => $elements)
        <button @click="activeCategory = '{{ $category }}'"
                :class="activeCategory === '{{ $category }}' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600'"
                class="px-2 py-1 text-xs rounded capitalize">
            {{ $category }}
        </button>
        @endforeach
    </div>

    {{-- Elements Grid --}}
    <div x-ref="palette" class="grid grid-cols-2 gap-2">
        @foreach(config('builder.elements') as $category => $elements)
            @foreach($elements as $element)
            <div x-show="activeCategory === 'all' || activeCategory === '{{ $category }}'"
                 data-type="{{ $element['type'] }}"
                 draggable="true"
                 @click="addElement('{{ $element['type'] }}')"
                 class="flex flex-col items-center justify-center p-3 bg-gray-50 border border-gray-200 rounded-lg cursor-move hover:bg-gray-100 hover:border-indigo-300 transition-colors">
                <span class="text-lg mb-1">{{ $element['icon'] }}</span>
                <span class="text-xs text-gray-600">{{ $element['name'] }}</span>
            </div>
            @endforeach
        @endforeach
    </div>
</div>
