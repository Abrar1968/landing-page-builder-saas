@extends('layouts.dashboard')

@section('title', 'Subscription Successful')

@section('content')
<div class="flex min-h-[60vh] items-center justify-center py-12">
    <div class="text-center">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
            <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
            </svg>
        </div>
        <h1 class="mt-6 text-3xl font-bold text-gray-900">Subscription Activated!</h1>
        <p class="mt-4 text-lg text-gray-600">Thank you for your subscription. Your account has been upgraded.</p>
        <div class="mt-8 flex justify-center gap-4">
            <a href="{{ route('subscription.manage') }}" class="rounded-lg bg-indigo-600 px-6 py-3 text-sm font-semibold text-white hover:bg-indigo-500">
                View Subscription
            </a>
            <a href="{{ route('dashboard') }}" class="rounded-lg bg-gray-100 px-6 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-200">
                Go to Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
