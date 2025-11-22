@extends('layouts.dashboard')

@section('title', $page->name)

@section('content')
<div>
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2">
                    <li>
                        <a href="{{ route('pages.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700">Pages</a>
                    </li>
                    <li class="flex items-center">
                        <svg class="h-5 w-5 flex-shrink-0 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                        </svg>
                        <span class="ml-2 text-sm font-medium text-gray-900">{{ $page->name }}</span>
                    </li>
                </ol>
            </nav>
            <h1 class="mt-2 text-2xl font-bold text-gray-900">{{ $page->name }}</h1>
        </div>
        <div class="mt-4 sm:mt-0 flex items-center gap-3">
            <a href="{{ route('builder.edit', $page) }}"
               class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                </svg>
                Edit Page
            </a>
            <a href="{{ route('builder.preview', $page) }}"
               class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                Preview
            </a>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Page Details</h3>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Page Name</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $page->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">URL Slug</dt>
                            <dd class="mt-1 text-sm text-gray-900">/{{ $page->slug }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                @if($page->status === 'published')
                                    <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                                        Published
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-700 ring-1 ring-inset ring-yellow-600/20">
                                        Draft
                                    </span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $page->created_at->format('M d, Y g:i A') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Last Modified</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $page->updated_at->format('M d, Y g:i A') }}</dd>
                        </div>
                        @if($page->published_at)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Published</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $page->published_at->format('M d, Y g:i A') }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            @if($page->meta_title || $page->meta_description)
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">SEO Settings</h3>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    <dl class="space-y-4">
                        @if($page->meta_title)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Meta Title</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $page->meta_title }}</dd>
                        </div>
                        @endif
                        @if($page->meta_description)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Meta Description</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $page->meta_description }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
                </div>
                <div class="px-4 py-5 sm:p-6 space-y-3">
                    <a href="{{ route('builder.edit', $page) }}"
                       class="flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700">
                        Edit in Builder
                    </a>
                    <a href="{{ route('pages.versions', $page) }}"
                       class="flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        View History
                    </a>
                    <a href="{{ route('analytics.show', $page) }}"
                       class="flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        View Analytics
                    </a>
                    <a href="{{ route('submissions.index', $page) }}"
                       class="flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        Form Submissions
                    </a>
                    @if($page->status === 'published')
                    <a href="{{ route('page.show', $page->slug) }}" target="_blank"
                       class="flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                        View Live Page
                        <svg class="ml-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                        </svg>
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
