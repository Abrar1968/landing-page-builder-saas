@extends('layouts.dashboard')

@section('title', 'Custom Domains')

@section('content')
<div x-data="domainManager()">
    <!-- Header -->
    <div class="md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold text-gray-900 sm:text-3xl">Custom Domains</h2>
            <p class="mt-1 text-sm text-gray-500">Connect custom domains to your landing pages.</p>
        </div>
        <div class="mt-4 flex md:ml-4 md:mt-0">
            <button @click="showAddModal = true"
                    class="inline-flex items-center rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 hover:from-indigo-700 hover:to-purple-700 transition-all">
                <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Add Domain
            </button>
        </div>
    </div>

    <!-- Domains Table -->
    <div class="mt-8 overflow-hidden bg-white shadow-sm ring-1 ring-gray-200 rounded-2xl">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-4 pl-6 pr-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Domain</th>
                    <th class="px-3 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Page</th>
                    <th class="px-3 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                    <th class="px-3 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">SSL</th>
                    <th class="relative py-4 pl-3 pr-6"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($domains as $domain)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="whitespace-nowrap py-4 pl-6 pr-3 text-sm">
                            <div class="flex items-center">
                                <div class="h-10 w-10 flex-shrink-0 rounded-lg bg-gradient-to-br from-purple-100 to-indigo-100 flex items-center justify-center">
                                    <svg class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <div class="font-medium text-gray-900">{{ $domain->domain }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                            {{ $domain->page->title ?? 'N/A' }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                            @if($domain->status === 'verified')
                                <span class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                                    <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    Verified
                                </span>
                            @elseif($domain->status === 'pending')
                                <span class="inline-flex items-center rounded-full bg-yellow-50 px-2.5 py-1 text-xs font-medium text-yellow-700 ring-1 ring-inset ring-yellow-600/20">
                                    <svg class="mr-1 h-3 w-3 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                    Pending
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-red-50 px-2.5 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/20">
                                    <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                    Failed
                                </span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                            @if($domain->ssl_status === 'active')
                                <span class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-1 text-xs font-medium text-green-700">
                                    <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                    </svg>
                                    Active
                                </span>
                            @elseif($domain->ssl_status === 'pending')
                                <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700">
                                    <svg class="mr-1 h-3 w-3 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                    Pending
                                </span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700">
                                    Pending
                                </span>
                            @endif
                        </td>
                        <td class="relative whitespace-nowrap py-4 pl-3 pr-6 text-right text-sm font-medium">
                            @if($domain->status !== 'verified')
                                <button @click="verifyDomain({{ $domain->id }})" class="text-indigo-600 hover:text-indigo-900 font-semibold mr-3">Verify</button>
                            @endif
                            <button @click="showDnsInstructions({{ json_encode($domain) }})" class="text-gray-600 hover:text-gray-900 font-semibold mr-3">DNS</button>
                            <button @click="deleteDomain({{ $domain->id }})" class="text-red-600 hover:text-red-900 font-semibold">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-16 text-center">
                            <div class="w-16 h-16 mx-auto bg-gray-100 rounded-2xl flex items-center justify-center mb-4">
                                <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                </svg>
                            </div>
                            <h3 class="text-sm font-semibold text-gray-900">No custom domains configured</h3>
                            <p class="mt-1 text-sm text-gray-500">Connect your own domain to your landing pages.</p>
                            <button @click="showAddModal = true"
                                    class="mt-4 inline-flex items-center rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 hover:from-indigo-700 hover:to-purple-700 transition-all">
                                Add your first domain
                            </button>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Add Domain Modal -->
    <div x-show="showAddModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape="showAddModal = false">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="showAddModal = false"></div>
            <div x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Add Custom Domain</h3>
                <form @submit.prevent="addDomain">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Domain Name</label>
                        <input type="text" x-model="newDomain.domain" placeholder="example.com"
                               class="w-full rounded-xl border-0 bg-white py-3 px-4 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm" required>
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Page</label>
                        <select x-model="newDomain.page_id"
                                class="w-full rounded-xl border-0 bg-white py-3 px-4 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm" required>
                            <option value="">Select a page</option>
                            @foreach(auth()->user()->pages as $page)
                                <option value="{{ $page->id }}">{{ $page->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="showAddModal = false" class="px-4 py-2.5 text-sm font-medium text-gray-700 hover:text-gray-900 rounded-xl hover:bg-gray-100 transition-colors">Cancel</button>
                        <button type="submit" class="px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-sm font-semibold rounded-xl hover:from-indigo-700 hover:to-purple-700 shadow-lg shadow-indigo-500/25 transition-all">Add Domain</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- DNS Instructions Modal -->
    <div x-show="showDnsModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape="showDnsModal = false">
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" @click="showDnsModal = false"></div>
            <div x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">DNS Configuration</h3>
                <p class="text-sm text-gray-600 mb-6">Add one of the following DNS records to verify your domain:</p>

                <div class="space-y-4">
                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                        <p class="text-sm font-semibold text-gray-900 mb-3">Option 1: CNAME Record</p>
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <span class="text-gray-500 text-xs uppercase tracking-wide">Type</span>
                                <code class="block mt-1 bg-white px-3 py-2 rounded-lg text-gray-900 font-mono text-xs">CNAME</code>
                            </div>
                            <div>
                                <span class="text-gray-500 text-xs uppercase tracking-wide">Name</span>
                                <code class="block mt-1 bg-white px-3 py-2 rounded-lg text-gray-900 font-mono text-xs truncate" x-text="selectedDomain?.domain"></code>
                            </div>
                            <div class="col-span-2">
                                <span class="text-gray-500 text-xs uppercase tracking-wide">Value</span>
                                <code class="block mt-1 bg-white px-3 py-2 rounded-lg text-gray-900 font-mono text-xs">{{ config('app.domain', 'pagebuilder.test') }}</code>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                        <p class="text-sm font-semibold text-gray-900 mb-3">Option 2: TXT Record</p>
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <span class="text-gray-500 text-xs uppercase tracking-wide">Type</span>
                                <code class="block mt-1 bg-white px-3 py-2 rounded-lg text-gray-900 font-mono text-xs">TXT</code>
                            </div>
                            <div>
                                <span class="text-gray-500 text-xs uppercase tracking-wide">Name</span>
                                <code class="block mt-1 bg-white px-3 py-2 rounded-lg text-gray-900 font-mono text-xs">_pagebuilder</code>
                            </div>
                            <div class="col-span-2">
                                <span class="text-gray-500 text-xs uppercase tracking-wide">Value</span>
                                <code class="block mt-1 bg-white px-3 py-2 rounded-lg text-gray-900 font-mono text-xs break-all" x-text="selectedDomain?.id ? 'verify-' + selectedDomain.id : ''"></code>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 p-4 bg-blue-50 rounded-xl border border-blue-100">
                    <div class="flex">
                        <svg class="h-5 w-5 text-blue-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="ml-3 text-sm text-blue-800">
                            <strong>Note:</strong> DNS changes can take up to 24-48 hours to propagate. After adding the records, click "Verify" to check the status.
                        </p>
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <button @click="showDnsModal = false" class="px-4 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-xl hover:bg-gray-200 transition-colors">Close</button>
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
