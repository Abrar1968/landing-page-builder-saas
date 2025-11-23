@extends('layouts.dashboard')

@section('title', 'Manage Subscription')

@section('content')
<div class="py-6">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold text-gray-900">Manage Subscription</h1>

        @if(session('success'))
            <div class="mt-4 rounded-md bg-green-50 p-4">
                <p class="text-sm text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        <div class="mt-6 space-y-6">
            <!-- Current Plan -->
            <div class="rounded-lg border border-gray-200 bg-white p-6">
                <h2 class="text-lg font-semibold text-gray-900">Current Plan</h2>
                <div class="mt-4 flex items-center justify-between">
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ $plans[$subscription?->plan ?? 'free']['name'] ?? 'Free' }}</p>
                        @if($subscription && $subscription->cancel_at_period_end)
                            <p class="mt-1 text-sm text-red-600">
                                Cancels on {{ $subscription->current_period_end->format('M d, Y') }}
                            </p>
                        @elseif($subscription)
                            <p class="mt-1 text-sm text-gray-500">
                                Renews on {{ $subscription->current_period_end->format('M d, Y') }}
                            </p>
                        @endif
                    </div>
                    <div class="flex gap-3">
                        @if($subscription && !$subscription->cancel_at_period_end)
                            <form action="{{ route('subscription.cancel.action') }}" method="POST">
                                @csrf
                                <button type="submit" class="rounded-lg border border-red-300 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50">
                                    Cancel Subscription
                                </button>
                            </form>
                        @elseif($subscription && $subscription->cancel_at_period_end)
                            <form action="{{ route('subscription.resume') }}" method="POST">
                                @csrf
                                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">
                                    Resume Subscription
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('subscription.pricing') }}" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                            Change Plan
                        </a>
                    </div>
                </div>
            </div>

            <!-- Usage -->
            <div class="rounded-lg border border-gray-200 bg-white p-6">
                <h2 class="text-lg font-semibold text-gray-900">Usage</h2>
                <div class="mt-4 space-y-4">
                    <!-- Pages -->
                    <div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Pages</span>
                            <span class="font-medium text-gray-900">
                                {{ $usage['pages']['used'] }} / {{ $usage['pages']['unlimited'] ? '∞' : $usage['pages']['limit'] }}
                            </span>
                        </div>
                        @unless($usage['pages']['unlimited'])
                            <div class="mt-2 h-2 w-full rounded-full bg-gray-200">
                                <div class="h-2 rounded-full bg-indigo-600" style="width: {{ min(($usage['pages']['used'] / $usage['pages']['limit']) * 100, 100) }}%"></div>
                            </div>
                        @endunless
                    </div>

                    <!-- Storage -->
                    <div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Storage</span>
                            <span class="font-medium text-gray-900">
                                {{ $usage['storage']['used_mb'] }} MB / {{ $usage['storage']['limit_mb'] }} MB
                            </span>
                        </div>
                        <div class="mt-2 h-2 w-full rounded-full bg-gray-200">
                            <div class="h-2 rounded-full bg-indigo-600" style="width: {{ min(($usage['storage']['used'] / $usage['storage']['limit']) * 100, 100) }}%"></div>
                        </div>
                    </div>

                    <!-- Custom Domains -->
                    <div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Custom Domains</span>
                            <span class="font-medium text-gray-900">
                                @if($usage['custom_domains']['allowed'])
                                    {{ $usage['custom_domains']['used'] }} / {{ $usage['custom_domains']['limit'] === -1 ? '∞' : $usage['custom_domains']['limit'] }}
                                @else
                                    Not available
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Billing Portal -->
            @if($subscription)
                <div class="rounded-lg border border-gray-200 bg-white p-6">
                    <h2 class="text-lg font-semibold text-gray-900">Billing</h2>
                    <p class="mt-2 text-sm text-gray-600">Manage your payment methods, view invoices, and update billing information.</p>
                    <a href="{{ route('subscription.billing') }}" class="mt-4 inline-flex items-center rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">
                        Open Billing Portal
                        <svg class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                        </svg>
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
