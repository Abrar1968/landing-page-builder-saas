@extends('layouts.dashboard')

@section('title', 'Pricing')

@section('content')
<div class="py-6">
    <div class="mx-auto max-w-7xl">
        <!-- Header -->
        <div class="text-center">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">Choose your plan</h1>
            <p class="mt-4 text-lg text-gray-600">Select the perfect plan to supercharge your landing pages</p>
        </div>

        <!-- Plans Grid -->
        <div class="mt-12 grid gap-8 lg:grid-cols-3">
            @foreach($plans as $key => $plan)
                <div class="relative rounded-2xl {{ $currentPlan === $key ? 'border-2 border-indigo-600 ring-4 ring-indigo-50' : 'border border-gray-200' }} bg-white p-8 shadow-sm hover:shadow-lg transition-all duration-300">
                    @if($currentPlan === $key)
                        <div class="absolute -top-4 left-1/2 -translate-x-1/2">
                            <span class="inline-flex items-center rounded-full bg-gradient-to-r from-indigo-600 to-purple-600 px-4 py-1.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25">
                                Current Plan
                            </span>
                        </div>
                    @endif

                    @if($key === 'pro')
                        <div class="absolute -top-4 right-4">
                            <span class="inline-flex items-center rounded-full bg-gradient-to-r from-amber-500 to-orange-500 px-3 py-1 text-xs font-semibold text-white">
                                Popular
                            </span>
                        </div>
                    @endif

                    <div class="text-center">
                        <h3 class="text-xl font-bold text-gray-900">{{ $plan['name'] }}</h3>
                        <div class="mt-4 flex items-baseline justify-center gap-x-2">
                            <span class="text-5xl font-extrabold tracking-tight text-gray-900">${{ $plan['price'] }}</span>
                            @if($plan['price'] > 0)
                                <span class="text-sm font-medium text-gray-500">/month</span>
                            @else
                                <span class="text-sm font-medium text-gray-500">forever</span>
                            @endif
                        </div>
                        @if($plan['price'] > 0)
                            <p class="mt-1 text-xs text-gray-400">Billed monthly</p>
                        @endif
                    </div>

                    <ul class="mt-8 space-y-4">
                        @foreach($plan['features'] as $feature)
                            <li class="flex gap-x-3">
                                <div class="flex-shrink-0 h-6 w-6 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center">
                                    <svg class="h-3.5 w-3.5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <span class="text-sm text-gray-600">{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-8">
                        @if($currentPlan === $key)
                            <button disabled class="w-full rounded-xl bg-gray-100 px-4 py-3 text-sm font-semibold text-gray-400 cursor-not-allowed">
                                Current Plan
                            </button>
                        @elseif($key === 'free')
                            <a href="{{ route('subscription.manage') }}" class="block w-full rounded-xl bg-gray-100 px-4 py-3 text-center text-sm font-semibold text-gray-700 hover:bg-gray-200 transition-colors">
                                Downgrade
                            </a>
                        @else
                            <form action="{{ route('subscription.checkout') }}" method="POST">
                                @csrf
                                <input type="hidden" name="plan" value="{{ $key }}">
                                <button type="submit" class="w-full rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 hover:from-indigo-700 hover:to-purple-700 transition-all">
                                    {{ $currentPlan === 'free' ? 'Get Started' : 'Upgrade Now' }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- FAQ Section -->
        <div class="mt-16 text-center">
            <h3 class="text-lg font-semibold text-gray-900">Have questions?</h3>
            <p class="mt-2 text-sm text-gray-600">
                Contact our support team at
                <a href="mailto:support@pagecraft.com" class="text-indigo-600 hover:text-indigo-500 font-medium">support@pagecraft.com</a>
            </p>
        </div>
    </div>
</div>
@endsection
