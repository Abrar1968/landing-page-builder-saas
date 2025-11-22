@props(['type' => 'pages', 'used' => 0, 'limit' => 1, 'threshold' => 80])

@php
    $percentage = $limit > 0 ? ($used / $limit) * 100 : 0;
    $isNearLimit = $percentage >= $threshold;
    $isAtLimit = $used >= $limit;
@endphp

@if($isNearLimit && $limit > 0)
    <div class="rounded-md {{ $isAtLimit ? 'bg-red-50' : 'bg-yellow-50' }} p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                @if($isAtLimit)
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                    </svg>
                @else
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                    </svg>
                @endif
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium {{ $isAtLimit ? 'text-red-800' : 'text-yellow-800' }}">
                    @if($isAtLimit)
                        {{ ucfirst($type) }} limit reached
                    @else
                        Approaching {{ $type }} limit
                    @endif
                </h3>
                <div class="mt-2 text-sm {{ $isAtLimit ? 'text-red-700' : 'text-yellow-700' }}">
                    <p>
                        You're using {{ $used }} of {{ $limit }} {{ $type }}.
                        @if($isAtLimit)
                            <a href="{{ route('subscription.pricing') }}" class="font-medium underline">Upgrade now</a> to continue.
                        @else
                            Consider <a href="{{ route('subscription.pricing') }}" class="font-medium underline">upgrading</a> for more {{ $type }}.
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
@endif
