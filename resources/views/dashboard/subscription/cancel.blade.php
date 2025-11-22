@extends('layouts.dashboard')

@section('title', 'Subscription Canceled')

@section('content')
<div class="flex min-h-[60vh] items-center justify-center py-12">
    <div class="text-center">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-yellow-100">
            <svg class="h-8 w-8 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
            </svg>
        </div>
        <h1 class="mt-6 text-3xl font-bold text-gray-900">Checkout Canceled</h1>
        <p class="mt-4 text-lg text-gray-600">Your checkout was canceled. No charges were made.</p>
        <div class="mt-8 flex justify-center gap-4">
            <a href="{{ route('subscription.pricing') }}" class="rounded-lg bg-indigo-600 px-6 py-3 text-sm font-semibold text-white hover:bg-indigo-500">
                View Plans
            </a>
            <a href="{{ route('dashboard') }}" class="rounded-lg bg-gray-100 px-6 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-200">
                Go to Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
