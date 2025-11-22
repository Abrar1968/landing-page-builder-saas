@props(['feature' => 'pages', 'show' => false])

<div x-data="{ open: {{ $show ? 'true' : 'false' }} }"
     x-show="open"
     x-cloak
     class="relative z-50">
    <div x-show="open"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div x-show="open"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">

                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                        <h3 class="text-lg font-semibold leading-6 text-gray-900">
                            @if($feature === 'pages')
                                Unlock More Pages
                            @elseif($feature === 'storage')
                                Need More Storage?
                            @elseif($feature === 'custom_domains')
                                Custom Domains
                            @else
                                Upgrade Your Plan
                            @endif
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                @if($feature === 'pages')
                                    You've reached your page limit. Upgrade to Pro for 10 pages or Business for unlimited pages.
                                @elseif($feature === 'storage')
                                    You're running low on storage. Upgrade to Pro for 5GB or Business for 50GB of storage.
                                @elseif($feature === 'custom_domains')
                                    Custom domains are available on Pro and Business plans. Upgrade to connect your own domain.
                                @else
                                    Upgrade your plan to unlock more features and capabilities.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <a href="{{ route('subscription.pricing') }}" class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto">
                        View Plans
                    </a>
                    <button type="button" @click="open = false" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                        Maybe Later
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
