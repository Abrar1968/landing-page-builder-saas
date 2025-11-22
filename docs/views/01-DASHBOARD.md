# Dashboard View Documentation

## Overview

The dashboard serves as the main control center for users to manage their landing pages, view analytics, and access all platform features.

**Tech Stack: TailwindCSS v4 + AlpineJS + Laravel Blade**

---

## 1. Dashboard Layout

### Main Layout (Blade Template)

```blade
{{-- resources/views/layouts/dashboard.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50" x-data="{ sidebarOpen: false }">
    {{-- Mobile sidebar overlay --}}
    <div
        x-show="sidebarOpen"
        x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 lg:hidden"
        @click="sidebarOpen = false"
    ></div>

    {{-- Sidebar --}}
    @include('components.dashboard.sidebar')

    {{-- Main content area --}}
    <div class="lg:pl-64">
        @include('components.dashboard.header')

        <main class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                @yield('content')
            </div>
        </main>
    </div>

    @stack('modals')
    @stack('scripts')
</body>
</html>
```

---

## 2. Sidebar Navigation

### Sidebar Component

```blade
{{-- resources/views/components/dashboard/sidebar.blade.php --}}
@php
$navigation = [
    ['name' => 'Dashboard', 'href' => route('dashboard'), 'icon' => 'home'],
    ['name' => 'Pages', 'href' => route('dashboard.pages'), 'icon' => 'document-duplicate'],
    ['name' => 'Analytics', 'href' => route('dashboard.analytics'), 'icon' => 'chart-bar'],
    ['name' => 'Settings', 'href' => route('dashboard.settings'), 'icon' => 'cog-6-tooth'],
];

$secondaryNavigation = [
    ['name' => 'Help & Support', 'href' => route('support'), 'icon' => 'question-mark-circle'],
];
@endphp

{{-- Mobile sidebar --}}
<div
    x-show="sidebarOpen"
    x-transition:enter="transition ease-in-out duration-300 transform"
    x-transition:enter-start="-translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transition ease-in-out duration-300 transform"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="-translate-x-full"
    class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-xl lg:hidden"
>
    <div class="absolute right-0 top-0 -mr-12 pt-2">
        <button
            type="button"
            class="ml-1 flex h-10 w-10 items-center justify-center rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white"
            @click="sidebarOpen = false"
        >
            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    @include('components.dashboard.sidebar-content')
</div>

{{-- Desktop sidebar --}}
<div class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-64 lg:flex-col">
    <div class="flex grow flex-col gap-y-5 overflow-y-auto border-r border-gray-200 bg-white">
        @include('components.dashboard.sidebar-content')
    </div>
</div>
```

### Sidebar Content

```blade
{{-- resources/views/components/dashboard/sidebar-content.blade.php --}}
<div class="flex h-full flex-col">
    {{-- Logo --}}
    <div class="flex h-16 shrink-0 items-center px-6">
        <img class="h-8 w-auto" src="{{ asset('logo.svg') }}" alt="{{ config('app.name') }}">
        <span class="ml-2 text-xl font-bold text-gray-900">PageBuilder</span>
    </div>

    {{-- Navigation --}}
    <nav class="flex flex-1 flex-col px-4 pb-4">
        <ul role="list" class="flex flex-1 flex-col gap-y-7">
            {{-- Primary navigation --}}
            <li>
                <ul role="list" class="-mx-2 space-y-1">
                    @foreach($navigation as $item)
                        @php
                            $isActive = request()->routeIs($item['href']) || request()->url() === $item['href'];
                        @endphp
                        <li>
                            <a
                                href="{{ $item['href'] }}"
                                class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 {{ $isActive ? 'bg-indigo-50 text-indigo-600' : 'text-gray-700 hover:bg-gray-50 hover:text-indigo-600' }}"
                            >
                                <x-icon :name="$item['icon']" class="h-6 w-6 shrink-0 {{ $isActive ? 'text-indigo-600' : 'text-gray-400 group-hover:text-indigo-600' }}" />
                                {{ $item['name'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </li>

            {{-- Secondary navigation --}}
            <li class="mt-auto">
                <ul role="list" class="-mx-2 space-y-1">
                    @foreach($secondaryNavigation as $item)
                        <li>
                            <a
                                href="{{ $item['href'] }}"
                                class="group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 text-gray-700 hover:bg-gray-50 hover:text-indigo-600"
                            >
                                <x-icon :name="$item['icon']" class="h-6 w-6 shrink-0 text-gray-400 group-hover:text-indigo-600" />
                                {{ $item['name'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </li>

            {{-- Plan info --}}
            <li class="-mx-2">
                <div class="rounded-lg bg-gray-50 p-4">
                    <p class="text-sm font-medium text-gray-900">{{ auth()->user()->plan_name ?? 'Free Plan' }}</p>
                    <p class="mt-1 text-xs text-gray-500">{{ auth()->user()->pages_count ?? 0 }} of {{ auth()->user()->pages_limit ?? 5 }} pages used</p>
                    <div class="mt-2 h-2 w-full rounded-full bg-gray-200">
                        @php
                            $usage = auth()->user()->pages_limit ? (auth()->user()->pages_count / auth()->user()->pages_limit) * 100 : 0;
                        @endphp
                        <div class="h-2 rounded-full bg-indigo-600" style="width: {{ $usage }}%"></div>
                    </div>
                    <a
                        href="{{ route('dashboard.billing') }}"
                        class="mt-3 block text-center text-sm font-semibold text-indigo-600 hover:text-indigo-500"
                    >
                        Upgrade Plan
                    </a>
                </div>
            </li>
        </ul>
    </nav>
</div>
```

---

## 3. Header with User Dropdown

### Header Component

```blade
{{-- resources/views/components/dashboard/header.blade.php --}}
<header class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200 bg-white px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
    {{-- Mobile menu button --}}
    <button
        type="button"
        class="-m-2.5 p-2.5 text-gray-700 lg:hidden"
        @click="sidebarOpen = true"
    >
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
        </svg>
    </button>

    {{-- Separator --}}
    <div class="h-6 w-px bg-gray-200 lg:hidden"></div>

    <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
        {{-- Search --}}
        <form class="relative flex flex-1" action="{{ route('dashboard.search') }}" method="GET">
            <label for="search-field" class="sr-only">Search</label>
            <svg class="pointer-events-none absolute inset-y-0 left-0 h-full w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
            </svg>
            <input
                id="search-field"
                class="block h-full w-full border-0 py-0 pl-8 pr-0 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm"
                placeholder="Search pages..."
                type="search"
                name="search"
            >
        </form>

        <div class="flex items-center gap-x-4 lg:gap-x-6">
            {{-- Notifications --}}
            <button type="button" class="-m-2.5 p-2.5 text-gray-400 hover:text-gray-500">
                <span class="sr-only">View notifications</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                </svg>
            </button>

            {{-- Separator --}}
            <div class="hidden lg:block lg:h-6 lg:w-px lg:bg-gray-200"></div>

            {{-- User dropdown --}}
            <div x-data="{ open: false }" class="relative">
                <button
                    type="button"
                    class="-m-1.5 flex items-center p-1.5"
                    @click="open = !open"
                    @click.outside="open = false"
                >
                    <span class="sr-only">Open user menu</span>
                    @if(auth()->user()->avatar)
                        <img class="h-8 w-8 rounded-full bg-gray-50" src="{{ auth()->user()->avatar }}" alt="">
                    @else
                        <div class="h-8 w-8 rounded-full bg-indigo-600 flex items-center justify-center">
                            <span class="text-sm font-medium text-white">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </span>
                        </div>
                    @endif
                    <span class="hidden lg:flex lg:items-center">
                        <span class="ml-4 text-sm font-semibold leading-6 text-gray-900">
                            {{ auth()->user()->name }}
                        </span>
                        <svg class="ml-2 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </button>

                {{-- Dropdown menu --}}
                <div
                    x-show="open"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    class="absolute right-0 z-10 mt-2.5 w-56 origin-top-right rounded-md bg-white py-2 shadow-lg ring-1 ring-gray-900/5 focus:outline-none"
                >
                    <div class="px-4 py-3 border-b border-gray-100">
                        <p class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</p>
                        <p class="text-sm text-gray-500 truncate">{{ auth()->user()->email }}</p>
                    </div>

                    <a href="{{ route('dashboard.profile') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        <svg class="mr-3 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Your Profile
                    </a>

                    <a href="{{ route('dashboard.settings') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        <svg class="mr-3 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Settings
                    </a>

                    <div class="border-t border-gray-100 mt-2 pt-2">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex w-full items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                <svg class="mr-3 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                                </svg>
                                Sign out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
```

---

## 4. Dashboard Home

### Stats and Recent Pages

```blade
{{-- resources/views/dashboard/index.blade.php --}}
@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('content')
    {{-- Page header --}}
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
            <button
                type="button"
                class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                @click="$dispatch('open-create-modal')"
            >
                Create New Page
            </button>
        </div>
    </div>

    {{-- Stats --}}
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

    {{-- Recent pages --}}
    <div class="mt-8">
        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h3 class="text-lg font-semibold leading-6 text-gray-900">
                    Recent Pages
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    Your most recently modified landing pages.
                </p>
            </div>
            <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                <a href="{{ route('dashboard.pages') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">
                    View all pages &rarr;
                </a>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($recentPages as $page)
                <div class="group relative overflow-hidden rounded-lg bg-white shadow hover:shadow-md transition-shadow">
                    {{-- Thumbnail --}}
                    <div class="aspect-[16/9] bg-gray-100 overflow-hidden">
                        <img
                            src="{{ $page->thumbnail }}"
                            alt="{{ $page->name }}"
                            class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-200"
                        >
                    </div>

                    {{-- Content --}}
                    <div class="p-4">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-semibold text-gray-900 truncate">
                                {{ $page->name }}
                            </h4>
                            <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium {{ $page->status === 'published' ? 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20' : 'bg-yellow-50 text-yellow-700 ring-1 ring-inset ring-yellow-600/20' }}">
                                {{ $page->status }}
                            </span>
                        </div>

                        <div class="mt-2 flex items-center text-sm text-gray-500">
                            <svg class="mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            {{ number_format($page->views) }} views
                            <span class="mx-2">|</span>
                            {{ $page->conversions }} conversions
                        </div>

                        <p class="mt-2 text-xs text-gray-400">
                            Modified {{ $page->updated_at->diffForHumans() }}
                        </p>
                    </div>

                    {{-- Hover actions --}}
                    <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all opacity-0 group-hover:opacity-100">
                        <div class="flex gap-2">
                            <a href="{{ route('dashboard.pages.edit', $page) }}" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm hover:bg-gray-50">
                                Edit
                            </a>
                            <a href="{{ route('page.show', $page->slug) }}" target="_blank" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                View
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection

@push('modals')
    @include('components.dashboard.create-page-modal')
@endpush
```

---

## 5. Pages List View with Search/Filter

### Full Pages List with Grid/List Toggle

```blade
{{-- resources/views/dashboard/pages/index.blade.php --}}
@extends('layouts.dashboard')

@section('title', 'Pages')

@section('content')
<div x-data="pagesManager()">
    {{-- Page header --}}
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Pages</h1>
            <p class="mt-1 text-sm text-gray-500">
                Manage all your landing pages in one place.
            </p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button
                @click="$dispatch('open-create-modal')"
                class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
            >
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Create Page
            </button>
        </div>
    </div>

    {{-- Filters and search --}}
    <div class="mt-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        {{-- Search --}}
        <div class="relative flex-1 max-w-md">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
            </div>
            <input
                type="text"
                placeholder="Search pages..."
                x-model="searchQuery"
                class="block w-full rounded-md border-0 py-2 pl-10 pr-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
            >
        </div>

        <div class="flex items-center gap-4">
            {{-- Status filter --}}
            <select
                x-model="statusFilter"
                class="rounded-md border-0 py-2 pl-3 pr-8 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm"
            >
                <option value="all">All Status</option>
                <option value="published">Published</option>
                <option value="draft">Draft</option>
            </select>

            {{-- View toggle --}}
            <div class="flex rounded-md shadow-sm">
                <button
                    @click="viewMode = 'grid'"
                    :class="viewMode === 'grid' ? 'bg-indigo-600 text-white ring-indigo-600' : 'bg-white text-gray-900 hover:bg-gray-50'"
                    class="relative inline-flex items-center rounded-l-md px-3 py-2 text-sm font-semibold ring-1 ring-inset ring-gray-300 focus:z-10"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                    </svg>
                </button>
                <button
                    @click="viewMode = 'list'"
                    :class="viewMode === 'list' ? 'bg-indigo-600 text-white ring-indigo-600' : 'bg-white text-gray-900 hover:bg-gray-50'"
                    class="relative -ml-px inline-flex items-center rounded-r-md px-3 py-2 text-sm font-semibold ring-1 ring-inset ring-gray-300 focus:z-10"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Pages grid view --}}
    <div x-show="viewMode === 'grid'" class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <template x-for="page in filteredPages" :key="page.id">
            <div class="group relative overflow-hidden rounded-lg bg-white shadow hover:shadow-md transition-shadow">
                <div class="aspect-[16/9] bg-gray-100 overflow-hidden">
                    <img
                        :src="page.thumbnail"
                        :alt="page.name"
                        class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-200"
                    >
                </div>

                <div class="p-4">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-semibold text-gray-900 truncate" x-text="page.name"></h3>
                            <p class="mt-1 text-xs text-gray-500 truncate" x-text="'/' + page.url"></p>
                        </div>
                        {{-- Actions menu --}}
                        <div x-data="{ menuOpen: false }" class="relative">
                            <button @click="menuOpen = !menuOpen" class="-m-2 flex items-center rounded-full p-2 text-gray-400 hover:text-gray-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 12.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 18.75a.75.75 0 110-1.5.75.75 0 010 1.5z" />
                                </svg>
                            </button>
                            <div
                                x-show="menuOpen"
                                @click.outside="menuOpen = false"
                                x-transition
                                class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 py-1"
                            >
                                <a :href="'/dashboard/pages/' + page.id + '/edit'" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <svg class="mr-3 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                    </svg>
                                    Edit
                                </a>
                                <a :href="'/p/' + page.url" target="_blank" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <svg class="mr-3 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    View Live
                                </a>
                                <button @click="duplicatePage(page.id)" class="flex w-full items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <svg class="mr-3 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75" />
                                    </svg>
                                    Duplicate
                                </button>
                                <div class="border-t border-gray-100"></div>
                                <button @click="deletePage(page.id)" class="flex w-full items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    <svg class="mr-3 h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 flex items-center justify-between">
                        <span
                            :class="page.status === 'published' ? 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20' : 'bg-yellow-50 text-yellow-700 ring-1 ring-inset ring-yellow-600/20'"
                            class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium"
                            x-text="page.status"
                        ></span>
                        <span class="text-xs text-gray-500" x-text="page.views + ' views'"></span>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- Pages list view --}}
    <div x-show="viewMode === 'list'" class="mt-6 overflow-hidden bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Page</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Views</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Conversions</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Last Modified</th>
                    <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                <template x-for="page in filteredPages" :key="page.id">
                    <tr class="hover:bg-gray-50">
                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                            <div class="flex items-center">
                                <div class="h-10 w-16 flex-shrink-0 overflow-hidden rounded bg-gray-100">
                                    <img :src="page.thumbnail" alt="" class="h-full w-full object-cover">
                                </div>
                                <div class="ml-4">
                                    <div class="font-medium text-gray-900" x-text="page.name"></div>
                                    <div class="text-gray-500" x-text="'/' + page.url"></div>
                                </div>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                            <span
                                :class="page.status === 'published' ? 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20' : 'bg-yellow-50 text-yellow-700 ring-1 ring-inset ring-yellow-600/20'"
                                class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium"
                                x-text="page.status"
                            ></span>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500" x-text="page.views"></td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500" x-text="page.conversions"></td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500" x-text="page.lastModified"></td>
                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                            <a :href="'/dashboard/pages/' + page.id + '/edit'" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-6 flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 rounded-lg shadow">
        <div class="flex flex-1 justify-between sm:hidden">
            <button
                @click="currentPage = Math.max(1, currentPage - 1)"
                :disabled="currentPage === 1"
                class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50"
            >
                Previous
            </button>
            <button
                @click="currentPage = Math.min(totalPages, currentPage + 1)"
                :disabled="currentPage === totalPages"
                class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50"
            >
                Next
            </button>
        </div>
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700">
                    Showing
                    <span class="font-medium" x-text="(currentPage - 1) * itemsPerPage + 1"></span>
                    to
                    <span class="font-medium" x-text="Math.min(currentPage * itemsPerPage, filteredPages.length)"></span>
                    of
                    <span class="font-medium" x-text="filteredPages.length"></span>
                    results
                </p>
            </div>
            <div>
                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm">
                    <button
                        @click="currentPage = Math.max(1, currentPage - 1)"
                        :disabled="currentPage === 1"
                        class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 disabled:opacity-50"
                    >
                        Previous
                    </button>
                    <template x-for="pageNum in totalPages" :key="pageNum">
                        <button
                            @click="currentPage = pageNum"
                            :class="pageNum === currentPage ? 'z-10 bg-indigo-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600' : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50'"
                            class="relative inline-flex items-center px-4 py-2 text-sm font-semibold"
                            x-text="pageNum"
                        ></button>
                    </template>
                    <button
                        @click="currentPage = Math.min(totalPages, currentPage + 1)"
                        :disabled="currentPage === totalPages"
                        class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 disabled:opacity-50"
                    >
                        Next
                    </button>
                </nav>
            </div>
        </div>
    </div>
</div>

<script>
function pagesManager() {
    return {
        viewMode: 'grid',
        searchQuery: '',
        statusFilter: 'all',
        currentPage: 1,
        itemsPerPage: 9,
        pages: @json($pages),

        get filteredPages() {
            return this.pages.filter(page => {
                const matchesSearch = page.name.toLowerCase().includes(this.searchQuery.toLowerCase());
                const matchesStatus = this.statusFilter === 'all' || page.status === this.statusFilter;
                return matchesSearch && matchesStatus;
            });
        },

        get totalPages() {
            return Math.ceil(this.filteredPages.length / this.itemsPerPage);
        },

        get paginatedPages() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            return this.filteredPages.slice(start, start + this.itemsPerPage);
        },

        duplicatePage(id) {
            // Handle duplicate via fetch/axios
            fetch(`/dashboard/pages/${id}/duplicate`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            }).then(() => window.location.reload());
        },

        deletePage(id) {
            if (confirm('Are you sure you want to delete this page?')) {
                fetch(`/dashboard/pages/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                }).then(() => window.location.reload());
            }
        }
    }
}
</script>
@endsection

@push('modals')
    @include('components.dashboard.create-page-modal')
@endpush
```

---

## 6. Create Page Modal

### Modal Component with AlpineJS

```blade
{{-- resources/views/components/dashboard/create-page-modal.blade.php --}}
<div
    x-data="createPageModal()"
    x-show="isOpen"
    @open-create-modal.window="isOpen = true"
    @keydown.escape.window="isOpen = false"
    class="relative z-50"
    style="display: none;"
>
    {{-- Backdrop --}}
    <div
        x-show="isOpen"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black bg-opacity-25"
        @click="isOpen = false"
    ></div>

    {{-- Modal --}}
    <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center">
            <div
                x-show="isOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="w-full max-w-2xl transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all"
                @click.outside="isOpen = false"
            >
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold leading-6 text-gray-900">
                        Create New Page
                    </h3>
                    <button
                        type="button"
                        class="rounded-md bg-white text-gray-400 hover:text-gray-500"
                        @click="isOpen = false"
                    >
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="handleCreate" class="mt-6">
                    {{-- Page name input --}}
                    <div>
                        <label for="page-name" class="block text-sm font-medium text-gray-700">
                            Page Name
                        </label>
                        <input
                            type="text"
                            id="page-name"
                            x-model="pageName"
                            placeholder="My Landing Page"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            required
                        >
                    </div>

                    {{-- Template selection --}}
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            Choose a Template
                        </label>
                        <div class="grid grid-cols-2 gap-4">
                            <template x-for="template in templates" :key="template.id">
                                <label
                                    :class="selectedTemplate === template.id ? 'border-indigo-600 bg-indigo-50 ring-2 ring-indigo-600' : 'border-gray-200'"
                                    class="relative flex cursor-pointer rounded-lg border p-4 shadow-sm focus:outline-none"
                                >
                                    <input
                                        type="radio"
                                        name="template"
                                        :value="template.id"
                                        x-model="selectedTemplate"
                                        class="sr-only"
                                    >
                                    <div class="flex w-full items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="text-sm">
                                                <p
                                                    :class="selectedTemplate === template.id ? 'text-indigo-900' : 'text-gray-900'"
                                                    class="font-medium"
                                                    x-text="template.name"
                                                ></p>
                                                <span
                                                    :class="selectedTemplate === template.id ? 'text-indigo-700' : 'text-gray-500'"
                                                    class="inline"
                                                    x-text="template.description"
                                                ></span>
                                            </div>
                                        </div>
                                        <div x-show="selectedTemplate === template.id" class="shrink-0 text-indigo-600">
                                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                    </div>
                                </label>
                            </template>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button
                            type="button"
                            class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            @click="isOpen = false"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="!pageName.trim() || isLoading"
                            class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <template x-if="isLoading">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </template>
                            <span x-text="isLoading ? 'Creating...' : 'Create Page'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function createPageModal() {
    return {
        isOpen: false,
        pageName: '',
        selectedTemplate: 'blank',
        isLoading: false,
        templates: [
            {
                id: 'blank',
                name: 'Blank Page',
                description: 'Start from scratch with a blank canvas',
            },
            {
                id: 'product',
                name: 'Product Launch',
                description: 'Perfect for launching new products',
            },
            {
                id: 'signup',
                name: 'Newsletter Signup',
                description: 'Grow your email list',
            },
            {
                id: 'webinar',
                name: 'Webinar Registration',
                description: 'Capture registrations for events',
            },
        ],

        async handleCreate() {
            if (!this.pageName.trim()) return;

            this.isLoading = true;

            try {
                const response = await fetch('{{ route("dashboard.pages.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        name: this.pageName,
                        template: this.selectedTemplate
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    window.location.href = `/dashboard/pages/${data.id}/edit`;
                } else {
                    alert(data.message || 'Failed to create page');
                }
            } catch (error) {
                console.error('Failed to create page:', error);
                alert('Failed to create page. Please try again.');
            } finally {
                this.isLoading = false;
            }
        }
    }
}
</script>
```

---

## 7. Responsive Design

### Breakpoint Strategy

| Breakpoint | Width | Target Device |
|------------|-------|---------------|
| sm | 640px | Mobile landscape |
| md | 768px | Tablet |
| lg | 1024px | Desktop |
| xl | 1280px | Large desktop |
| 2xl | 1536px | Extra large |

### Key Responsive Patterns (TailwindCSS v4)

```css
/* In resources/css/app.css */

/* Sidebar: Hidden on mobile, fixed on desktop */
.sidebar {
    @apply fixed inset-y-0 left-0 z-50 w-64 transform -translate-x-full transition-transform lg:translate-x-0;
}

/* Main content: Full width on mobile, offset on desktop */
.main-content {
    @apply lg:pl-64;
}

/* Grid: 1 column mobile, 2 tablet, 3 desktop */
.page-grid {
    @apply grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3;
}

/* Header elements: Stack on mobile, inline on desktop */
.header-content {
    @apply flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4;
}

/* Hide on mobile, show on desktop */
.desktop-only {
    @apply hidden lg:block;
}

/* Show on mobile, hide on desktop */
.mobile-only {
    @apply lg:hidden;
}
```

### Mobile Navigation Pattern

- Hamburger menu triggers sidebar overlay via AlpineJS `x-data="{ sidebarOpen: false }"`
- Sidebar slides in from left with backdrop using `x-show` and `x-transition`
- Touch-friendly tap targets (minimum 44px)
- Simplified navigation for small screens

---

## 8. Icon Component

### Blade Icon Component

```blade
{{-- resources/views/components/icon.blade.php --}}
@props(['name', 'class' => 'h-6 w-6'])

@php
$icons = [
    'home' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />',
    'document-duplicate' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75" />',
    'chart-bar' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />',
    'cog-6-tooth' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />',
    'question-mark-circle' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />',
];
@endphp

<svg {{ $attributes->merge(['class' => $class]) }} fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
    {!! $icons[$name] ?? '' !!}
</svg>
```

---

## Dependencies

```json
{
    "devDependencies": {
        "alpinejs": "^3.13.0",
        "tailwindcss": "^4.0.0",
        "vite": "^5.0.0",
        "laravel-vite-plugin": "^1.0.0"
    }
}
```

### AlpineJS Setup

```javascript
// resources/js/app.js
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();
```

---

## File Structure

```
resources/
  views/
    layouts/
      dashboard.blade.php
    components/
      dashboard/
        sidebar.blade.php
        sidebar-content.blade.php
        header.blade.php
        create-page-modal.blade.php
      icon.blade.php
    dashboard/
      index.blade.php          # Dashboard home
      pages/
        index.blade.php        # Pages list view
        edit.blade.php         # Page editor
```

---

## Controller Example

```php
<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            ['name' => 'Total Pages', 'value' => auth()->user()->pages()->count(), 'icon' => 'document-duplicate', 'change' => '+2', 'changeType' => 'increase'],
            ['name' => 'Total Views', 'value' => number_format(auth()->user()->pages()->sum('views')), 'icon' => 'eye', 'change' => '+12%', 'changeType' => 'increase'],
            ['name' => 'Conversions', 'value' => auth()->user()->pages()->sum('conversions'), 'icon' => 'cursor-arrow-rays', 'change' => '+8%', 'changeType' => 'increase'],
            ['name' => 'Conversion Rate', 'value' => '5.1%', 'icon' => 'arrow-trending-up', 'change' => '+0.3%', 'changeType' => 'increase'],
        ];

        $recentPages = auth()->user()->pages()
            ->latest('updated_at')
            ->take(6)
            ->get();

        return view('dashboard.index', compact('stats', 'recentPages'));
    }

    public function pages()
    {
        $pages = auth()->user()->pages()
            ->latest('updated_at')
            ->get()
            ->map(fn($page) => [
                'id' => $page->id,
                'name' => $page->name,
                'url' => $page->slug,
                'status' => $page->status,
                'views' => $page->views,
                'conversions' => $page->conversions,
                'thumbnail' => $page->thumbnail,
                'lastModified' => $page->updated_at->format('M d, Y'),
            ]);

        return view('dashboard.pages.index', compact('pages'));
    }
}
```
