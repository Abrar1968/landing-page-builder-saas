@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('content')
    <!-- Header -->
    <div class="md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">
                Welcome back, {{ auth()->user()->name }}!
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Here's what's happening with your pages today.
            </p>
        </div>
        <div class="mt-4 flex md:ml-4 md:mt-0">
            <button type="button"
                    class="inline-flex items-center rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 hover:from-indigo-700 hover:to-purple-700 transition-all"
                    @click="$dispatch('open-create-modal')">
                <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Create New Page
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="mt-8">
        <dl class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($stats as $stat)
                <div class="relative overflow-hidden rounded-2xl bg-white p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-xl bg-gradient-to-br from-indigo-500 to-purple-500 p-3">
                                <x-icon :name="$stat['icon']" class="h-5 w-5 text-white" />
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="text-sm font-medium text-gray-500 truncate">{{ $stat['name'] }}</p>
                            <div class="flex items-baseline">
                                <p class="text-2xl font-bold text-gray-900">{{ $stat['value'] }}</p>
                                @if(isset($stat['change']))
                                <p class="ml-2 text-xs font-semibold {{ $stat['changeType'] === 'increase' ? 'text-green-600' : 'text-red-600' }}">
                                    @if($stat['changeType'] === 'increase')
                                        <svg class="inline h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    @else
                                        <svg class="inline h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    @endif
                                    {{ $stat['change'] }}
                                </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </dl>
    </div>

    <!-- Quick Actions -->
    <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-3">
        <a href="{{ route('templates.index') }}" class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 p-6 text-white shadow-lg hover:shadow-xl transition-all">
            <div class="absolute right-0 top-0 -mt-4 -mr-4 h-32 w-32 rounded-full bg-white/10"></div>
            <svg class="h-8 w-8 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
            </svg>
            <h3 class="text-lg font-semibold">Browse Templates</h3>
            <p class="mt-1 text-sm text-blue-100">Start with a professional design</p>
        </a>

        <a href="{{ route('analytics.index') }}" class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 p-6 text-white shadow-lg hover:shadow-xl transition-all">
            <div class="absolute right-0 top-0 -mt-4 -mr-4 h-32 w-32 rounded-full bg-white/10"></div>
            <svg class="h-8 w-8 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <h3 class="text-lg font-semibold">View Analytics</h3>
            <p class="mt-1 text-sm text-emerald-100">Track your page performance</p>
        </a>

        <a href="{{ route('domains.index') }}" class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-purple-500 to-purple-600 p-6 text-white shadow-lg hover:shadow-xl transition-all">
            <div class="absolute right-0 top-0 -mt-4 -mr-4 h-32 w-32 rounded-full bg-white/10"></div>
            <svg class="h-8 w-8 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
            </svg>
            <h3 class="text-lg font-semibold">Manage Domains</h3>
            <p class="mt-1 text-sm text-purple-100">Connect your custom domains</p>
        </a>
    </div>

    <!-- Recent Pages -->
    <div class="mt-8">
        <div class="sm:flex sm:items-center sm:justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Recent Pages</h3>
                <p class="mt-1 text-sm text-gray-500">Your most recently modified landing pages.</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <a href="{{ route('pages.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500 flex items-center">
                    View all pages
                    <svg class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($recentPages as $page)
                <div class="group relative overflow-hidden rounded-2xl bg-white shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300">
                    <div class="aspect-[16/9] bg-gradient-to-br from-gray-50 to-gray-100 overflow-hidden">
                        <div class="h-full w-full flex items-center justify-center">
                            <div class="text-center">
                                <div class="w-12 h-12 mx-auto bg-gray-200 rounded-xl flex items-center justify-center mb-2">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                                    </svg>
                                </div>
                                <p class="text-xs text-gray-400">Preview</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-4">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-semibold text-gray-900 truncate">{{ $page->title }}</h4>
                            @if($page->status === 'published')
                                <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                                    Published
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-700 ring-1 ring-inset ring-yellow-600/20">
                                    Draft
                                </span>
                            @endif
                        </div>

                        <p class="mt-2 text-xs text-gray-400">
                            Modified {{ $page->updated_at->diffForHumans() }}
                        </p>
                    </div>

                    <!-- Hover Overlay -->
                    <div class="absolute inset-0 flex items-center justify-center bg-gray-900/60 opacity-0 group-hover:opacity-100 transition-all duration-300">
                        <div class="flex gap-2">
                            <a href="{{ route('builder.edit', $page) }}" class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm hover:bg-gray-50 transition-colors">
                                Edit
                            </a>
                            @if($page->status === 'published')
                                <a href="{{ route('page.show', $page->slug) }}" target="_blank" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                                    View
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center py-16 bg-white rounded-2xl shadow-sm border border-gray-100">
                    <div class="w-16 h-16 mx-auto bg-gray-100 rounded-2xl flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900">No pages yet</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating your first landing page.</p>
                    <button type="button"
                            class="mt-4 inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                            @click="$dispatch('open-create-modal')">
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Create Page
                    </button>
                </div>
            @endforelse
        </div>
    </div>
@endsection

@push('modals')
    @include('components.dashboard.create-page-modal')
@endpush
