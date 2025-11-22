@extends('layouts.dashboard')

@section('title', 'Pricing')

@section('content')
<div class="py-6">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900">Choose your plan</h1>
            <p class="mt-4 text-lg text-gray-600">Select the perfect plan for your needs</p>
        </div>

        <div class="mt-12 grid gap-8 lg:grid-cols-3">
            @foreach($plans as $key => $plan)
                <div class="relative rounded-2xl border {{ $currentPlan === $key ? 'border-indigo-600 ring-2 ring-indigo-600' : 'border-gray-200' }} bg-white p-8 shadow-sm">
                    @if($currentPlan === $key)
                        <div class="absolute -top-4 left-1/2 -translate-x-1/2">
                            <span class="inline-flex items-center rounded-full bg-indigo-600 px-4 py-1 text-sm font-semibold text-white">
                                Current Plan
                            </span>
                        </div>
                    @endif

                    <div class="text-center">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $plan['name'] }}</h3>
                        <div class="mt-4 flex items-baseline justify-center gap-x-2">
                            <span class="text-4xl font-bold tracking-tight text-gray-900">${{ $plan['price'] }}</span>
                            @if($plan['price'] > 0)
                                <span class="text-sm text-gray-500">/month</span>
                            @endif
                        </div>
                    </div>

                    <ul class="mt-8 space-y-3">
                        @foreach($plan['features'] as $feature)
                            <li class="flex gap-x-3">
                                <svg class="h-6 w-5 flex-none text-indigo-600" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-sm text-gray-600">{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-8">
                        @if($currentPlan === $key)
                            <button disabled class="w-full rounded-lg bg-gray-100 px-4 py-2.5 text-sm font-semibold text-gray-400">
                                Current Plan
                            </button>
                        @elseif($key === 'free')
                            <a href="{{ route('subscription.manage') }}" class="block w-full rounded-lg bg-gray-100 px-4 py-2.5 text-center text-sm font-semibold text-gray-900 hover:bg-gray-200">
                                Downgrade
                            </a>
                        @else
                            <form action="{{ route('subscription.checkout') }}" method="POST">
                                @csrf
                                <input type="hidden" name="plan" value="{{ $key }}">
                                <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">
                                    {{ $currentPlan === 'free' ? 'Get Started' : 'Upgrade' }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
