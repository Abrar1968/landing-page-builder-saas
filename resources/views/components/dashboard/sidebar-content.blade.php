@php
$navigation = [
    ['name' => 'Dashboard', 'href' => route('dashboard'), 'icon' => 'home', 'route' => 'dashboard'],
    ['name' => 'Pages', 'href' => route('pages.index'), 'icon' => 'document-duplicate', 'route' => 'pages.*'],
    ['name' => 'Templates', 'href' => route('templates.index'), 'icon' => 'template', 'route' => 'templates.*'],
    ['name' => 'Media', 'href' => route('media.index'), 'icon' => 'photo', 'route' => 'media.*'],
];
@endphp

<div class="flex h-full flex-col">
    <div class="flex h-16 shrink-0 items-center px-6">
        <span class="text-xl font-bold text-gray-900">PageBuilder</span>
    </div>

    <nav class="flex flex-1 flex-col px-4 pb-4">
        <ul role="list" class="flex flex-1 flex-col gap-y-7">
            <li>
                <ul role="list" class="-mx-2 space-y-1">
                    @foreach($navigation as $item)
                        @php
                            $isActive = request()->routeIs($item['route']);
                        @endphp
                        <li>
                            <a href="{{ $item['href'] }}"
                               class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 {{ $isActive ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600' }}">
                                <x-icon :name="$item['icon']" class="h-6 w-6 shrink-0 {{ $isActive ? 'text-indigo-600' : 'text-gray-400 group-hover:text-indigo-600' }}" />
                                {{ $item['name'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </li>

            <li class="mt-auto -mx-2">
                <div class="rounded-lg bg-gray-50 p-4">
                    <p class="text-sm font-medium text-gray-900">Free Plan</p>
                    <p class="mt-1 text-xs text-gray-500">{{ auth()->user()->pages()->count() }} of 5 pages used</p>
                    <div class="mt-2 h-2 w-full rounded-full bg-gray-200">
                        @php
                            $usage = min((auth()->user()->pages()->count() / 5) * 100, 100);
                        @endphp
                        <div class="h-2 rounded-full bg-indigo-600" style="width: {{ $usage }}%"></div>
                    </div>
                </div>
            </li>
        </ul>
    </nav>
</div>
