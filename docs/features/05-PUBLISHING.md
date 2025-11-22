# Publishing Feature

Complete documentation for the landing page publishing system including domain management, SSL, and version control.

## Table of Contents

1. [Publish Flow](#publish-flow)
2. [Page URLs](#page-urls)
3. [Custom Domain Management](#custom-domain-management)
4. [Domain Verification](#domain-verification)
5. [SSL Management](#ssl-management)
6. [Published Page Rendering](#published-page-rendering)
7. [Unpublish Flow](#unpublish-flow)
8. [Version Control](#version-control)
9. [Controllers](#controllers)
10. [Services](#services)

---

## Publish Flow

### Database Schema

```php
// database/migrations/xxxx_xx_xx_create_pages_table.php
Schema::create('pages', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('project_id')->constrained()->onDelete('cascade');
    $table->string('title');
    $table->string('slug')->unique();
    $table->json('content')->nullable(); // Draft content
    $table->json('published_content')->nullable(); // Published content
    $table->enum('status', ['draft', 'published', 'unpublished'])->default('draft');
    $table->timestamp('published_at')->nullable();
    $table->string('subdomain')->unique()->nullable();
    $table->json('seo_settings')->nullable();
    $table->json('analytics_settings')->nullable();
    $table->timestamps();
});

// database/migrations/xxxx_xx_xx_create_custom_domains_table.php
Schema::create('custom_domains', function (Blueprint $table) {
    $table->id();
    $table->foreignId('page_id')->constrained()->onDelete('cascade');
    $table->string('domain')->unique();
    $table->enum('status', ['pending', 'verified', 'failed'])->default('pending');
    $table->enum('ssl_status', ['pending', 'provisioning', 'active', 'failed'])->default('pending');
    $table->string('verification_token')->nullable();
    $table->timestamp('verified_at')->nullable();
    $table->timestamp('ssl_provisioned_at')->nullable();
    $table->timestamps();
});

// database/migrations/xxxx_xx_xx_create_page_versions_table.php
Schema::create('page_versions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('page_id')->constrained()->onDelete('cascade');
    $table->json('content');
    $table->integer('version_number');
    $table->string('change_description')->nullable();
    $table->foreignId('created_by')->constrained('users');
    $table->timestamps();
});
```

### Publish Button Component

```blade
{{-- resources/views/components/publish-button.blade.php --}}
<div x-data="publishFlow(@js($page))" class="relative">
    {{-- Main Publish Button --}}
    <button
        @click="openPublishModal"
        class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium"
        :class="{ 'opacity-50 cursor-not-allowed': isPublishing }"
        :disabled="isPublishing"
    >
        <template x-if="!isPublishing">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
        </template>
        <template x-if="isPublishing">
            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
        </template>
        <span x-text="isPublishing ? 'Publishing...' : (page.status === 'published' ? 'Update' : 'Publish')"></span>
    </button>

    {{-- Publish Modal --}}
    <template x-teleport="body">
        <div
            x-show="showPublishModal"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            @click.self="closePublishModal"
            @keydown.escape.window="closePublishModal"
        >
            <div
                x-show="showPublishModal"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="bg-white rounded-xl shadow-2xl w-full max-w-lg"
            >
                {{-- Modal Header --}}
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <span x-text="page.status === 'published' ? 'Update Published Page' : 'Publish Page'"></span>
                    </h3>
                    <p class="text-sm text-gray-500 mt-1">Review the checklist before publishing</p>
                </div>

                {{-- Publish Checklist --}}
                <div class="px-6 py-4 space-y-3">
                    <template x-for="item in checklist" :key="item.id">
                        <div
                            class="flex items-center gap-3 p-3 rounded-lg"
                            :class="item.passed ? 'bg-green-50' : 'bg-red-50'"
                        >
                            <div
                                class="flex-shrink-0 w-5 h-5 rounded-full flex items-center justify-center"
                                :class="item.passed ? 'bg-green-500' : 'bg-red-500'"
                            >
                                <template x-if="item.passed">
                                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </template>
                                <template x-if="!item.passed">
                                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                </template>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium" :class="item.passed ? 'text-green-800' : 'text-red-800'" x-text="item.label"></p>
                                <p x-show="!item.passed && item.hint" class="text-xs mt-0.5" :class="item.passed ? 'text-green-600' : 'text-red-600'" x-text="item.hint"></p>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- URL Preview --}}
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Page URL</label>
                    <div class="flex items-center gap-2">
                        <code class="flex-1 px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm text-gray-600 truncate" x-text="getPublishUrl()"></code>
                        <button @click="copyUrl" class="p-2 text-gray-500 hover:text-gray-700">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-end gap-3">
                    <button
                        @click="closePublishModal"
                        class="px-4 py-2 text-gray-700 hover:text-gray-900 font-medium"
                    >
                        Cancel
                    </button>
                    <button
                        @click="confirmPublish"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="!allChecksPassed || isPublishing"
                    >
                        <span x-text="page.status === 'published' ? 'Update Page' : 'Publish Now'"></span>
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
```

### Publish Flow Alpine Component

```javascript
// resources/js/components/publish-flow.js
document.addEventListener('alpine:init', () => {
    Alpine.data('publishFlow', (page) => ({
        page: page,
        showPublishModal: false,
        isPublishing: false,
        checklist: [],

        init() {
            this.runChecklist();
        },

        get allChecksPassed() {
            return this.checklist.every(item => item.passed || !item.required);
        },

        async runChecklist() {
            this.checklist = [
                {
                    id: 'title',
                    label: 'Page title is set',
                    passed: this.page.title && this.page.title.trim().length > 0,
                    hint: 'Add a title in page settings',
                    required: true
                },
                {
                    id: 'content',
                    label: 'Page has content',
                    passed: this.page.content && Object.keys(this.page.content).length > 0,
                    hint: 'Add at least one section to your page',
                    required: true
                },
                {
                    id: 'slug',
                    label: 'URL slug is set',
                    passed: this.page.slug && this.page.slug.trim().length > 0,
                    hint: 'Set a URL slug in page settings',
                    required: true
                },
                {
                    id: 'seo',
                    label: 'SEO meta tags configured',
                    passed: this.page.seo_settings?.meta_title && this.page.seo_settings?.meta_description,
                    hint: 'Add meta title and description for better SEO',
                    required: false
                },
                {
                    id: 'favicon',
                    label: 'Favicon uploaded',
                    passed: this.page.seo_settings?.favicon,
                    hint: 'Upload a favicon for brand recognition',
                    required: false
                }
            ];
        },

        openPublishModal() {
            this.runChecklist();
            this.showPublishModal = true;
        },

        closePublishModal() {
            this.showPublishModal = false;
        },

        getPublishUrl() {
            if (this.page.custom_domain?.status === 'verified') {
                return `https://${this.page.custom_domain.domain}`;
            }
            if (this.page.subdomain) {
                return `https://${this.page.subdomain}.${window.appDomain}`;
            }
            return `https://${window.appDomain}/p/${this.page.slug}`;
        },

        async copyUrl() {
            await navigator.clipboard.writeText(this.getPublishUrl());
            this.$dispatch('notify', { message: 'URL copied to clipboard', type: 'success' });
        },

        async confirmPublish() {
            if (!this.allChecksPassed) return;

            this.isPublishing = true;

            try {
                const response = await fetch(`/api/pages/${this.page.id}/publish`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    this.page.status = 'published';
                    this.page.published_at = data.published_at;
                    this.$dispatch('notify', { message: 'Page published successfully!', type: 'success' });
                    this.$dispatch('page-published', { page: this.page });
                    this.closePublishModal();
                } else {
                    throw new Error(data.message || 'Failed to publish page');
                }
            } catch (error) {
                this.$dispatch('notify', { message: error.message, type: 'error' });
            } finally {
                this.isPublishing = false;
            }
        }
    }));
});
```

---

## Page URLs

### Subdomain System

```php
// app/Services/SubdomainService.php
<?php

namespace App\Services;

use App\Models\Page;
use Illuminate\Support\Str;

class SubdomainService
{
    protected array $reservedSubdomains = [
        'www', 'app', 'api', 'admin', 'mail', 'smtp', 'ftp', 'ssh',
        'blog', 'help', 'support', 'status', 'cdn', 'assets', 'static'
    ];

    public function generate(string $title): string
    {
        $base = Str::slug($title);
        $subdomain = $base;
        $counter = 1;

        while ($this->isReserved($subdomain) || $this->exists($subdomain)) {
            $subdomain = $base . '-' . $counter;
            $counter++;
        }

        return $subdomain;
    }

    public function isAvailable(string $subdomain): bool
    {
        return !$this->isReserved($subdomain) && !$this->exists($subdomain);
    }

    public function isReserved(string $subdomain): bool
    {
        return in_array(strtolower($subdomain), $this->reservedSubdomains);
    }

    public function exists(string $subdomain): bool
    {
        return Page::where('subdomain', strtolower($subdomain))->exists();
    }

    public function validate(string $subdomain): array
    {
        $errors = [];

        if (strlen($subdomain) < 3) {
            $errors[] = 'Subdomain must be at least 3 characters';
        }

        if (strlen($subdomain) > 63) {
            $errors[] = 'Subdomain must be less than 63 characters';
        }

        if (!preg_match('/^[a-z0-9]([a-z0-9-]*[a-z0-9])?$/', $subdomain)) {
            $errors[] = 'Subdomain can only contain lowercase letters, numbers, and hyphens';
        }

        if ($this->isReserved($subdomain)) {
            $errors[] = 'This subdomain is reserved';
        }

        if ($this->exists($subdomain)) {
            $errors[] = 'This subdomain is already taken';
        }

        return $errors;
    }
}
```

### Custom Slug Component

```blade
{{-- resources/views/components/page-url-settings.blade.php --}}
<div x-data="pageUrlSettings(@js($page))" class="space-y-6">
    {{-- Subdomain Setting --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Subdomain</label>
        <div class="flex items-center gap-2">
            <div class="relative flex-1">
                <input
                    type="text"
                    x-model="subdomain"
                    @input.debounce.500ms="checkSubdomainAvailability"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :class="{ 'border-red-500': subdomainError, 'border-green-500': subdomainAvailable }"
                    placeholder="my-landing-page"
                >
                <div x-show="checkingSubdomain" class="absolute right-3 top-1/2 -translate-y-1/2">
                    <svg class="w-4 h-4 animate-spin text-gray-400" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </div>
            </div>
            <span class="text-gray-500">.{{ config('app.domain') }}</span>
        </div>
        <p x-show="subdomainError" class="mt-1 text-sm text-red-600" x-text="subdomainError"></p>
        <p x-show="subdomainAvailable && !subdomainError" class="mt-1 text-sm text-green-600">Subdomain is available</p>
    </div>

    {{-- Custom Slug Setting --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Page Slug</label>
        <div class="flex items-center gap-2">
            <span class="text-gray-500">{{ config('app.url') }}/p/</span>
            <input
                type="text"
                x-model="slug"
                @input.debounce.500ms="checkSlugAvailability"
                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-500': slugError, 'border-green-500': slugAvailable }"
                placeholder="my-page"
            >
        </div>
        <p x-show="slugError" class="mt-1 text-sm text-red-600" x-text="slugError"></p>
        <p x-show="slugAvailable && !slugError" class="mt-1 text-sm text-green-600">Slug is available</p>
    </div>

    {{-- URL Preview --}}
    <div class="p-4 bg-gray-50 rounded-lg">
        <h4 class="text-sm font-medium text-gray-700 mb-2">Your page will be available at:</h4>
        <div class="space-y-2">
            <div class="flex items-center gap-2">
                <span class="text-xs font-medium text-gray-500 w-20">Subdomain:</span>
                <code class="text-sm text-blue-600" x-text="`https://${subdomain}.{{ config('app.domain') }}`"></code>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-medium text-gray-500 w-20">Direct URL:</span>
                <code class="text-sm text-blue-600" x-text="`{{ config('app.url') }}/p/${slug}`"></code>
            </div>
        </div>
    </div>

    {{-- Save Button --}}
    <button
        @click="saveUrlSettings"
        class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium disabled:opacity-50"
        :disabled="saving || subdomainError || slugError"
    >
        <span x-text="saving ? 'Saving...' : 'Save URL Settings'"></span>
    </button>
</div>
```

```javascript
// resources/js/components/page-url-settings.js
document.addEventListener('alpine:init', () => {
    Alpine.data('pageUrlSettings', (page) => ({
        subdomain: page.subdomain || '',
        slug: page.slug || '',
        subdomainError: null,
        slugError: null,
        subdomainAvailable: false,
        slugAvailable: false,
        checkingSubdomain: false,
        checkingSlug: false,
        saving: false,

        async checkSubdomainAvailability() {
            if (!this.subdomain) {
                this.subdomainError = null;
                this.subdomainAvailable = false;
                return;
            }

            this.checkingSubdomain = true;
            this.subdomainError = null;

            try {
                const response = await fetch(`/api/subdomains/check?subdomain=${encodeURIComponent(this.subdomain)}&page_id=${page.id}`);
                const data = await response.json();

                if (data.available) {
                    this.subdomainAvailable = true;
                } else {
                    this.subdomainError = data.errors?.join(', ') || 'Subdomain not available';
                    this.subdomainAvailable = false;
                }
            } catch (error) {
                this.subdomainError = 'Error checking availability';
            } finally {
                this.checkingSubdomain = false;
            }
        },

        async checkSlugAvailability() {
            if (!this.slug) {
                this.slugError = null;
                this.slugAvailable = false;
                return;
            }

            this.checkingSlug = true;
            this.slugError = null;

            try {
                const response = await fetch(`/api/slugs/check?slug=${encodeURIComponent(this.slug)}&page_id=${page.id}`);
                const data = await response.json();

                if (data.available) {
                    this.slugAvailable = true;
                } else {
                    this.slugError = data.message || 'Slug not available';
                    this.slugAvailable = false;
                }
            } catch (error) {
                this.slugError = 'Error checking availability';
            } finally {
                this.checkingSlug = false;
            }
        },

        async saveUrlSettings() {
            this.saving = true;

            try {
                const response = await fetch(`/api/pages/${page.id}/url-settings`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        subdomain: this.subdomain,
                        slug: this.slug
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    this.$dispatch('notify', { message: 'URL settings saved', type: 'success' });
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                this.$dispatch('notify', { message: error.message, type: 'error' });
            } finally {
                this.saving = false;
            }
        }
    }));
});
```

---

## Custom Domain Management

### Domain Add Form

```blade
{{-- resources/views/components/custom-domain-manager.blade.php --}}
<div x-data="customDomainManager(@js($page))" class="space-y-6">
    {{-- Add Domain Form --}}
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Add Custom Domain</h3>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Domain Name</label>
                <input
                    type="text"
                    x-model="newDomain"
                    @keydown.enter="addDomain"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="landing.yourdomain.com"
                >
                <p class="mt-1 text-sm text-gray-500">Enter your domain without http:// or https://</p>
            </div>

            <button
                @click="addDomain"
                class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium disabled:opacity-50"
                :disabled="!newDomain || addingDomain"
            >
                <span x-text="addingDomain ? 'Adding...' : 'Add Domain'"></span>
            </button>
        </div>
    </div>

    {{-- Domain List --}}
    <div x-show="domains.length > 0" class="space-y-4">
        <h3 class="text-lg font-semibold text-gray-900">Your Domains</h3>

        <template x-for="domain in domains" :key="domain.id">
            <div class="bg-white border border-gray-200 rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h4 class="font-medium text-gray-900" x-text="domain.domain"></h4>
                        <div class="flex items-center gap-2 mt-1">
                            {{-- Domain Status Badge --}}
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                :class="{
                                    'bg-yellow-100 text-yellow-800': domain.status === 'pending',
                                    'bg-green-100 text-green-800': domain.status === 'verified',
                                    'bg-red-100 text-red-800': domain.status === 'failed'
                                }"
                                x-text="domain.status.charAt(0).toUpperCase() + domain.status.slice(1)"
                            ></span>
                            {{-- SSL Status Badge --}}
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                :class="{
                                    'bg-gray-100 text-gray-800': domain.ssl_status === 'pending',
                                    'bg-blue-100 text-blue-800': domain.ssl_status === 'provisioning',
                                    'bg-green-100 text-green-800': domain.ssl_status === 'active',
                                    'bg-red-100 text-red-800': domain.ssl_status === 'failed'
                                }"
                            >
                                SSL: <span x-text="domain.ssl_status"></span>
                            </span>
                        </div>
                    </div>
                    <button
                        @click="removeDomain(domain.id)"
                        class="text-red-600 hover:text-red-700"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>

                {{-- DNS Instructions --}}
                <div x-show="domain.status === 'pending'" class="bg-gray-50 rounded-lg p-4">
                    <h5 class="font-medium text-gray-900 mb-3">DNS Configuration Required</h5>
                    <p class="text-sm text-gray-600 mb-4">Add the following DNS records to your domain registrar:</p>

                    <div class="space-y-4">
                        {{-- CNAME Record --}}
                        <div>
                            <h6 class="text-sm font-medium text-gray-700 mb-2">Option 1: CNAME Record (Recommended for subdomains)</h6>
                            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-gray-600">Type</th>
                                            <th class="px-3 py-2 text-left text-gray-600">Name</th>
                                            <th class="px-3 py-2 text-left text-gray-600">Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="px-3 py-2 font-mono">CNAME</td>
                                            <td class="px-3 py-2 font-mono" x-text="getDomainName(domain.domain)"></td>
                                            <td class="px-3 py-2 font-mono">{{ config('app.cname_target') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- A Record --}}
                        <div>
                            <h6 class="text-sm font-medium text-gray-700 mb-2">Option 2: A Record (For root domains)</h6>
                            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-gray-600">Type</th>
                                            <th class="px-3 py-2 text-left text-gray-600">Name</th>
                                            <th class="px-3 py-2 text-left text-gray-600">Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="px-3 py-2 font-mono">A</td>
                                            <td class="px-3 py-2 font-mono">@</td>
                                            <td class="px-3 py-2 font-mono">{{ config('app.a_record_ip') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- TXT Verification Record --}}
                        <div>
                            <h6 class="text-sm font-medium text-gray-700 mb-2">Verification Record (Required)</h6>
                            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-gray-600">Type</th>
                                            <th class="px-3 py-2 text-left text-gray-600">Name</th>
                                            <th class="px-3 py-2 text-left text-gray-600">Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="px-3 py-2 font-mono">TXT</td>
                                            <td class="px-3 py-2 font-mono">_lpb-verify</td>
                                            <td class="px-3 py-2 font-mono text-xs break-all" x-text="domain.verification_token"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center gap-3">
                        <button
                            @click="verifyDomain(domain.id)"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium"
                            :disabled="verifying === domain.id"
                        >
                            <span x-text="verifying === domain.id ? 'Verifying...' : 'Verify DNS'"></span>
                        </button>
                        <p class="text-xs text-gray-500">DNS changes can take up to 48 hours to propagate</p>
                    </div>
                </div>

                {{-- Verified Success --}}
                <div x-show="domain.status === 'verified'" class="bg-green-50 rounded-lg p-4">
                    <div class="flex items-center gap-2 text-green-800">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-medium">Domain verified and active</span>
                    </div>
                    <p class="mt-2 text-sm text-green-700">
                        Your page is now available at:
                        <a :href="`https://${domain.domain}`" target="_blank" class="underline" x-text="`https://${domain.domain}`"></a>
                    </p>
                </div>
            </div>
        </template>
    </div>
</div>
```

```javascript
// resources/js/components/custom-domain-manager.js
document.addEventListener('alpine:init', () => {
    Alpine.data('customDomainManager', (page) => ({
        newDomain: '',
        domains: page.custom_domains || [],
        addingDomain: false,
        verifying: null,

        getDomainName(domain) {
            const parts = domain.split('.');
            if (parts.length > 2) {
                return parts[0];
            }
            return '@';
        },

        async addDomain() {
            if (!this.newDomain) return;

            this.addingDomain = true;

            try {
                const response = await fetch(`/api/pages/${page.id}/domains`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ domain: this.newDomain })
                });

                const data = await response.json();

                if (response.ok) {
                    this.domains.push(data.domain);
                    this.newDomain = '';
                    this.$dispatch('notify', { message: 'Domain added successfully', type: 'success' });
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                this.$dispatch('notify', { message: error.message, type: 'error' });
            } finally {
                this.addingDomain = false;
            }
        },

        async verifyDomain(domainId) {
            this.verifying = domainId;

            try {
                const response = await fetch(`/api/domains/${domainId}/verify`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    const index = this.domains.findIndex(d => d.id === domainId);
                    if (index !== -1) {
                        this.domains[index] = data.domain;
                    }

                    if (data.domain.status === 'verified') {
                        this.$dispatch('notify', { message: 'Domain verified successfully!', type: 'success' });
                    } else {
                        this.$dispatch('notify', { message: 'DNS records not found. Please check your configuration.', type: 'warning' });
                    }
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                this.$dispatch('notify', { message: error.message, type: 'error' });
            } finally {
                this.verifying = null;
            }
        },

        async removeDomain(domainId) {
            if (!confirm('Are you sure you want to remove this domain?')) return;

            try {
                const response = await fetch(`/api/domains/${domainId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (response.ok) {
                    this.domains = this.domains.filter(d => d.id !== domainId);
                    this.$dispatch('notify', { message: 'Domain removed', type: 'success' });
                } else {
                    const data = await response.json();
                    throw new Error(data.message);
                }
            } catch (error) {
                this.$dispatch('notify', { message: error.message, type: 'error' });
            }
        }
    }));
});
```

---

## Domain Verification

### DNS Verification Service

```php
// app/Services/DnsVerificationService.php
<?php

namespace App\Services;

use App\Models\CustomDomain;
use Illuminate\Support\Facades\Log;

class DnsVerificationService
{
    protected string $cnameTarget;
    protected string $aRecordIp;

    public function __construct()
    {
        $this->cnameTarget = config('app.cname_target');
        $this->aRecordIp = config('app.a_record_ip');
    }

    public function verify(CustomDomain $domain): bool
    {
        $results = [
            'txt' => $this->verifyTxtRecord($domain),
            'dns' => $this->verifyCnameOrARecord($domain),
        ];

        Log::info('DNS verification results', [
            'domain' => $domain->domain,
            'results' => $results
        ]);

        // Both TXT verification and DNS record must pass
        return $results['txt'] && $results['dns'];
    }

    protected function verifyTxtRecord(CustomDomain $domain): bool
    {
        $hostname = '_lpb-verify.' . $domain->domain;

        try {
            $records = dns_get_record($hostname, DNS_TXT);

            if (!$records) {
                return false;
            }

            foreach ($records as $record) {
                if (isset($record['txt']) && $record['txt'] === $domain->verification_token) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            Log::error('TXT record verification failed', [
                'domain' => $domain->domain,
                'error' => $e->getMessage()
            ]);
        }

        return false;
    }

    protected function verifyCnameOrARecord(CustomDomain $domain): bool
    {
        // Try CNAME first
        if ($this->verifyCnameRecord($domain)) {
            return true;
        }

        // Fall back to A record
        return $this->verifyARecord($domain);
    }

    protected function verifyCnameRecord(CustomDomain $domain): bool
    {
        try {
            $records = dns_get_record($domain->domain, DNS_CNAME);

            if (!$records) {
                return false;
            }

            foreach ($records as $record) {
                if (isset($record['target'])) {
                    // Normalize the target (remove trailing dot)
                    $target = rtrim($record['target'], '.');
                    $expected = rtrim($this->cnameTarget, '.');

                    if (strcasecmp($target, $expected) === 0) {
                        return true;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('CNAME record verification failed', [
                'domain' => $domain->domain,
                'error' => $e->getMessage()
            ]);
        }

        return false;
    }

    protected function verifyARecord(CustomDomain $domain): bool
    {
        try {
            $records = dns_get_record($domain->domain, DNS_A);

            if (!$records) {
                return false;
            }

            foreach ($records as $record) {
                if (isset($record['ip']) && $record['ip'] === $this->aRecordIp) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            Log::error('A record verification failed', [
                'domain' => $domain->domain,
                'error' => $e->getMessage()
            ]);
        }

        return false;
    }

    public function getDnsRecords(string $domain): array
    {
        $records = [];

        try {
            $records['A'] = dns_get_record($domain, DNS_A) ?: [];
            $records['CNAME'] = dns_get_record($domain, DNS_CNAME) ?: [];
            $records['TXT'] = dns_get_record('_lpb-verify.' . $domain, DNS_TXT) ?: [];
        } catch (\Exception $e) {
            Log::error('Failed to fetch DNS records', [
                'domain' => $domain,
                'error' => $e->getMessage()
            ]);
        }

        return $records;
    }
}
```

### Domain Verification Job

```php
// app/Jobs/VerifyDomainJob.php
<?php

namespace App\Jobs;

use App\Models\CustomDomain;
use App\Services\DnsVerificationService;
use App\Services\SslProvisioningService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class VerifyDomainJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        protected CustomDomain $domain
    ) {}

    public function handle(
        DnsVerificationService $dnsService,
        SslProvisioningService $sslService
    ): void {
        $verified = $dnsService->verify($this->domain);

        if ($verified) {
            $this->domain->update([
                'status' => 'verified',
                'verified_at' => now(),
            ]);

            // Trigger SSL provisioning
            $sslService->provision($this->domain);
        } else {
            // Check if we should mark as failed (after multiple attempts)
            if ($this->attempts() >= $this->tries) {
                $this->domain->update(['status' => 'failed']);
            }
        }
    }
}
```

---

## SSL Management

### SSL Provisioning Service (Let's Encrypt)

```php
// app/Services/SslProvisioningService.php
<?php

namespace App\Services;

use App\Models\CustomDomain;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SslProvisioningService
{
    /**
     * Provision SSL certificate for a domain.
     *
     * This service integrates with your SSL provider (e.g., Caddy, nginx + certbot,
     * or a managed service like Cloudflare).
     *
     * Implementation depends on your infrastructure:
     * - Caddy: Automatic HTTPS with on-demand TLS
     * - nginx + certbot: API call to certbot or ACME client
     * - Cloudflare: API call to enable SSL
     * - AWS ACM: Request certificate via AWS SDK
     */
    public function provision(CustomDomain $domain): bool
    {
        try {
            $domain->update(['ssl_status' => 'provisioning']);

            // Example: Using Caddy's on-demand TLS API
            // Caddy automatically provisions SSL when the domain is accessed
            // We just need to add it to the allowed domains list

            if ($this->useCaddyOnDemandTls()) {
                return $this->provisionWithCaddy($domain);
            }

            // Example: Using certbot via API
            if ($this->useCertbot()) {
                return $this->provisionWithCertbot($domain);
            }

            // Example: Using Cloudflare
            if ($this->useCloudflare()) {
                return $this->provisionWithCloudflare($domain);
            }

            // Default: Mark as active (for development or proxy-based SSL)
            $domain->update([
                'ssl_status' => 'active',
                'ssl_provisioned_at' => now(),
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('SSL provisioning failed', [
                'domain' => $domain->domain,
                'error' => $e->getMessage()
            ]);

            $domain->update(['ssl_status' => 'failed']);
            return false;
        }
    }

    protected function provisionWithCaddy(CustomDomain $domain): bool
    {
        // Caddy's on-demand TLS configuration
        // Add domain to the ask endpoint whitelist

        // Update your domains whitelist (stored in cache/database)
        cache()->forever('ssl_domains', array_merge(
            cache()->get('ssl_domains', []),
            [$domain->domain]
        ));

        $domain->update([
            'ssl_status' => 'active',
            'ssl_provisioned_at' => now(),
        ]);

        return true;
    }

    protected function provisionWithCertbot(CustomDomain $domain): bool
    {
        // Call your certbot API or run command
        // This is typically done via a queue job on your server

        $response = Http::post(config('services.certbot.endpoint'), [
            'domain' => $domain->domain,
            'webroot' => config('services.certbot.webroot'),
        ]);

        if ($response->successful()) {
            $domain->update([
                'ssl_status' => 'active',
                'ssl_provisioned_at' => now(),
            ]);
            return true;
        }

        return false;
    }

    protected function provisionWithCloudflare(CustomDomain $domain): bool
    {
        // Cloudflare handles SSL automatically for proxied domains
        // Just need to add the domain to Cloudflare

        $response = Http::withToken(config('services.cloudflare.api_token'))
            ->post('https://api.cloudflare.com/client/v4/zones/' . config('services.cloudflare.zone_id') . '/custom_hostnames', [
                'hostname' => $domain->domain,
                'ssl' => [
                    'method' => 'http',
                    'type' => 'dv',
                ]
            ]);

        if ($response->successful()) {
            $domain->update([
                'ssl_status' => 'active',
                'ssl_provisioned_at' => now(),
            ]);
            return true;
        }

        return false;
    }

    protected function useCaddyOnDemandTls(): bool
    {
        return config('services.ssl.provider') === 'caddy';
    }

    protected function useCertbot(): bool
    {
        return config('services.ssl.provider') === 'certbot';
    }

    protected function useCloudflare(): bool
    {
        return config('services.ssl.provider') === 'cloudflare';
    }

    /**
     * Check SSL certificate status
     */
    public function checkStatus(CustomDomain $domain): array
    {
        try {
            $context = stream_context_create([
                'ssl' => [
                    'capture_peer_cert' => true,
                    'verify_peer' => false,
                ]
            ]);

            $client = @stream_socket_client(
                "ssl://{$domain->domain}:443",
                $errno,
                $errstr,
                30,
                STREAM_CLIENT_CONNECT,
                $context
            );

            if ($client) {
                $params = stream_context_get_params($client);
                $cert = openssl_x509_parse($params['options']['ssl']['peer_certificate']);

                return [
                    'valid' => true,
                    'issuer' => $cert['issuer']['O'] ?? 'Unknown',
                    'expires' => date('Y-m-d H:i:s', $cert['validTo_time_t']),
                    'subject' => $cert['subject']['CN'] ?? $domain->domain,
                ];
            }
        } catch (\Exception $e) {
            Log::error('SSL status check failed', [
                'domain' => $domain->domain,
                'error' => $e->getMessage()
            ]);
        }

        return [
            'valid' => false,
            'error' => $errstr ?? 'Could not connect'
        ];
    }
}
```

### Caddy Configuration Example

```caddyfile
# /etc/caddy/Caddyfile

{
    on_demand_tls {
        ask http://localhost:8080/api/ssl/verify-domain
        interval 2m
        burst 5
    }
}

# Main application
app.yourdomain.com {
    reverse_proxy localhost:8000
}

# Wildcard for subdomains
*.yourdomain.com {
    reverse_proxy localhost:8000
}

# Custom domains with on-demand TLS
:443 {
    tls {
        on_demand
    }
    reverse_proxy localhost:8000
}
```

---

## Published Page Rendering

### Routes Configuration

```php
// routes/web.php

use App\Http\Controllers\PublishedPageController;

// Published pages via slug
Route::get('/p/{slug}', [PublishedPageController::class, 'showBySlug'])
    ->name('published.page.slug');

// Subdomain routing (requires wildcard DNS)
Route::domain('{subdomain}.' . config('app.domain'))
    ->group(function () {
        Route::get('/', [PublishedPageController::class, 'showBySubdomain'])
            ->name('published.page.subdomain');
    });
```

### Custom Domain Middleware

```php
// app/Http/Middleware/ResolveCustomDomain.php
<?php

namespace App\Http\Middleware;

use App\Models\CustomDomain;
use Closure;
use Illuminate\Http\Request;

class ResolveCustomDomain
{
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();

        // Skip if it's the main app domain
        if ($this->isAppDomain($host)) {
            return $next($request);
        }

        // Look up custom domain
        $customDomain = CustomDomain::where('domain', $host)
            ->where('status', 'verified')
            ->with('page')
            ->first();

        if ($customDomain && $customDomain->page) {
            $request->attributes->set('custom_domain', $customDomain);
            $request->attributes->set('published_page', $customDomain->page);
        }

        return $next($request);
    }

    protected function isAppDomain(string $host): bool
    {
        $appDomain = config('app.domain');
        return $host === $appDomain || str_ends_with($host, '.' . $appDomain);
    }
}
```

### Published Page Controller

```php
// app/Http/Controllers/PublishedPageController.php
<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Services\PageRendererService;
use Illuminate\Http\Request;

class PublishedPageController extends Controller
{
    public function __construct(
        protected PageRendererService $renderer
    ) {}

    public function showBySlug(string $slug)
    {
        $page = Page::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        return $this->renderPage($page);
    }

    public function showBySubdomain(Request $request, string $subdomain)
    {
        $page = Page::where('subdomain', $subdomain)
            ->where('status', 'published')
            ->firstOrFail();

        return $this->renderPage($page);
    }

    public function showByCustomDomain(Request $request)
    {
        $page = $request->attributes->get('published_page');

        if (!$page || $page->status !== 'published') {
            abort(404);
        }

        return $this->renderPage($page);
    }

    protected function renderPage(Page $page)
    {
        // Track page view
        $this->trackPageView($page);

        return view('published.page', [
            'page' => $page,
            'html' => $this->renderer->render($page->published_content),
            'seo' => $page->seo_settings ?? [],
            'analytics' => $page->analytics_settings ?? [],
        ]);
    }

    protected function trackPageView(Page $page): void
    {
        // Queue analytics tracking
        dispatch(function () use ($page) {
            $page->increment('view_count');
        })->afterResponse();
    }
}
```

### Page Renderer Service (JSON to HTML)

```php
// app/Services/PageRendererService.php
<?php

namespace App\Services;

use Illuminate\Support\Str;

class PageRendererService
{
    public function render(?array $content): string
    {
        if (!$content || empty($content['sections'])) {
            return '<div class="min-h-screen flex items-center justify-center"><p class="text-gray-500">No content</p></div>';
        }

        $html = '';

        foreach ($content['sections'] as $section) {
            $html .= $this->renderSection($section);
        }

        return $html;
    }

    protected function renderSection(array $section): string
    {
        $type = $section['type'] ?? 'container';
        $method = 'render' . Str::studly($type) . 'Section';

        if (method_exists($this, $method)) {
            return $this->{$method}($section);
        }

        return $this->renderGenericSection($section);
    }

    protected function renderHeroSection(array $section): string
    {
        $data = $section['data'] ?? [];
        $styles = $this->buildStyles($section['styles'] ?? []);

        return <<<HTML
        <section class="relative py-20 px-4" style="{$styles}">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-4xl md:text-6xl font-bold mb-6">{$this->escape($data['headline'] ?? '')}</h1>
                <p class="text-xl md:text-2xl mb-8 opacity-90">{$this->escape($data['subheadline'] ?? '')}</p>
                {$this->renderButton($data['cta'] ?? [])}
            </div>
        </section>
        HTML;
    }

    protected function renderFeaturesSection(array $section): string
    {
        $data = $section['data'] ?? [];
        $features = $data['features'] ?? [];
        $styles = $this->buildStyles($section['styles'] ?? []);

        $featuresHtml = '';
        foreach ($features as $feature) {
            $featuresHtml .= <<<HTML
            <div class="text-center p-6">
                <div class="w-12 h-12 mx-auto mb-4 rounded-full bg-blue-100 flex items-center justify-center">
                    {$this->renderIcon($feature['icon'] ?? 'star')}
                </div>
                <h3 class="text-lg font-semibold mb-2">{$this->escape($feature['title'] ?? '')}</h3>
                <p class="text-gray-600">{$this->escape($feature['description'] ?? '')}</p>
            </div>
            HTML;
        }

        return <<<HTML
        <section class="py-16 px-4" style="{$styles}">
            <div class="max-w-6xl mx-auto">
                <h2 class="text-3xl font-bold text-center mb-12">{$this->escape($data['title'] ?? '')}</h2>
                <div class="grid md:grid-cols-3 gap-8">
                    {$featuresHtml}
                </div>
            </div>
        </section>
        HTML;
    }

    protected function renderCtaSection(array $section): string
    {
        $data = $section['data'] ?? [];
        $styles = $this->buildStyles($section['styles'] ?? []);

        return <<<HTML
        <section class="py-16 px-4" style="{$styles}">
            <div class="max-w-3xl mx-auto text-center">
                <h2 class="text-3xl font-bold mb-4">{$this->escape($data['headline'] ?? '')}</h2>
                <p class="text-lg mb-8 opacity-90">{$this->escape($data['description'] ?? '')}</p>
                {$this->renderButton($data['button'] ?? [])}
            </div>
        </section>
        HTML;
    }

    protected function renderTestimonialsSection(array $section): string
    {
        $data = $section['data'] ?? [];
        $testimonials = $data['testimonials'] ?? [];
        $styles = $this->buildStyles($section['styles'] ?? []);

        $testimonialsHtml = '';
        foreach ($testimonials as $testimonial) {
            $testimonialsHtml .= <<<HTML
            <div class="bg-white p-6 rounded-lg shadow-md">
                <p class="text-gray-600 mb-4">"{$this->escape($testimonial['quote'] ?? '')}"</p>
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-gray-300 rounded-full mr-3"></div>
                    <div>
                        <p class="font-semibold">{$this->escape($testimonial['name'] ?? '')}</p>
                        <p class="text-sm text-gray-500">{$this->escape($testimonial['title'] ?? '')}</p>
                    </div>
                </div>
            </div>
            HTML;
        }

        return <<<HTML
        <section class="py-16 px-4 bg-gray-50" style="{$styles}">
            <div class="max-w-6xl mx-auto">
                <h2 class="text-3xl font-bold text-center mb-12">{$this->escape($data['title'] ?? '')}</h2>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {$testimonialsHtml}
                </div>
            </div>
        </section>
        HTML;
    }

    protected function renderGenericSection(array $section): string
    {
        $data = $section['data'] ?? [];
        $styles = $this->buildStyles($section['styles'] ?? []);

        return <<<HTML
        <section class="py-12 px-4" style="{$styles}">
            <div class="max-w-4xl mx-auto">
                {$this->renderContent($data['content'] ?? '')}
            </div>
        </section>
        HTML;
    }

    protected function renderButton(array $button): string
    {
        if (empty($button['text'])) {
            return '';
        }

        $href = $this->escape($button['url'] ?? '#');
        $text = $this->escape($button['text']);
        $style = $button['style'] ?? 'primary';

        $classes = match ($style) {
            'primary' => 'bg-blue-600 text-white hover:bg-blue-700',
            'secondary' => 'bg-gray-200 text-gray-800 hover:bg-gray-300',
            'outline' => 'border-2 border-current hover:bg-white/10',
            default => 'bg-blue-600 text-white hover:bg-blue-700',
        };

        return <<<HTML
        <a href="{$href}" class="inline-block px-8 py-3 rounded-lg font-semibold transition-colors {$classes}">
            {$text}
        </a>
        HTML;
    }

    protected function renderIcon(string $icon): string
    {
        // Simple icon rendering - extend with your icon library
        return '<svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
    }

    protected function renderContent(string $content): string
    {
        // Parse markdown or return as-is
        return nl2br($this->escape($content));
    }

    protected function buildStyles(array $styles): string
    {
        $css = [];

        if (!empty($styles['backgroundColor'])) {
            $css[] = "background-color: {$styles['backgroundColor']}";
        }

        if (!empty($styles['backgroundImage'])) {
            $css[] = "background-image: url('{$styles['backgroundImage']}')";
            $css[] = "background-size: cover";
            $css[] = "background-position: center";
        }

        if (!empty($styles['textColor'])) {
            $css[] = "color: {$styles['textColor']}";
        }

        if (!empty($styles['padding'])) {
            $css[] = "padding: {$styles['padding']}";
        }

        return implode('; ', $css);
    }

    protected function escape(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}
```

### Published Page View

```blade
{{-- resources/views/published/page.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- SEO Meta Tags --}}
    <title>{{ $seo['meta_title'] ?? $page->title }}</title>
    <meta name="description" content="{{ $seo['meta_description'] ?? '' }}">

    @if(!empty($seo['meta_keywords']))
        <meta name="keywords" content="{{ $seo['meta_keywords'] }}">
    @endif

    {{-- Open Graph --}}
    <meta property="og:title" content="{{ $seo['og_title'] ?? $seo['meta_title'] ?? $page->title }}">
    <meta property="og:description" content="{{ $seo['og_description'] ?? $seo['meta_description'] ?? '' }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">

    @if(!empty($seo['og_image']))
        <meta property="og:image" content="{{ $seo['og_image'] }}">
    @endif

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seo['twitter_title'] ?? $seo['meta_title'] ?? $page->title }}">
    <meta name="twitter:description" content="{{ $seo['twitter_description'] ?? $seo['meta_description'] ?? '' }}">

    @if(!empty($seo['twitter_image']))
        <meta name="twitter:image" content="{{ $seo['twitter_image'] }}">
    @endif

    {{-- Favicon --}}
    @if(!empty($seo['favicon']))
        <link rel="icon" href="{{ $seo['favicon'] }}" type="image/x-icon">
    @endif

    {{-- Canonical URL --}}
    @if(!empty($seo['canonical_url']))
        <link rel="canonical" href="{{ $seo['canonical_url'] }}">
    @endif

    {{-- Robots --}}
    @if(!empty($seo['robots']))
        <meta name="robots" content="{{ $seo['robots'] }}">
    @endif

    {{-- TailwindCSS v4 --}}
    @vite(['resources/css/published.css'])

    {{-- Custom CSS --}}
    @if(!empty($page->custom_css))
        <style>{!! $page->custom_css !!}</style>
    @endif

    {{-- Analytics - Google Analytics --}}
    @if(!empty($analytics['google_analytics_id']))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $analytics['google_analytics_id'] }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ $analytics['google_analytics_id'] }}');
        </script>
    @endif

    {{-- Analytics - Facebook Pixel --}}
    @if(!empty($analytics['facebook_pixel_id']))
        <script>
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
            n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s)}(window, document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '{{ $analytics['facebook_pixel_id'] }}');
            fbq('track', 'PageView');
        </script>
    @endif

    {{-- Custom Head Code --}}
    @if(!empty($analytics['custom_head_code']))
        {!! $analytics['custom_head_code'] !!}
    @endif
</head>
<body class="min-h-screen">
    {{-- Rendered Page Content --}}
    {!! $html !!}

    {{-- Custom Body Code --}}
    @if(!empty($analytics['custom_body_code']))
        {!! $analytics['custom_body_code'] !!}
    @endif
</body>
</html>
```

---

## Unpublish Flow

### Unpublish Button Component

```blade
{{-- resources/views/components/unpublish-button.blade.php --}}
<div x-data="{ showConfirm: false, unpublishing: false }">
    <button
        @click="showConfirm = true"
        class="inline-flex items-center gap-2 px-4 py-2 text-red-600 hover:text-red-700 font-medium"
        x-show="$wire.page.status === 'published'"
    >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
        </svg>
        Unpublish
    </button>

    {{-- Confirmation Modal --}}
    <template x-teleport="body">
        <div
            x-show="showConfirm"
            x-transition
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            @click.self="showConfirm = false"
        >
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Unpublish Page</h3>
                        <p class="text-sm text-gray-500">This will take your page offline</p>
                    </div>
                </div>

                <p class="text-gray-600 mb-6">
                    Are you sure you want to unpublish this page? It will no longer be accessible to visitors, but you can publish it again at any time.
                </p>

                <div class="flex items-center justify-end gap-3">
                    <button
                        @click="showConfirm = false"
                        class="px-4 py-2 text-gray-700 hover:text-gray-900 font-medium"
                    >
                        Cancel
                    </button>
                    <button
                        @click="unpublish"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium disabled:opacity-50"
                        :disabled="unpublishing"
                    >
                        <span x-text="unpublishing ? 'Unpublishing...' : 'Unpublish'"></span>
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
function unpublish() {
    this.unpublishing = true;

    fetch(`/api/pages/${this.$wire.page.id}/unpublish`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            this.$wire.page.status = 'unpublished';
            this.$dispatch('notify', { message: 'Page unpublished', type: 'success' });
            this.$dispatch('page-unpublished');
        }
        this.showConfirm = false;
    })
    .catch(error => {
        this.$dispatch('notify', { message: 'Failed to unpublish', type: 'error' });
    })
    .finally(() => {
        this.unpublishing = false;
    });
}
</script>
```

---

## Version Control

### Version Control Service

```php
// app/Services/PageVersionService.php
<?php

namespace App\Services;

use App\Models\Page;
use App\Models\PageVersion;
use Illuminate\Support\Facades\Auth;

class PageVersionService
{
    public function createVersion(Page $page, ?string $description = null): PageVersion
    {
        $latestVersion = $page->versions()->max('version_number') ?? 0;

        return $page->versions()->create([
            'content' => $page->content,
            'version_number' => $latestVersion + 1,
            'change_description' => $description,
            'created_by' => Auth::id(),
        ]);
    }

    public function restoreVersion(Page $page, PageVersion $version): Page
    {
        // Create a backup of current state
        $this->createVersion($page, 'Auto-backup before restore');

        // Restore the content
        $page->update([
            'content' => $version->content,
        ]);

        return $page->fresh();
    }

    public function compareVersions(PageVersion $version1, PageVersion $version2): array
    {
        return [
            'version1' => [
                'number' => $version1->version_number,
                'content' => $version1->content,
                'created_at' => $version1->created_at,
            ],
            'version2' => [
                'number' => $version2->version_number,
                'content' => $version2->content,
                'created_at' => $version2->created_at,
            ],
            'differences' => $this->calculateDifferences($version1->content, $version2->content),
        ];
    }

    protected function calculateDifferences(array $content1, array $content2): array
    {
        $differences = [];

        // Compare sections
        $sections1 = $content1['sections'] ?? [];
        $sections2 = $content2['sections'] ?? [];

        $differences['sections_added'] = count($sections2) - count($sections1);

        return $differences;
    }
}
```

### Version History Component

```blade
{{-- resources/views/components/version-history.blade.php --}}
<div x-data="versionHistory(@js($page->id))" class="space-y-4">
    <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900">Version History</h3>
        <button
            @click="loadVersions"
            class="text-sm text-blue-600 hover:text-blue-700"
        >
            Refresh
        </button>
    </div>

    {{-- Current Draft vs Published --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-sm font-medium text-blue-800">Current Draft</span>
                <p class="text-xs text-blue-600 mt-1">
                    <span x-show="hasUnpublishedChanges">Has unpublished changes</span>
                    <span x-show="!hasUnpublishedChanges">Matches published version</span>
                </p>
            </div>
            <button
                x-show="hasUnpublishedChanges"
                @click="discardDraftChanges"
                class="text-sm text-blue-600 hover:text-blue-700"
            >
                Discard changes
            </button>
        </div>
    </div>

    {{-- Version List --}}
    <div class="space-y-2">
        <template x-for="version in versions" :key="version.id">
            <div class="bg-white border border-gray-200 rounded-lg p-4 hover:border-gray-300 transition-colors">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="font-medium text-gray-900">Version <span x-text="version.version_number"></span></span>
                        <p class="text-sm text-gray-500" x-text="version.change_description || 'No description'"></p>
                        <p class="text-xs text-gray-400 mt-1">
                            <span x-text="formatDate(version.created_at)"></span>
                            by <span x-text="version.creator?.name || 'Unknown'"></span>
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            @click="previewVersion(version)"
                            class="p-2 text-gray-500 hover:text-gray-700"
                            title="Preview"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                        <button
                            @click="restoreVersion(version)"
                            class="p-2 text-gray-500 hover:text-gray-700"
                            title="Restore"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- Empty State --}}
    <div x-show="versions.length === 0 && !loading" class="text-center py-8">
        <p class="text-gray-500">No version history yet</p>
    </div>
</div>
```

```javascript
// resources/js/components/version-history.js
document.addEventListener('alpine:init', () => {
    Alpine.data('versionHistory', (pageId) => ({
        versions: [],
        loading: false,
        hasUnpublishedChanges: false,

        async init() {
            await this.loadVersions();
            this.checkForUnpublishedChanges();
        },

        async loadVersions() {
            this.loading = true;

            try {
                const response = await fetch(`/api/pages/${pageId}/versions`);
                const data = await response.json();
                this.versions = data.versions;
            } catch (error) {
                console.error('Failed to load versions:', error);
            } finally {
                this.loading = false;
            }
        },

        async checkForUnpublishedChanges() {
            try {
                const response = await fetch(`/api/pages/${pageId}/has-changes`);
                const data = await response.json();
                this.hasUnpublishedChanges = data.has_changes;
            } catch (error) {
                console.error('Failed to check changes:', error);
            }
        },

        async restoreVersion(version) {
            if (!confirm(`Restore to version ${version.version_number}? This will replace your current draft.`)) {
                return;
            }

            try {
                const response = await fetch(`/api/pages/${pageId}/versions/${version.id}/restore`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (response.ok) {
                    this.$dispatch('notify', { message: 'Version restored successfully', type: 'success' });
                    this.$dispatch('version-restored');
                    await this.loadVersions();
                }
            } catch (error) {
                this.$dispatch('notify', { message: 'Failed to restore version', type: 'error' });
            }
        },

        async discardDraftChanges() {
            if (!confirm('Discard all unpublished changes? This cannot be undone.')) {
                return;
            }

            try {
                const response = await fetch(`/api/pages/${pageId}/discard-draft`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (response.ok) {
                    this.$dispatch('notify', { message: 'Changes discarded', type: 'success' });
                    this.$dispatch('draft-discarded');
                    this.hasUnpublishedChanges = false;
                }
            } catch (error) {
                this.$dispatch('notify', { message: 'Failed to discard changes', type: 'error' });
            }
        },

        previewVersion(version) {
            window.open(`/api/pages/${pageId}/versions/${version.id}/preview`, '_blank');
        },

        formatDate(dateString) {
            return new Date(dateString).toLocaleString();
        }
    }));
});
```

---

## Controllers

### PublishController

```php
// app/Http/Controllers/Api/PublishController.php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\PageVersionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PublishController extends Controller
{
    public function __construct(
        protected PageVersionService $versionService
    ) {}

    public function publish(Request $request, Page $page): JsonResponse
    {
        Gate::authorize('update', $page);

        // Validate page is ready to publish
        $validation = $this->validateForPublish($page);
        if (!$validation['valid']) {
            return response()->json([
                'message' => 'Page cannot be published',
                'errors' => $validation['errors']
            ], 422);
        }

        // Create version before publishing
        $this->versionService->createVersion($page, 'Published');

        // Update page status
        $page->update([
            'published_content' => $page->content,
            'status' => 'published',
            'published_at' => now(),
        ]);

        return response()->json([
            'message' => 'Page published successfully',
            'published_at' => $page->published_at->toISOString(),
            'url' => $this->getPublishedUrl($page),
        ]);
    }

    public function unpublish(Request $request, Page $page): JsonResponse
    {
        Gate::authorize('update', $page);

        $page->update([
            'status' => 'unpublished',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Page unpublished successfully',
        ]);
    }

    public function checkSlug(Request $request): JsonResponse
    {
        $request->validate([
            'slug' => 'required|string|max:255',
            'page_id' => 'nullable|integer',
        ]);

        $query = Page::where('slug', $request->slug);

        if ($request->page_id) {
            $query->where('id', '!=', $request->page_id);
        }

        $available = !$query->exists();

        return response()->json([
            'available' => $available,
            'message' => $available ? 'Slug is available' : 'Slug is already taken',
        ]);
    }

    public function updateUrlSettings(Request $request, Page $page): JsonResponse
    {
        Gate::authorize('update', $page);

        $request->validate([
            'subdomain' => 'nullable|string|max:63|regex:/^[a-z0-9]([a-z0-9-]*[a-z0-9])?$/',
            'slug' => 'required|string|max:255|unique:pages,slug,' . $page->id,
        ]);

        $page->update([
            'subdomain' => $request->subdomain,
            'slug' => $request->slug,
        ]);

        return response()->json([
            'message' => 'URL settings updated',
            'page' => $page,
        ]);
    }

    protected function validateForPublish(Page $page): array
    {
        $errors = [];

        if (empty($page->title)) {
            $errors[] = 'Page title is required';
        }

        if (empty($page->content) || empty($page->content['sections'])) {
            $errors[] = 'Page must have at least one section';
        }

        if (empty($page->slug)) {
            $errors[] = 'Page slug is required';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    protected function getPublishedUrl(Page $page): string
    {
        if ($page->customDomain && $page->customDomain->status === 'verified') {
            return 'https://' . $page->customDomain->domain;
        }

        if ($page->subdomain) {
            return 'https://' . $page->subdomain . '.' . config('app.domain');
        }

        return config('app.url') . '/p/' . $page->slug;
    }
}
```

### DomainController

```php
// app/Http/Controllers/Api/DomainController.php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\VerifyDomainJob;
use App\Models\CustomDomain;
use App\Models\Page;
use App\Services\DnsVerificationService;
use App\Services\SslProvisioningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class DomainController extends Controller
{
    public function __construct(
        protected DnsVerificationService $dnsService,
        protected SslProvisioningService $sslService
    ) {}

    public function store(Request $request, Page $page): JsonResponse
    {
        Gate::authorize('update', $page);

        $request->validate([
            'domain' => [
                'required',
                'string',
                'max:255',
                'regex:/^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$/',
                'unique:custom_domains,domain',
            ],
        ]);

        // Check if page already has a custom domain
        if ($page->customDomain) {
            return response()->json([
                'message' => 'Page already has a custom domain. Remove it first.',
            ], 422);
        }

        $domain = CustomDomain::create([
            'page_id' => $page->id,
            'domain' => strtolower($request->domain),
            'status' => 'pending',
            'verification_token' => Str::random(32),
        ]);

        return response()->json([
            'message' => 'Domain added successfully',
            'domain' => $domain,
        ], 201);
    }

    public function verify(CustomDomain $domain): JsonResponse
    {
        Gate::authorize('update', $domain->page);

        $verified = $this->dnsService->verify($domain);

        if ($verified) {
            $domain->update([
                'status' => 'verified',
                'verified_at' => now(),
            ]);

            // Provision SSL
            $this->sslService->provision($domain);

            return response()->json([
                'message' => 'Domain verified successfully',
                'domain' => $domain->fresh(),
            ]);
        }

        return response()->json([
            'message' => 'DNS verification failed',
            'domain' => $domain,
            'dns_records' => $this->dnsService->getDnsRecords($domain->domain),
        ]);
    }

    public function destroy(CustomDomain $domain): JsonResponse
    {
        Gate::authorize('update', $domain->page);

        $domain->delete();

        return response()->json([
            'message' => 'Domain removed successfully',
        ]);
    }

    public function checkSslStatus(CustomDomain $domain): JsonResponse
    {
        Gate::authorize('update', $domain->page);

        $status = $this->sslService->checkStatus($domain);

        return response()->json([
            'ssl_status' => $domain->ssl_status,
            'certificate' => $status,
        ]);
    }

    public function verifyForSsl(Request $request): JsonResponse
    {
        // Endpoint for Caddy's on-demand TLS verification
        $domain = $request->query('domain');

        if (!$domain) {
            return response()->json(['error' => 'Domain required'], 400);
        }

        $exists = CustomDomain::where('domain', $domain)
            ->where('status', 'verified')
            ->exists();

        if ($exists) {
            return response()->json(['allowed' => true]);
        }

        return response()->json(['error' => 'Domain not allowed'], 403);
    }
}
```

### API Routes

```php
// routes/api.php

use App\Http\Controllers\Api\PublishController;
use App\Http\Controllers\Api\DomainController;

Route::middleware('auth:sanctum')->group(function () {
    // Publishing
    Route::post('/pages/{page}/publish', [PublishController::class, 'publish']);
    Route::post('/pages/{page}/unpublish', [PublishController::class, 'unpublish']);
    Route::put('/pages/{page}/url-settings', [PublishController::class, 'updateUrlSettings']);
    Route::get('/slugs/check', [PublishController::class, 'checkSlug']);

    // Domains
    Route::post('/pages/{page}/domains', [DomainController::class, 'store']);
    Route::post('/domains/{domain}/verify', [DomainController::class, 'verify']);
    Route::delete('/domains/{domain}', [DomainController::class, 'destroy']);
    Route::get('/domains/{domain}/ssl-status', [DomainController::class, 'checkSslStatus']);

    // Versions
    Route::get('/pages/{page}/versions', [VersionController::class, 'index']);
    Route::post('/pages/{page}/versions/{version}/restore', [VersionController::class, 'restore']);
    Route::get('/pages/{page}/versions/{version}/preview', [VersionController::class, 'preview']);
    Route::get('/pages/{page}/has-changes', [VersionController::class, 'hasChanges']);
    Route::post('/pages/{page}/discard-draft', [VersionController::class, 'discardDraft']);

    // Subdomains
    Route::get('/subdomains/check', [SubdomainController::class, 'check']);
});

// Public SSL verification endpoint (for Caddy)
Route::get('/ssl/verify-domain', [DomainController::class, 'verifyForSsl']);
```

---

## Configuration

### Environment Variables

```env
# .env

# App domain settings
APP_DOMAIN=yourdomain.com
CNAME_TARGET=proxy.yourdomain.com
A_RECORD_IP=123.45.67.89

# SSL provider (caddy, certbot, cloudflare)
SSL_PROVIDER=caddy

# Cloudflare (if using)
CLOUDFLARE_API_TOKEN=your-api-token
CLOUDFLARE_ZONE_ID=your-zone-id

# Certbot (if using)
CERTBOT_ENDPOINT=http://localhost:9000/certbot
CERTBOT_WEBROOT=/var/www/certbot
```

### Config File

```php
// config/app.php

return [
    // ... other config

    'domain' => env('APP_DOMAIN', 'localhost'),
    'cname_target' => env('CNAME_TARGET', 'proxy.localhost'),
    'a_record_ip' => env('A_RECORD_IP', '127.0.0.1'),
];

// config/services.php

return [
    // ... other services

    'ssl' => [
        'provider' => env('SSL_PROVIDER', 'caddy'),
    ],

    'cloudflare' => [
        'api_token' => env('CLOUDFLARE_API_TOKEN'),
        'zone_id' => env('CLOUDFLARE_ZONE_ID'),
    ],

    'certbot' => [
        'endpoint' => env('CERTBOT_ENDPOINT'),
        'webroot' => env('CERTBOT_WEBROOT'),
    ],
];
```

---

## Models

### Page Model

```php
// app/Models/Page.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'project_id',
        'title',
        'slug',
        'content',
        'published_content',
        'status',
        'published_at',
        'subdomain',
        'seo_settings',
        'analytics_settings',
    ];

    protected $casts = [
        'content' => 'array',
        'published_content' => 'array',
        'seo_settings' => 'array',
        'analytics_settings' => 'array',
        'published_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function customDomain(): HasOne
    {
        return $this->hasOne(CustomDomain::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(PageVersion::class)->orderByDesc('version_number');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function hasUnpublishedChanges(): bool
    {
        return $this->content !== $this->published_content;
    }
}
```

### CustomDomain Model

```php
// app/Models/CustomDomain.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomDomain extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_id',
        'domain',
        'status',
        'ssl_status',
        'verification_token',
        'verified_at',
        'ssl_provisioned_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'ssl_provisioned_at' => 'datetime',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    public function hasSsl(): bool
    {
        return $this->ssl_status === 'active';
    }
}
```

### PageVersion Model

```php
// app/Models/PageVersion.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_id',
        'content',
        'version_number',
        'change_description',
        'created_by',
    ];

    protected $casts = [
        'content' => 'array',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
```

---

## Summary

This publishing system provides:

1. **Complete Publish Flow** - Button with checklist validation and confirmation modal
2. **Flexible URL System** - Subdomains, custom slugs, and custom domains
3. **Domain Management** - Add, verify, and manage custom domains with DNS instructions
4. **DNS Verification** - Automatic CNAME/A/TXT record verification
5. **SSL Management** - Integration with Let's Encrypt via Caddy, certbot, or Cloudflare
6. **Page Rendering** - JSON to HTML conversion with SEO and analytics support
7. **Unpublish Flow** - Safely take pages offline with confirmation
8. **Version Control** - Track changes, restore previous versions, compare drafts

The system is built with Laravel backend, AlpineJS for interactivity, and TailwindCSS v4 for styling.
