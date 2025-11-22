@forelse($templates as $template)
<div class="bg-white rounded-lg shadow-sm overflow-hidden group hover:shadow-md transition-shadow">
    {{-- Thumbnail --}}
    <div class="relative aspect-[4/3] bg-gray-100 overflow-hidden">
        @if($template->thumbnail)
            <img
                src="{{ $template->thumbnail }}"
                alt="{{ $template->name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
            >
        @else
            <div class="w-full h-full flex items-center justify-center text-gray-400">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                </svg>
            </div>
        @endif

        {{-- Badges --}}
        <div class="absolute top-3 left-3 flex gap-2">
            @if($template->is_premium)
                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-amber-100 text-amber-800">
                    Premium
                </span>
            @endif
            @if($template->is_featured)
                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-indigo-100 text-indigo-800">
                    Featured
                </span>
            @endif
        </div>

        {{-- Hover Actions --}}
        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
            <button
                @click="openPreview({{ $template->id }})"
                class="px-4 py-2 bg-white text-gray-900 rounded-lg text-sm font-medium hover:bg-gray-100 transition-colors"
            >
                Preview
            </button>
            <button
                @click="openApplyModal({{ json_encode(['id' => $template->id, 'name' => $template->name]) }})"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors"
            >
                Use Template
            </button>
        </div>
    </div>

    {{-- Content --}}
    <div class="p-4">
        <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
                <h3 class="font-medium text-gray-900 truncate">{{ $template->name }}</h3>
                <p class="text-sm text-gray-500">{{ $template->category?->name ?? 'Uncategorized' }}</p>
            </div>
            <div class="flex items-center gap-1 text-sm text-gray-500 shrink-0">
                <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                <span>{{ number_format($template->rating, 1) }}</span>
            </div>
        </div>

        {{-- Tags --}}
        @if($template->tags->isNotEmpty())
        <div class="mt-3 flex flex-wrap gap-1">
            @foreach($template->tags->take(3) as $tag)
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-600">
                    {{ $tag->name }}
                </span>
            @endforeach
            @if($template->tags->count() > 3)
                <span class="text-xs text-gray-400">+{{ $template->tags->count() - 3 }}</span>
            @endif
        </div>
        @endif

        {{-- Stats --}}
        <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
            <span>{{ number_format($template->usage_count) }} uses</span>
            <span>{{ $template->created_at->diffForHumans() }}</span>
        </div>
    </div>
</div>
@empty
<div class="col-span-full text-center py-12">
    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <h3 class="mt-2 text-sm font-medium text-gray-900">No templates found</h3>
    <p class="mt-1 text-sm text-gray-500">Try adjusting your search or filter criteria.</p>
</div>
@endforelse
