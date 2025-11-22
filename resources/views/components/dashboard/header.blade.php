<header class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200 bg-white px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
    <button type="button" class="-m-2.5 p-2.5 text-gray-700 lg:hidden" @click="sidebarOpen = true">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
        </svg>
    </button>

    <div class="h-6 w-px bg-gray-200 lg:hidden"></div>

    <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
        <div class="relative flex flex-1"></div>

        <div class="flex items-center gap-x-4 lg:gap-x-6">
            <div class="hidden lg:block lg:h-6 lg:w-px lg:bg-gray-200"></div>

            <div x-data="{ open: false }" class="relative">
                <button type="button" class="-m-1.5 flex items-center p-1.5" @click="open = !open" @click.outside="open = false">
                    <div class="h-8 w-8 rounded-full bg-indigo-600 flex items-center justify-center">
                        <span class="text-sm font-medium text-white">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </span>
                    </div>
                    <span class="hidden lg:flex lg:items-center">
                        <span class="ml-4 text-sm font-semibold leading-6 text-gray-900">
                            {{ auth()->user()->name }}
                        </span>
                        <svg class="ml-2 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </button>

                <div x-show="open"
                     x-transition
                     class="absolute right-0 z-10 mt-2.5 w-56 origin-top-right rounded-md bg-white py-2 shadow-lg ring-1 ring-gray-900/5"
                     x-cloak>
                    <div class="px-4 py-3 border-b border-gray-100">
                        <p class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</p>
                        <p class="text-sm text-gray-500 truncate">{{ auth()->user()->email }}</p>
                    </div>

                    <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        Profile
                    </a>

                    <div class="border-t border-gray-100 mt-2 pt-2">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex w-full items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                Sign out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
