@extends('layouts.dashboard')

@section('title', 'Form Submissions - ' . $page->title)

@section('content')
<div>
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2">
                    <li><a href="{{ route('pages.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Pages</a></li>
                    <li class="text-gray-400">/</li>
                    <li class="text-sm text-gray-900">{{ $page->title }}</li>
                </ol>
            </nav>
            <h1 class="mt-2 text-2xl font-bold text-gray-900">Form Submissions</h1>
            <p class="mt-1 text-sm text-gray-500">{{ $submissions->total() }} submissions received</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('submissions.export', $page) }}" 
               class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Export CSV
            </a>
        </div>
    </div>

    <div class="mt-8 bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Date</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Data Preview</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                    <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($submissions as $submission)
                    <tr>
                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-900 sm:pl-6">
                            {{ $submission->created_at->format('M d, Y H:i') }}
                        </td>
                        <td class="px-3 py-4 text-sm text-gray-500 max-w-xs truncate">
                            @php
                                $preview = collect($submission->data)->take(2)->map(fn($v, $k) => "$k: $v")->join(', ');
                            @endphp
                            {{ Str::limit($preview, 100) }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                            @if($submission->is_read)
                                <span class="inline-flex items-center rounded-full bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600">
                                    Read
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700">
                                    New
                                </span>
                            @endif
                        </td>
                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                            <button onclick="viewSubmission({{ $submission->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">View</button>
                            <button onclick="deleteSubmission({{ $submission->id }})" class="text-red-600 hover:text-red-900">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-12 text-center text-gray-500">
                            No form submissions yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($submissions->hasPages())
        <div class="mt-4">
            {{ $submissions->links() }}
        </div>
    @endif
</div>

<!-- View Submission Modal -->
<div id="viewModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="fixed inset-0 bg-black bg-opacity-25" onclick="closeModal()"></div>
        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Submission Details</h3>
            <div id="submissionData" class="space-y-3"></div>
            <div class="flex justify-end mt-6">
                <button onclick="closeModal()" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
async function viewSubmission(id) {
    const response = await fetch(`/submissions/${id}`);
    const data = await response.json();
    
    const container = document.getElementById('submissionData');
    container.innerHTML = Object.entries(data.submission.data)
        .map(([key, value]) => `
            <div class="flex justify-between py-2 border-b border-gray-100">
                <span class="text-sm font-medium text-gray-500">${key}</span>
                <span class="text-sm text-gray-900">${value}</span>
            </div>
        `).join('');
    
    document.getElementById('viewModal').classList.remove('hidden');
}

async function deleteSubmission(id) {
    if (!confirm('Are you sure you want to delete this submission?')) return;
    
    await fetch(`/submissions/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    });
    
    window.location.reload();
}

function closeModal() {
    document.getElementById('viewModal').classList.add('hidden');
}
</script>
@endsection
