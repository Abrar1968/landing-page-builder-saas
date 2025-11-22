@php
$navigation = [
    ['name' => 'Dashboard', 'href' => route('dashboard'), 'icon' => 'home', 'route' => 'dashboard'],
    ['name' => 'Pages', 'href' => route('pages.index'), 'icon' => 'document-duplicate', 'route' => 'pages.*'],
    ['name' => 'Templates', 'href' => route('templates.index'), 'icon' => 'template', 'route' => 'templates.*'],
    ['name' => 'Media', 'href' => route('media.index'), 'icon' => 'photo', 'route' => 'media.*'],
    ['name' => 'Analytics', 'href' => route('analytics.index'), 'icon' => 'chart-bar', 'route' => 'analytics.*'],
    ['name' => 'Domains', 'href' => route('domains.index'), 'icon' => 'globe', 'route' => 'domains.*'],
];
@endphp

<div class="flex h-full flex-col">
    <!-- Logo -->
    <div class="flex h-16 shrink-0 items-center px-6 border-b border-gray-100">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
            <div class="w-8 h-8 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                </svg>
            </div>
            <span class="text-lg font-bold text-gray-900">PageCraft</span>
        </a>
    </div>

    <!-- Navigation -->
    <nav class="flex flex-1 flex-col px-4 py-4">
        <ul role="list" class="flex flex-1 flex-col gap-y-7">
            <li>
                <ul role="list" class="space-y-1">
                    @foreach($navigation as $item)
                        @php
                            $isActive = request()->routeIs($item['route']);
                        @endphp
                        <li>
                            <a href="{{ $item['href'] }}"
                               class="group flex gap-x-3 rounded-xl p-3 text-sm font-medium transition-all duration-150
                                      {{ $isActive
                                         ? 'bg-gradient-to-r from-indigo-50 to-purple-50 text-indigo-600 shadow-sm'
                                         : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                <x-icon :name="$item['icon']"
                                    class="h-5 w-5 shrink-0 transition-colors {{ $isActive ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-600' }}" />
                                {{ $item['name'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </li>

            <!-- Subscription Card -->
            <li class="mt-auto">
                <div class="rounded-xl bg-gradient-to-br from-indigo-50 to-purple-50 p-4 border border-indigo-100">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold text-indigo-600 uppercase tracking-wide">Free Plan</span>
                        <a href="{{ route('subscription.pricing') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-700">
                            Upgrade
                        </a>
                    </div>
                    <p class="text-xs text-gray-600 mb-3">
                        {{ auth()->user()->pages()->count() }} of 1 pages used
                    </p>
                    <div class="h-1.5 w-full rounded-full bg-white overflow-hidden">
                        @php
                            $usage = min((auth()->user()->pages()->count() / 1) * 100, 100);
                        @endphp
                        <div class="h-1.5 rounded-full bg-gradient-to-r from-indigo-500 to-purple-500 transition-all duration-300" style="width: {{ $usage }}%"></div>
                    </div>
                </div>
            </li>
        </ul>
    </nav>
</div>
