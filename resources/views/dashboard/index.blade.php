@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('content')
    <div class="md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                Dashboard
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Welcome back! Here's what's happening with your pages.
            </p>
        </div>
        <div class="mt-4 flex md:ml-4 md:mt-0">
            <button type="button"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                    @click="$dispatch('open-create-modal')">
                Create New Page
            </button>
        </div>
    </div>

    <div class="mt-8">
        <dl class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($stats as $stat)
                <div class="relative overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:px-6 sm:py-6">
                    <dt>
                        <div class="absolute rounded-md bg-indigo-500 p-3">
                            <x-icon :name="$stat['icon']" class="h-6 w-6 text-white" />
                        </div>
                        <p class="ml-16 truncate text-sm font-medium text-gray-500">
                            {{ $stat['name'] }}
                        </p>
                    </dt>
                    <dd class="ml-16 flex items-baseline">
                        <p class="text-2xl font-semibold text-gray-900">{{ $stat['value'] }}</p>
                        <p class="ml-2 flex items-baseline text-sm font-semibold {{ $stat['changeType'] === 'increase' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $stat['change'] }}
                        </p>
                    </dd>
                </div>
            @endforeach
        </dl>
    </div>

    <div class="mt-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h3 class="text-lg font-semibold leading-6 text-gray-900">Recent Pages</h3>
                <p class="mt-1 text-sm text-gray-500">Your most recently modified landing pages.</p>
            </div>
            <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                <a href="{{ route('pages.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">
                    View all pages &rarr;
                </a>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($recentPages as $page)
                <div class="group relative overflow-hidden rounded-lg bg-white shadow hover:shadow-md transition-shadow">
                    <div class="aspect-[16/9] bg-gray-100 overflow-hidden">
                        <div class="h-full w-full flex items-center justify-center text-gray-400">
                            Preview
                        </div>
                    </div>

                    <div class="p-4">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-semibold text-gray-900 truncate">{{ $page->title }}</h4>
                            <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium {{ $page->status === 'published' ? 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20' : 'bg-yellow-50 text-yellow-700 ring-1 ring-inset ring-yellow-600/20' }}">
                                {{ $page->status }}
                            </span>
                        </div>

                        <p class="mt-2 text-xs text-gray-400">
                            Modified {{ $page->updated_at->diffForHumans() }}
                        </p>
                    </div>

                    <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all opacity-0 group-hover:opacity-100">
                        <div class="flex gap-2">
                            <a href="{{ route('builder.edit', $page) }}" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm hover:bg-gray-50">
                                Edit
                            </a>
                            @if($page->status === 'published')
                                <a href="{{ route('page.show', $page->slug) }}" target="_blank" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                    View
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center py-12 bg-white rounded-lg shadow">
                    <p class="text-gray-500">No pages yet. Create your first page!</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection

@push('modals')
    @include('components.dashboard.create-page-modal')
@endpush
