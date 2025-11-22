@extends('layouts.dashboard')

@section('title', 'Custom Domains')

@section('content')
<div x-data="domainManager()">
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Custom Domains</h1>
            <p class="mt-1 text-sm text-gray-500">Connect custom domains to your landing pages.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button @click="showAddModal = true"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Add Domain
            </button>
        </div>
    </div>

    <div class="mt-8 bg-white shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Domain</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Page</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">SSL</th>
                    <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($domains as $domain)
                    <tr>
                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                            {{ $domain->domain }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                            {{ $domain->page->title ?? 'N/A' }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                            @if($domain->status === 'verified')
                                <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                                    Verified
                                </span>
                            @elseif($domain->status === 'pending')
                                <span class="inline-flex items-center rounded-full bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-700 ring-1 ring-inset ring-yellow-600/20">
                                    Pending
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/20">
                                    Failed
                                </span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                            @if($domain->ssl_status === 'active')
                                <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                                    Active
                                </span>
                            @elseif($domain->ssl_status === 'provisioning')
                                <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700">
                                    Provisioning
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-gray-50 px-2 py-1 text-xs font-medium text-gray-700">
                                    Pending
                                </span>
                            @endif
                        </td>
                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                            @if($domain->status !== 'verified')
                                <button @click="verifyDomain({{ $domain->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">Verify</button>
                            @endif
                            <button @click="showDnsInstructions({{ json_encode($domain) }})" class="text-gray-600 hover:text-gray-900 mr-3">DNS</button>
                            <button @click="deleteDomain({{ $domain->id }})" class="text-red-600 hover:text-red-900">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-gray-500">
                            No custom domains configured yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Add Domain Modal -->
    <div x-show="showAddModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape="showAddModal = false">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-black bg-opacity-25" @click="showAddModal = false"></div>
            <div class="relative bg-white rounded-lg shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Add Custom Domain</h3>
                <form @submit.prevent="addDomain">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Domain Name</label>
                        <input type="text" x-model="newDomain.domain" placeholder="example.com"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Select Page</label>
                        <select x-model="newDomain.page_id"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                            <option value="">Select a page</option>
                            @foreach(auth()->user()->pages as $page)
                                <option value="{{ $page->id }}">{{ $page->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="showAddModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">Add Domain</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- DNS Instructions Modal -->
    <div x-show="showDnsModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape="showDnsModal = false">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-black bg-opacity-25" @click="showDnsModal = false"></div>
            <div class="relative bg-white rounded-lg shadow-xl w-full max-w-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">DNS Configuration</h3>
                <p class="text-sm text-gray-600 mb-4">Add one of the following DNS records to verify your domain:</p>

                <div class="space-y-4">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm font-medium text-gray-700 mb-2">Option 1: CNAME Record</p>
                        <div class="grid grid-cols-2 gap-2 text-sm">
                            <div>
                                <span class="text-gray-500">Type:</span>
                                <code class="ml-2 bg-white px-2 py-1 rounded">CNAME</code>
                            </div>
                            <div>
                                <span class="text-gray-500">Name:</span>
                                <code class="ml-2 bg-white px-2 py-1 rounded" x-text="selectedDomain?.domain"></code>
                            </div>
                            <div class="col-span-2">
                                <span class="text-gray-500">Value:</span>
                                <code class="ml-2 bg-white px-2 py-1 rounded">{{ config('app.domain', 'pagebuilder.test') }}</code>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm font-medium text-gray-700 mb-2">Option 2: TXT Record</p>
                        <div class="grid grid-cols-2 gap-2 text-sm">
                            <div>
                                <span class="text-gray-500">Type:</span>
                                <code class="ml-2 bg-white px-2 py-1 rounded">TXT</code>
                            </div>
                            <div>
                                <span class="text-gray-500">Name:</span>
                                <code class="ml-2 bg-white px-2 py-1 rounded">_pagebuilder</code>
                            </div>
                            <div class="col-span-2">
                                <span class="text-gray-500">Value:</span>
                                <code class="ml-2 bg-white px-2 py-1 rounded text-xs break-all" x-text="selectedDomain?.verification_token"></code>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                    <p class="text-sm text-blue-800">
                        <strong>Note:</strong> DNS changes can take up to 24-48 hours to propagate. After adding the records, click "Verify" to check the status.
                    </p>
                </div>

                <div class="flex justify-end mt-6">
                    <button @click="showDnsModal = false" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function domainManager() {
    return {
        showAddModal: false,
        showDnsModal: false,
        selectedDomain: null,
        newDomain: { domain: '', page_id: '' },

        async addDomain() {
            try {
                const response = await fetch('/domains', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.newDomain)
                });
                const data = await response.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to add domain');
                }
            } catch (error) {
                alert('Failed to add domain');
            }
        },

        async verifyDomain(id) {
            try {
                const response = await fetch(`/domains/${id}/verify`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const data = await response.json();
                alert(data.message);
                if (data.success) window.location.reload();
            } catch (error) {
                alert('Verification failed');
            }
        },

        async deleteDomain(id) {
            if (!confirm('Are you sure you want to delete this domain?')) return;
            try {
                await fetch(`/domains/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                window.location.reload();
            } catch (error) {
                alert('Failed to delete domain');
            }
        },

        showDnsInstructions(domain) {
            this.selectedDomain = domain;
            this.showDnsModal = true;
        }
    }
}
</script>
@endsection
