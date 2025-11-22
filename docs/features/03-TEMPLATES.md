# Templates Feature Documentation

## Overview

The Templates feature provides pre-built landing page designs that users can browse, preview, and apply to quickly create professional landing pages. Users can also save their own designs as templates for reuse.

## Tech Stack
- **Backend**: Laravel 11
- **Frontend**: AlpineJS, TailwindCSS v4
- **Views**: Blade Templates

---

## Table of Contents

1. [Template Data Structure](#template-data-structure)
2. [Database Migration](#database-migration)
3. [Template Model](#template-model)
4. [Template Controller](#template-controller)
5. [Template Service](#template-service)
6. [Template Gallery View](#template-gallery-view)
7. [Template Categories](#template-categories)
8. [Template Preview Modal](#template-preview-modal)
9. [Apply Template Flow](#apply-template-flow)
10. [Save as Template](#save-as-template)
11. [Template Seeder](#template-seeder)
12. [Complete Template JSONs](#complete-template-jsons)
13. [Routes](#routes)

---

## Template Data Structure

### Schema Definition

```php
// database/migrations/2024_01_15_000001_create_templates_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('template_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('template_categories')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('preview_image')->nullable();
            $table->json('content'); // Main template structure
            $table->json('settings'); // Theme settings, fonts, colors
            $table->json('meta')->nullable(); // SEO, analytics settings
            $table->boolean('is_premium')->default(false);
            $table->boolean('is_public')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('usage_count')->default(0);
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('rating_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'is_public', 'is_active']);
            $table->index('is_featured');
        });

        Schema::create('template_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('template_tag', function (Blueprint $table) {
            $table->foreignId('template_id')->constrained()->onDelete('cascade');
            $table->foreignId('template_tag_id')->constrained('template_tags')->onDelete('cascade');
            $table->primary(['template_id', 'template_tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_tag');
        Schema::dropIfExists('template_tags');
        Schema::dropIfExists('templates');
        Schema::dropIfExists('template_categories');
    }
};
```

---

## Template Model

```php
// app/Models/Template.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Template extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'user_id',
        'name',
        'slug',
        'description',
        'thumbnail',
        'preview_image',
        'content',
        'settings',
        'meta',
        'is_premium',
        'is_public',
        'is_featured',
        'usage_count',
        'rating',
        'rating_count',
    ];

    protected $casts = [
        'content' => 'array',
        'settings' => 'array',
        'meta' => 'array',
        'is_premium' => 'boolean',
        'is_public' => 'boolean',
        'is_featured' => 'boolean',
        'rating' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(TemplateCategory::class, 'category_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(TemplateTag::class, 'template_tag');
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%");
        });
    }

    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    public function getFormattedRatingAttribute(): string
    {
        return number_format($this->rating, 1);
    }
}
```

```php
// app/Models/TemplateCategory.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TemplateCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function templates(): HasMany
    {
        return $this->hasMany(Template::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
```

---

## Template Controller

```php
// app/Http/Controllers/TemplateController.php
<?php

namespace App\Http\Controllers;

use App\Models\Template;
use App\Models\TemplateCategory;
use App\Models\LandingPage;
use App\Services\TemplateService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class TemplateController extends Controller
{
    public function __construct(
        protected TemplateService $templateService
    ) {}

    /**
     * Display template gallery
     */
    public function index(Request $request): View
    {
        $categories = TemplateCategory::active()
            ->ordered()
            ->withCount(['templates' => fn($q) => $q->public()])
            ->get();

        $templates = $this->templateService->getFilteredTemplates(
            categoryId: $request->get('category'),
            search: $request->get('search'),
            tags: $request->get('tags', []),
            sortBy: $request->get('sort', 'popular'),
            perPage: 12
        );

        return view('templates.index', compact('categories', 'templates'));
    }

    /**
     * Get templates via AJAX for filtering
     */
    public function filter(Request $request): JsonResponse
    {
        $templates = $this->templateService->getFilteredTemplates(
            categoryId: $request->get('category'),
            search: $request->get('search'),
            tags: $request->get('tags', []),
            sortBy: $request->get('sort', 'popular'),
            perPage: $request->get('per_page', 12)
        );

        $html = view('templates.partials.grid', compact('templates'))->render();

        return response()->json([
            'html' => $html,
            'hasMore' => $templates->hasMorePages(),
            'total' => $templates->total(),
        ]);
    }

    /**
     * Show template preview
     */
    public function show(Template $template): JsonResponse
    {
        $template->load(['category', 'tags', 'user']);

        return response()->json([
            'template' => [
                'id' => $template->id,
                'name' => $template->name,
                'description' => $template->description,
                'thumbnail' => $template->thumbnail,
                'preview_image' => $template->preview_image,
                'category' => $template->category->name,
                'tags' => $template->tags->pluck('name'),
                'is_premium' => $template->is_premium,
                'usage_count' => $template->usage_count,
                'rating' => $template->formatted_rating,
                'rating_count' => $template->rating_count,
                'content' => $template->content,
                'settings' => $template->settings,
            ],
        ]);
    }

    /**
     * Apply template to create new landing page
     */
    public function apply(Request $request, Template $template): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        $landingPage = $this->templateService->applyTemplate(
            template: $template,
            user: $request->user(),
            name: $request->name,
            projectId: $request->project_id
        );

        return response()->json([
            'success' => true,
            'message' => 'Template applied successfully',
            'landing_page' => [
                'id' => $landingPage->id,
                'slug' => $landingPage->slug,
                'edit_url' => route('landing-pages.edit', $landingPage),
            ],
        ]);
    }

    /**
     * Save landing page as template
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'landing_page_id' => 'required|exists:landing_pages,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category_id' => 'required|exists:template_categories,id',
            'is_public' => 'boolean',
            'tags' => 'array',
            'tags.*' => 'string|max:50',
        ]);

        $landingPage = LandingPage::findOrFail($validated['landing_page_id']);

        $this->authorize('view', $landingPage);

        $template = $this->templateService->createFromLandingPage(
            landingPage: $landingPage,
            user: $request->user(),
            data: $validated
        );

        return response()->json([
            'success' => true,
            'message' => 'Template saved successfully',
            'template' => [
                'id' => $template->id,
                'name' => $template->name,
                'slug' => $template->slug,
            ],
        ]);
    }

    /**
     * Get user's saved templates
     */
    public function userTemplates(Request $request): JsonResponse
    {
        $templates = Template::where('user_id', $request->user()->id)
            ->with('category')
            ->latest()
            ->paginate(10);

        return response()->json([
            'templates' => $templates,
        ]);
    }

    /**
     * Delete user's template
     */
    public function destroy(Template $template): JsonResponse
    {
        $this->authorize('delete', $template);

        $template->delete();

        return response()->json([
            'success' => true,
            'message' => 'Template deleted successfully',
        ]);
    }

    /**
     * Get live preview HTML
     */
    public function preview(Template $template): View
    {
        return view('templates.preview', [
            'content' => $template->content,
            'settings' => $template->settings,
        ]);
    }
}
```

---

## Template Service

```php
// app/Services/TemplateService.php
<?php

namespace App\Services;

use App\Models\Template;
use App\Models\TemplateTag;
use App\Models\LandingPage;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TemplateService
{
    /**
     * Get filtered templates with pagination
     */
    public function getFilteredTemplates(
        ?int $categoryId = null,
        ?string $search = null,
        array $tags = [],
        string $sortBy = 'popular',
        int $perPage = 12
    ): LengthAwarePaginator {
        $query = Template::query()
            ->public()
            ->with(['category', 'tags']);

        // Filter by category
        if ($categoryId) {
            $query->byCategory($categoryId);
        }

        // Search filter
        if ($search) {
            $query->search($search);
        }

        // Tags filter
        if (!empty($tags)) {
            $query->whereHas('tags', function ($q) use ($tags) {
                $q->whereIn('slug', $tags);
            });
        }

        // Sorting
        $query = match ($sortBy) {
            'popular' => $query->orderByDesc('usage_count'),
            'rating' => $query->orderByDesc('rating'),
            'newest' => $query->orderByDesc('created_at'),
            'name' => $query->orderBy('name'),
            default => $query->orderByDesc('usage_count'),
        };

        return $query->paginate($perPage);
    }

    /**
     * Apply template to create a new landing page
     */
    public function applyTemplate(
        Template $template,
        User $user,
        string $name,
        ?int $projectId = null
    ): LandingPage {
        return DB::transaction(function () use ($template, $user, $name, $projectId) {
            // Create landing page from template
            $landingPage = LandingPage::create([
                'user_id' => $user->id,
                'project_id' => $projectId,
                'name' => $name,
                'slug' => Str::slug($name) . '-' . Str::random(6),
                'content' => $this->processTemplateContent($template->content),
                'settings' => $template->settings,
                'meta' => $template->meta ?? $this->getDefaultMeta(),
                'status' => 'draft',
            ]);

            // Increment template usage
            $template->incrementUsage();

            return $landingPage;
        });
    }

    /**
     * Create template from existing landing page
     */
    public function createFromLandingPage(
        LandingPage $landingPage,
        User $user,
        array $data
    ): Template {
        return DB::transaction(function () use ($landingPage, $user, $data) {
            // Generate thumbnail
            $thumbnail = $this->generateThumbnail($landingPage);

            // Create template
            $template = Template::create([
                'category_id' => $data['category_id'],
                'user_id' => $user->id,
                'name' => $data['name'],
                'slug' => Str::slug($data['name']) . '-' . Str::random(6),
                'description' => $data['description'] ?? null,
                'thumbnail' => $thumbnail,
                'content' => $landingPage->content,
                'settings' => $landingPage->settings,
                'meta' => $landingPage->meta,
                'is_public' => $data['is_public'] ?? false,
            ]);

            // Handle tags
            if (!empty($data['tags'])) {
                $tagIds = $this->getOrCreateTags($data['tags']);
                $template->tags()->sync($tagIds);
            }

            return $template;
        });
    }

    /**
     * Process template content (replace placeholders, generate new IDs)
     */
    protected function processTemplateContent(array $content): array
    {
        return array_map(function ($section) {
            $section['id'] = Str::uuid()->toString();

            if (isset($section['components'])) {
                $section['components'] = array_map(function ($component) {
                    $component['id'] = Str::uuid()->toString();
                    return $component;
                }, $section['components']);
            }

            return $section;
        }, $content);
    }

    /**
     * Get or create tags
     */
    protected function getOrCreateTags(array $tagNames): array
    {
        $tagIds = [];

        foreach ($tagNames as $name) {
            $tag = TemplateTag::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );
            $tagIds[] = $tag->id;
        }

        return $tagIds;
    }

    /**
     * Generate thumbnail for template
     */
    protected function generateThumbnail(LandingPage $landingPage): ?string
    {
        // Implement thumbnail generation logic
        // This could use a headless browser service like Browsershot
        return null;
    }

    /**
     * Get default meta settings
     */
    protected function getDefaultMeta(): array
    {
        return [
            'title' => '',
            'description' => '',
            'keywords' => [],
            'og_image' => '',
            'favicon' => '',
        ];
    }

    /**
     * Duplicate template
     */
    public function duplicate(Template $template, User $user): Template
    {
        $newTemplate = $template->replicate([
            'usage_count',
            'rating',
            'rating_count',
        ]);

        $newTemplate->name = $template->name . ' (Copy)';
        $newTemplate->slug = Str::slug($newTemplate->name) . '-' . Str::random(6);
        $newTemplate->user_id = $user->id;
        $newTemplate->is_public = false;
        $newTemplate->save();

        // Copy tags
        $newTemplate->tags()->sync($template->tags->pluck('id'));

        return $newTemplate;
    }
}
```

---

## Template Gallery View

```blade
{{-- resources/views/templates/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Template Gallery')

@section('content')
<div x-data="templateGallery()" x-init="init()" class="min-h-screen bg-gray-50">
    {{-- Header --}}
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <h1 class="text-3xl font-bold text-gray-900">Template Gallery</h1>
            <p class="mt-2 text-gray-600">Choose from our collection of professionally designed templates</p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col lg:flex-row gap-8">
            {{-- Sidebar Filters --}}
            <aside class="w-full lg:w-64 shrink-0">
                <div class="bg-white rounded-lg shadow-sm p-6 sticky top-8">
                    {{-- Search --}}
                    <div class="mb-6">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <div class="relative">
                            <input
                                type="text"
                                id="search"
                                x-model.debounce.300ms="filters.search"
                                @input="filterTemplates()"
                                placeholder="Search templates..."
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                            >
                            <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>

                    {{-- Categories --}}
                    <div class="mb-6">
                        <h3 class="text-sm font-medium text-gray-700 mb-3">Categories</h3>
                        <div class="space-y-2">
                            <button
                                @click="filters.category = null; filterTemplates()"
                                :class="filters.category === null ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : 'text-gray-600 hover:bg-gray-50'"
                                class="w-full text-left px-3 py-2 rounded-lg text-sm border border-transparent transition-colors"
                            >
                                All Templates
                                <span class="float-right text-gray-400">{{ $templates->total() }}</span>
                            </button>
                            @foreach($categories as $category)
                            <button
                                @click="filters.category = {{ $category->id }}; filterTemplates()"
                                :class="filters.category === {{ $category->id }} ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : 'text-gray-600 hover:bg-gray-50'"
                                class="w-full text-left px-3 py-2 rounded-lg text-sm border border-transparent transition-colors"
                            >
                                <span class="inline-flex items-center gap-2">
                                    @if($category->icon)
                                        <span>{!! $category->icon !!}</span>
                                    @endif
                                    {{ $category->name }}
                                </span>
                                <span class="float-right text-gray-400">{{ $category->templates_count }}</span>
                            </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Sort --}}
                    <div>
                        <label for="sort" class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
                        <select
                            id="sort"
                            x-model="filters.sort"
                            @change="filterTemplates()"
                            class="w-full border border-gray-300 rounded-lg py-2 px-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option value="popular">Most Popular</option>
                            <option value="rating">Highest Rated</option>
                            <option value="newest">Newest First</option>
                            <option value="name">Name A-Z</option>
                        </select>
                    </div>
                </div>
            </aside>

            {{-- Main Content --}}
            <main class="flex-1">
                {{-- Results Header --}}
                <div class="flex items-center justify-between mb-6">
                    <p class="text-sm text-gray-600">
                        <span x-text="totalResults"></span> templates found
                    </p>
                    <div class="flex items-center gap-2">
                        <button
                            @click="viewMode = 'grid'"
                            :class="viewMode === 'grid' ? 'bg-gray-200' : 'hover:bg-gray-100'"
                            class="p-2 rounded-lg"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                        </button>
                        <button
                            @click="viewMode = 'list'"
                            :class="viewMode === 'list' ? 'bg-gray-200' : 'hover:bg-gray-100'"
                            class="p-2 rounded-lg"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Template Grid --}}
                <div
                    id="template-grid"
                    :class="viewMode === 'grid' ? 'grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6' : 'space-y-4'"
                >
                    @include('templates.partials.grid', ['templates' => $templates])
                </div>

                {{-- Load More --}}
                <div x-show="hasMore" class="mt-8 text-center">
                    <button
                        @click="loadMore()"
                        :disabled="loading"
                        class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
                    >
                        <span x-show="!loading">Load More</span>
                        <span x-show="loading" class="inline-flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                            </svg>
                            Loading...
                        </span>
                    </button>
                </div>
            </main>
        </div>
    </div>

    {{-- Preview Modal --}}
    @include('templates.partials.preview-modal')

    {{-- Apply Template Modal --}}
    @include('templates.partials.apply-modal')
</div>

@push('scripts')
<script>
function templateGallery() {
    return {
        filters: {
            category: null,
            search: '',
            sort: 'popular',
            tags: []
        },
        viewMode: 'grid',
        loading: false,
        hasMore: {{ $templates->hasMorePages() ? 'true' : 'false' }},
        currentPage: 1,
        totalResults: {{ $templates->total() }},
        selectedTemplate: null,
        showPreviewModal: false,
        showApplyModal: false,
        newPageName: '',
        applying: false,

        init() {
            // Initialize from URL params
            const params = new URLSearchParams(window.location.search);
            if (params.get('category')) this.filters.category = parseInt(params.get('category'));
            if (params.get('search')) this.filters.search = params.get('search');
            if (params.get('sort')) this.filters.sort = params.get('sort');
        },

        async filterTemplates() {
            this.loading = true;
            this.currentPage = 1;

            try {
                const response = await fetch('/templates/filter?' + this.buildQueryString());
                const data = await response.json();

                document.getElementById('template-grid').innerHTML = data.html;
                this.hasMore = data.hasMore;
                this.totalResults = data.total;

                // Update URL
                this.updateUrl();
            } catch (error) {
                console.error('Filter error:', error);
            } finally {
                this.loading = false;
            }
        },

        async loadMore() {
            this.loading = true;
            this.currentPage++;

            try {
                const response = await fetch('/templates/filter?' + this.buildQueryString() + '&page=' + this.currentPage);
                const data = await response.json();

                document.getElementById('template-grid').insertAdjacentHTML('beforeend', data.html);
                this.hasMore = data.hasMore;
            } catch (error) {
                console.error('Load more error:', error);
                this.currentPage--;
            } finally {
                this.loading = false;
            }
        },

        buildQueryString() {
            const params = new URLSearchParams();
            if (this.filters.category) params.set('category', this.filters.category);
            if (this.filters.search) params.set('search', this.filters.search);
            if (this.filters.sort) params.set('sort', this.filters.sort);
            this.filters.tags.forEach(tag => params.append('tags[]', tag));
            return params.toString();
        },

        updateUrl() {
            const url = new URL(window.location);
            url.search = this.buildQueryString();
            window.history.replaceState({}, '', url);
        },

        async openPreview(templateId) {
            try {
                const response = await fetch(`/templates/${templateId}`);
                this.selectedTemplate = (await response.json()).template;
                this.showPreviewModal = true;
            } catch (error) {
                console.error('Preview error:', error);
            }
        },

        openApplyModal(template) {
            this.selectedTemplate = template;
            this.newPageName = template.name;
            this.showApplyModal = true;
        },

        async applyTemplate() {
            if (!this.newPageName.trim() || this.applying) return;

            this.applying = true;

            try {
                const response = await fetch(`/templates/${this.selectedTemplate.id}/apply`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        name: this.newPageName
                    })
                });

                const data = await response.json();

                if (data.success) {
                    window.location.href = data.landing_page.edit_url;
                }
            } catch (error) {
                console.error('Apply error:', error);
            } finally {
                this.applying = false;
            }
        }
    }
}
</script>
@endpush
@endsection
```

---

## Template Grid Partial

```blade
{{-- resources/views/templates/partials/grid.blade.php --}}
@forelse($templates as $template)
<div class="bg-white rounded-lg shadow-sm overflow-hidden group hover:shadow-md transition-shadow">
    {{-- Thumbnail --}}
    <div class="relative aspect-[4/3] bg-gray-100 overflow-hidden">
        @if($template->thumbnail)
            <img
                src="{{ $template->thumbnail }}"
                alt="{{ $template->name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
            >
        @else
            <div class="w-full h-full flex items-center justify-center text-gray-400">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                </svg>
            </div>
        @endif

        {{-- Badges --}}
        <div class="absolute top-3 left-3 flex gap-2">
            @if($template->is_premium)
                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-amber-100 text-amber-800">
                    Premium
                </span>
            @endif
            @if($template->is_featured)
                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-indigo-100 text-indigo-800">
                    Featured
                </span>
            @endif
        </div>

        {{-- Hover Actions --}}
        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
            <button
                @click="openPreview({{ $template->id }})"
                class="px-4 py-2 bg-white text-gray-900 rounded-lg text-sm font-medium hover:bg-gray-100 transition-colors"
            >
                Preview
            </button>
            <button
                @click="openApplyModal({{ json_encode(['id' => $template->id, 'name' => $template->name]) }})"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors"
            >
                Use Template
            </button>
        </div>
    </div>

    {{-- Content --}}
    <div class="p-4">
        <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
                <h3 class="font-medium text-gray-900 truncate">{{ $template->name }}</h3>
                <p class="text-sm text-gray-500">{{ $template->category->name }}</p>
            </div>
            <div class="flex items-center gap-1 text-sm text-gray-500 shrink-0">
                <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                <span>{{ number_format($template->rating, 1) }}</span>
            </div>
        </div>

        {{-- Tags --}}
        @if($template->tags->isNotEmpty())
        <div class="mt-3 flex flex-wrap gap-1">
            @foreach($template->tags->take(3) as $tag)
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-600">
                    {{ $tag->name }}
                </span>
            @endforeach
            @if($template->tags->count() > 3)
                <span class="text-xs text-gray-400">+{{ $template->tags->count() - 3 }}</span>
            @endif
        </div>
        @endif

        {{-- Stats --}}
        <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
            <span>{{ number_format($template->usage_count) }} uses</span>
            <span>{{ $template->created_at->diffForHumans() }}</span>
        </div>
    </div>
</div>
@empty
<div class="col-span-full text-center py-12">
    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <h3 class="mt-2 text-sm font-medium text-gray-900">No templates found</h3>
    <p class="mt-1 text-sm text-gray-500">Try adjusting your search or filter criteria.</p>
</div>
@endforelse
```

---

## Preview Modal

```blade
{{-- resources/views/templates/partials/preview-modal.blade.php --}}
<div
    x-show="showPreviewModal"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="preview-modal-title"
    role="dialog"
    aria-modal="true"
>
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
        {{-- Backdrop --}}
        <div
            x-show="showPreviewModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="showPreviewModal = false"
            class="fixed inset-0 bg-black/60 transition-opacity"
        ></div>

        {{-- Modal Panel --}}
        <div
            x-show="showPreviewModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="relative inline-block w-full max-w-6xl overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl"
        >
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <div>
                    <h3 x-text="selectedTemplate?.name" class="text-lg font-semibold text-gray-900"></h3>
                    <p class="text-sm text-gray-500">
                        <span x-text="selectedTemplate?.category"></span>
                        <span class="mx-2">·</span>
                        <span x-text="selectedTemplate?.usage_count?.toLocaleString()"></span> uses
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <button
                        @click="openApplyModal(selectedTemplate); showPreviewModal = false"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors"
                    >
                        Use This Template
                    </button>
                    <button
                        @click="showPreviewModal = false"
                        class="p-2 text-gray-400 hover:text-gray-500 rounded-lg hover:bg-gray-100"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Preview Content --}}
            <div class="flex">
                {{-- Preview Frame --}}
                <div class="flex-1 bg-gray-100 p-4">
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden" style="height: 600px;">
                        <iframe
                            x-bind:src="selectedTemplate ? `/templates/${selectedTemplate.id}/preview` : ''"
                            class="w-full h-full border-0"
                            title="Template Preview"
                        ></iframe>
                    </div>
                </div>

                {{-- Sidebar Info --}}
                <div class="w-80 border-l border-gray-200 p-6 overflow-y-auto" style="max-height: 680px;">
                    {{-- Description --}}
                    <div class="mb-6">
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Description</h4>
                        <p x-text="selectedTemplate?.description || 'No description provided.'" class="text-sm text-gray-600"></p>
                    </div>

                    {{-- Rating --}}
                    <div class="mb-6">
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Rating</h4>
                        <div class="flex items-center gap-2">
                            <div class="flex">
                                <template x-for="i in 5">
                                    <svg
                                        :class="i <= Math.round(selectedTemplate?.rating || 0) ? 'text-amber-400' : 'text-gray-200'"
                                        class="w-5 h-5"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                </template>
                            </div>
                            <span class="text-sm text-gray-600">
                                <span x-text="selectedTemplate?.rating"></span>
                                (<span x-text="selectedTemplate?.rating_count"></span> reviews)
                            </span>
                        </div>
                    </div>

                    {{-- Tags --}}
                    <div class="mb-6">
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Tags</h4>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="tag in selectedTemplate?.tags || []">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800" x-text="tag"></span>
                            </template>
                        </div>
                    </div>

                    {{-- Features --}}
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Includes</h4>
                        <ul class="space-y-2">
                            <li class="flex items-center gap-2 text-sm text-gray-600">
                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Responsive design
                            </li>
                            <li class="flex items-center gap-2 text-sm text-gray-600">
                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                SEO optimized
                            </li>
                            <li class="flex items-center gap-2 text-sm text-gray-600">
                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Customizable sections
                            </li>
                            <li class="flex items-center gap-2 text-sm text-gray-600">
                                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Fast loading
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
```

---

## Apply Template Modal

```blade
{{-- resources/views/templates/partials/apply-modal.blade.php --}}
<div
    x-show="showApplyModal"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="apply-modal-title"
    role="dialog"
    aria-modal="true"
>
    <div class="flex items-center justify-center min-h-screen px-4">
        {{-- Backdrop --}}
        <div
            x-show="showApplyModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="showApplyModal = false"
            class="fixed inset-0 bg-black/50 transition-opacity"
        ></div>

        {{-- Modal Panel --}}
        <div
            x-show="showApplyModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative w-full max-w-md p-6 bg-white rounded-lg shadow-xl"
        >
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Create New Landing Page</h3>

            <form @submit.prevent="applyTemplate()">
                <div class="mb-4">
                    <label for="page-name" class="block text-sm font-medium text-gray-700 mb-1">
                        Page Name
                    </label>
                    <input
                        type="text"
                        id="page-name"
                        x-model="newPageName"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="My Landing Page"
                    >
                </div>

                <div class="flex justify-end gap-3">
                    <button
                        type="button"
                        @click="showApplyModal = false"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="applying || !newPageName.trim()"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 disabled:opacity-50"
                    >
                        <span x-show="!applying">Create Page</span>
                        <span x-show="applying" class="inline-flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                            </svg>
                            Creating...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
```

---

## Save as Template Modal

```blade
{{-- resources/views/templates/partials/save-modal.blade.php --}}
<div
    x-data="saveTemplateModal()"
    x-show="$store.saveTemplate.show"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
>
    <div class="flex items-center justify-center min-h-screen px-4">
        <div
            x-show="$store.saveTemplate.show"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="$store.saveTemplate.show = false"
            class="fixed inset-0 bg-black/50"
        ></div>

        <div
            x-show="$store.saveTemplate.show"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative w-full max-w-lg p-6 bg-white rounded-lg shadow-xl"
        >
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Save as Template</h3>

            <form @submit.prevent="save()">
                {{-- Template Name --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Template Name</label>
                    <input
                        type="text"
                        x-model="form.name"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        placeholder="My Template"
                    >
                </div>

                {{-- Description --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea
                        x-model="form.description"
                        rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        placeholder="Describe your template..."
                    ></textarea>
                </div>

                {{-- Category --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select
                        x-model="form.category_id"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="">Select category...</option>
                        @foreach(\App\Models\TemplateCategory::active()->ordered()->get() as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Tags --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tags</label>
                    <input
                        type="text"
                        x-model="tagInput"
                        @keydown.enter.prevent="addTag()"
                        @keydown.comma.prevent="addTag()"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                        placeholder="Add tags (press Enter)"
                    >
                    <div class="flex flex-wrap gap-2 mt-2">
                        <template x-for="(tag, index) in form.tags" :key="index">
                            <span class="inline-flex items-center gap-1 px-2 py-1 bg-gray-100 text-sm rounded">
                                <span x-text="tag"></span>
                                <button type="button" @click="form.tags.splice(index, 1)" class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </span>
                        </template>
                    </div>
                </div>

                {{-- Public Toggle --}}
                <div class="mb-6">
                    <label class="flex items-center gap-3">
                        <input
                            type="checkbox"
                            x-model="form.is_public"
                            class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                        >
                        <span class="text-sm text-gray-700">Make this template public</span>
                    </label>
                    <p class="mt-1 text-xs text-gray-500 ml-7">Public templates can be used by other users</p>
                </div>

                {{-- Actions --}}
                <div class="flex justify-end gap-3">
                    <button
                        type="button"
                        @click="$store.saveTemplate.show = false"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="saving"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 disabled:opacity-50"
                    >
                        <span x-show="!saving">Save Template</span>
                        <span x-show="saving">Saving...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function saveTemplateModal() {
    return {
        form: {
            name: '',
            description: '',
            category_id: '',
            tags: [],
            is_public: false
        },
        tagInput: '',
        saving: false,

        addTag() {
            const tag = this.tagInput.trim();
            if (tag && !this.form.tags.includes(tag)) {
                this.form.tags.push(tag);
            }
            this.tagInput = '';
        },

        async save() {
            this.saving = true;

            try {
                const response = await fetch('/templates', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        ...this.form,
                        landing_page_id: Alpine.store('saveTemplate').landingPageId
                    })
                });

                const data = await response.json();

                if (data.success) {
                    Alpine.store('saveTemplate').show = false;
                    // Show success notification
                    window.dispatchEvent(new CustomEvent('notify', {
                        detail: { message: 'Template saved successfully', type: 'success' }
                    }));
                }
            } catch (error) {
                console.error('Save error:', error);
            } finally {
                this.saving = false;
            }
        }
    }
}

// Alpine store for save template modal
document.addEventListener('alpine:init', () => {
    Alpine.store('saveTemplate', {
        show: false,
        landingPageId: null
    });
});
</script>
```

---

## Template Seeder

```php
// database/seeders/TemplateSeeder.php
<?php

namespace Database\Seeders;

use App\Models\Template;
use App\Models\TemplateCategory;
use App\Models\TemplateTag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TemplateSeeder extends Seeder
{
    public function run(): void
    {
        // Create Categories
        $categories = [
            ['name' => 'SaaS', 'slug' => 'saas', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/></svg>', 'sort_order' => 1],
            ['name' => 'Mobile App', 'slug' => 'mobile-app', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>', 'sort_order' => 2],
            ['name' => 'Coming Soon', 'slug' => 'coming-soon', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>', 'sort_order' => 3],
            ['name' => 'Lead Generation', 'slug' => 'lead-generation', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>', 'sort_order' => 4],
            ['name' => 'E-commerce', 'slug' => 'ecommerce', 'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>', 'sort_order' => 5],
        ];

        foreach ($categories as $category) {
            TemplateCategory::create($category);
        }

        // Create Tags
        $tags = ['modern', 'minimal', 'bold', 'professional', 'creative', 'dark', 'light', 'gradient', 'animated', 'responsive'];
        foreach ($tags as $tag) {
            TemplateTag::create(['name' => $tag, 'slug' => Str::slug($tag)]);
        }

        // Create Templates
        $templates = $this->getTemplates();

        foreach ($templates as $templateData) {
            $tagNames = $templateData['tags'] ?? [];
            unset($templateData['tags']);

            $template = Template::create($templateData);

            // Attach tags
            if (!empty($tagNames)) {
                $tagIds = TemplateTag::whereIn('name', $tagNames)->pluck('id');
                $template->tags()->sync($tagIds);
            }
        }
    }

    protected function getTemplates(): array
    {
        return [
            // 1. SaaS Landing Page Template
            [
                'category_id' => 1,
                'name' => 'SaaS Pro',
                'slug' => 'saas-pro',
                'description' => 'Professional SaaS landing page with hero, features, pricing, testimonials, and CTA sections. Perfect for B2B software products.',
                'thumbnail' => '/images/templates/saas-pro-thumb.jpg',
                'preview_image' => '/images/templates/saas-pro-preview.jpg',
                'is_featured' => true,
                'usage_count' => 1250,
                'rating' => 4.8,
                'rating_count' => 156,
                'tags' => ['modern', 'professional', 'gradient'],
                'content' => [
                    [
                        'id' => 'hero-section',
                        'type' => 'hero',
                        'variant' => 'centered',
                        'components' => [
                            [
                                'id' => 'hero-badge',
                                'type' => 'badge',
                                'content' => 'New Release v2.0',
                                'style' => ['background' => 'indigo-100', 'color' => 'indigo-700']
                            ],
                            [
                                'id' => 'hero-heading',
                                'type' => 'heading',
                                'level' => 1,
                                'content' => 'Build better products with our all-in-one platform',
                                'style' => ['fontSize' => '4xl', 'fontWeight' => 'bold', 'color' => 'gray-900']
                            ],
                            [
                                'id' => 'hero-subheading',
                                'type' => 'text',
                                'content' => 'Streamline your workflow, collaborate with your team, and ship faster. Join thousands of teams already using our platform.',
                                'style' => ['fontSize' => 'xl', 'color' => 'gray-600', 'maxWidth' => '2xl']
                            ],
                            [
                                'id' => 'hero-cta-group',
                                'type' => 'button-group',
                                'buttons' => [
                                    ['text' => 'Start Free Trial', 'variant' => 'primary', 'size' => 'lg', 'link' => '#signup'],
                                    ['text' => 'View Demo', 'variant' => 'outline', 'size' => 'lg', 'link' => '#demo']
                                ]
                            ],
                            [
                                'id' => 'hero-image',
                                'type' => 'image',
                                'src' => '/images/placeholder/dashboard.png',
                                'alt' => 'Product Dashboard',
                                'style' => ['shadow' => '2xl', 'rounded' => 'lg', 'border' => true]
                            ]
                        ],
                        'settings' => [
                            'padding' => ['top' => 24, 'bottom' => 24],
                            'background' => 'gradient-to-b from-indigo-50 to-white'
                        ]
                    ],
                    [
                        'id' => 'logos-section',
                        'type' => 'logo-cloud',
                        'components' => [
                            [
                                'id' => 'logos-heading',
                                'type' => 'text',
                                'content' => 'Trusted by leading companies worldwide',
                                'style' => ['fontSize' => 'sm', 'color' => 'gray-500', 'textAlign' => 'center']
                            ],
                            [
                                'id' => 'logos-grid',
                                'type' => 'logo-grid',
                                'logos' => [
                                    ['name' => 'Company 1', 'src' => '/images/logos/logo1.svg'],
                                    ['name' => 'Company 2', 'src' => '/images/logos/logo2.svg'],
                                    ['name' => 'Company 3', 'src' => '/images/logos/logo3.svg'],
                                    ['name' => 'Company 4', 'src' => '/images/logos/logo4.svg'],
                                    ['name' => 'Company 5', 'src' => '/images/logos/logo5.svg']
                                ]
                            ]
                        ],
                        'settings' => ['padding' => ['top' => 16, 'bottom' => 16], 'background' => 'white']
                    ],
                    [
                        'id' => 'features-section',
                        'type' => 'features',
                        'variant' => 'grid',
                        'components' => [
                            [
                                'id' => 'features-heading',
                                'type' => 'section-header',
                                'title' => 'Everything you need to scale',
                                'subtitle' => 'Our platform provides all the tools you need to build, deploy, and grow your product.',
                                'alignment' => 'center'
                            ],
                            [
                                'id' => 'features-grid',
                                'type' => 'feature-grid',
                                'columns' => 3,
                                'features' => [
                                    [
                                        'icon' => 'lightning-bolt',
                                        'title' => 'Lightning Fast',
                                        'description' => 'Built for speed with edge computing and global CDN distribution.'
                                    ],
                                    [
                                        'icon' => 'shield-check',
                                        'title' => 'Enterprise Security',
                                        'description' => 'SOC 2 compliant with end-to-end encryption and SSO support.'
                                    ],
                                    [
                                        'icon' => 'chart-bar',
                                        'title' => 'Advanced Analytics',
                                        'description' => 'Real-time insights and custom dashboards for data-driven decisions.'
                                    ],
                                    [
                                        'icon' => 'puzzle',
                                        'title' => 'Integrations',
                                        'description' => 'Connect with 100+ tools including Slack, GitHub, and Jira.'
                                    ],
                                    [
                                        'icon' => 'users',
                                        'title' => 'Team Collaboration',
                                        'description' => 'Real-time collaboration with comments, mentions, and sharing.'
                                    ],
                                    [
                                        'icon' => 'code',
                                        'title' => 'API Access',
                                        'description' => 'Full REST API with webhooks and SDKs for custom integrations.'
                                    ]
                                ]
                            ]
                        ],
                        'settings' => ['padding' => ['top' => 24, 'bottom' => 24], 'background' => 'gray-50']
                    ],
                    [
                        'id' => 'pricing-section',
                        'type' => 'pricing',
                        'variant' => 'three-tier',
                        'components' => [
                            [
                                'id' => 'pricing-header',
                                'type' => 'section-header',
                                'title' => 'Simple, transparent pricing',
                                'subtitle' => 'Choose the plan that fits your needs. Upgrade or downgrade at any time.',
                                'alignment' => 'center'
                            ],
                            [
                                'id' => 'pricing-toggle',
                                'type' => 'pricing-toggle',
                                'options' => ['monthly', 'annual'],
                                'discount' => '20%'
                            ],
                            [
                                'id' => 'pricing-cards',
                                'type' => 'pricing-grid',
                                'plans' => [
                                    [
                                        'name' => 'Starter',
                                        'description' => 'Perfect for side projects',
                                        'monthlyPrice' => 29,
                                        'annualPrice' => 290,
                                        'features' => ['5 team members', '10GB storage', 'Basic analytics', 'Email support'],
                                        'cta' => ['text' => 'Get Started', 'variant' => 'outline']
                                    ],
                                    [
                                        'name' => 'Pro',
                                        'description' => 'For growing teams',
                                        'monthlyPrice' => 79,
                                        'annualPrice' => 790,
                                        'featured' => true,
                                        'features' => ['Unlimited members', '100GB storage', 'Advanced analytics', 'Priority support', 'Custom integrations', 'API access'],
                                        'cta' => ['text' => 'Start Free Trial', 'variant' => 'primary']
                                    ],
                                    [
                                        'name' => 'Enterprise',
                                        'description' => 'For large organizations',
                                        'monthlyPrice' => 199,
                                        'annualPrice' => 1990,
                                        'features' => ['Everything in Pro', 'Unlimited storage', 'SSO/SAML', 'Dedicated support', 'Custom contracts', 'SLA guarantee'],
                                        'cta' => ['text' => 'Contact Sales', 'variant' => 'outline']
                                    ]
                                ]
                            ]
                        ],
                        'settings' => ['padding' => ['top' => 24, 'bottom' => 24], 'background' => 'white']
                    ],
                    [
                        'id' => 'testimonials-section',
                        'type' => 'testimonials',
                        'variant' => 'carousel',
                        'components' => [
                            [
                                'id' => 'testimonials-header',
                                'type' => 'section-header',
                                'title' => 'Loved by teams worldwide',
                                'alignment' => 'center'
                            ],
                            [
                                'id' => 'testimonials-list',
                                'type' => 'testimonial-carousel',
                                'testimonials' => [
                                    [
                                        'quote' => 'This platform has completely transformed how we build and ship products. The speed and reliability are unmatched.',
                                        'author' => 'Sarah Chen',
                                        'role' => 'CTO',
                                        'company' => 'TechCorp',
                                        'avatar' => '/images/avatars/sarah.jpg'
                                    ],
                                    [
                                        'quote' => 'We reduced our deployment time by 80% and improved team collaboration significantly. Highly recommended!',
                                        'author' => 'Michael Rodriguez',
                                        'role' => 'Engineering Lead',
                                        'company' => 'StartupXYZ',
                                        'avatar' => '/images/avatars/michael.jpg'
                                    ],
                                    [
                                        'quote' => 'The analytics and insights have helped us make better decisions and grow faster than ever before.',
                                        'author' => 'Emily Watson',
                                        'role' => 'Product Manager',
                                        'company' => 'GrowthCo',
                                        'avatar' => '/images/avatars/emily.jpg'
                                    ]
                                ]
                            ]
                        ],
                        'settings' => ['padding' => ['top' => 24, 'bottom' => 24], 'background' => 'indigo-50']
                    ],
                    [
                        'id' => 'cta-section',
                        'type' => 'cta',
                        'variant' => 'centered',
                        'components' => [
                            [
                                'id' => 'cta-heading',
                                'type' => 'heading',
                                'level' => 2,
                                'content' => 'Ready to get started?',
                                'style' => ['fontSize' => '3xl', 'fontWeight' => 'bold', 'color' => 'white']
                            ],
                            [
                                'id' => 'cta-subheading',
                                'type' => 'text',
                                'content' => 'Join thousands of teams already using our platform. Start your free trial today.',
                                'style' => ['fontSize' => 'lg', 'color' => 'indigo-100']
                            ],
                            [
                                'id' => 'cta-buttons',
                                'type' => 'button-group',
                                'buttons' => [
                                    ['text' => 'Start Free Trial', 'variant' => 'white', 'size' => 'lg', 'link' => '#signup'],
                                    ['text' => 'Contact Sales', 'variant' => 'outline-white', 'size' => 'lg', 'link' => '#contact']
                                ]
                            ]
                        ],
                        'settings' => ['padding' => ['top' => 20, 'bottom' => 20], 'background' => 'gradient-to-r from-indigo-600 to-purple-600']
                    ],
                    [
                        'id' => 'footer-section',
                        'type' => 'footer',
                        'variant' => 'multi-column',
                        'components' => [
                            [
                                'id' => 'footer-content',
                                'type' => 'footer-content',
                                'logo' => '/images/logo.svg',
                                'description' => 'Building the future of product development.',
                                'columns' => [
                                    [
                                        'title' => 'Product',
                                        'links' => [
                                            ['text' => 'Features', 'url' => '#features'],
                                            ['text' => 'Pricing', 'url' => '#pricing'],
                                            ['text' => 'Integrations', 'url' => '#integrations'],
                                            ['text' => 'Changelog', 'url' => '#changelog']
                                        ]
                                    ],
                                    [
                                        'title' => 'Company',
                                        'links' => [
                                            ['text' => 'About', 'url' => '#about'],
                                            ['text' => 'Blog', 'url' => '#blog'],
                                            ['text' => 'Careers', 'url' => '#careers'],
                                            ['text' => 'Contact', 'url' => '#contact']
                                        ]
                                    ],
                                    [
                                        'title' => 'Legal',
                                        'links' => [
                                            ['text' => 'Privacy', 'url' => '#privacy'],
                                            ['text' => 'Terms', 'url' => '#terms'],
                                            ['text' => 'Security', 'url' => '#security']
                                        ]
                                    ]
                                ],
                                'social' => [
                                    ['platform' => 'twitter', 'url' => '#'],
                                    ['platform' => 'github', 'url' => '#'],
                                    ['platform' => 'linkedin', 'url' => '#']
                                ],
                                'copyright' => '2024 SaaS Company. All rights reserved.'
                            ]
                        ],
                        'settings' => ['padding' => ['top' => 16, 'bottom' => 8], 'background' => 'gray-900']
                    ]
                ],
                'settings' => [
                    'theme' => [
                        'primaryColor' => '#4F46E5',
                        'secondaryColor' => '#7C3AED',
                        'fontFamily' => 'Inter',
                        'headingFont' => 'Inter',
                        'borderRadius' => 'lg'
                    ],
                    'global' => [
                        'maxWidth' => '7xl',
                        'containerPadding' => 'px-4 sm:px-6 lg:px-8'
                    ]
                ],
                'meta' => [
                    'title' => 'SaaS Platform - Build Better Products',
                    'description' => 'All-in-one platform for building, deploying, and scaling your products.',
                    'keywords' => ['saas', 'platform', 'productivity', 'collaboration']
                ]
            ],

            // 2. App Download Template
            [
                'category_id' => 2,
                'name' => 'App Launch',
                'slug' => 'app-launch',
                'description' => 'Mobile app landing page with device mockups, feature highlights, download buttons, and app store badges.',
                'thumbnail' => '/images/templates/app-launch-thumb.jpg',
                'preview_image' => '/images/templates/app-launch-preview.jpg',
                'is_featured' => true,
                'usage_count' => 890,
                'rating' => 4.7,
                'rating_count' => 98,
                'tags' => ['modern', 'minimal', 'animated'],
                'content' => [
                    [
                        'id' => 'hero-section',
                        'type' => 'hero',
                        'variant' => 'split',
                        'components' => [
                            [
                                'id' => 'hero-content',
                                'type' => 'hero-content',
                                'heading' => 'Your life, organized in one app',
                                'subheading' => 'Track habits, manage tasks, and achieve your goals with our beautifully designed mobile app.',
                                'buttons' => [
                                    ['type' => 'app-store', 'url' => '#', 'platform' => 'ios'],
                                    ['type' => 'app-store', 'url' => '#', 'platform' => 'android']
                                ]
                            ],
                            [
                                'id' => 'hero-device',
                                'type' => 'device-mockup',
                                'device' => 'iphone',
                                'screen' => '/images/placeholder/app-screen-1.png',
                                'animation' => 'float'
                            ]
                        ],
                        'settings' => ['padding' => ['top' => 20, 'bottom' => 20], 'background' => 'gradient-to-br from-violet-500 to-purple-600']
                    ],
                    [
                        'id' => 'stats-section',
                        'type' => 'stats',
                        'components' => [
                            [
                                'id' => 'stats-grid',
                                'type' => 'stat-grid',
                                'stats' => [
                                    ['value' => '500K+', 'label' => 'Downloads'],
                                    ['value' => '4.9', 'label' => 'App Store Rating'],
                                    ['value' => '50+', 'label' => 'Countries'],
                                    ['value' => '99.9%', 'label' => 'Uptime']
                                ]
                            ]
                        ],
                        'settings' => ['padding' => ['top' => 12, 'bottom' => 12], 'background' => 'white']
                    ],
                    [
                        'id' => 'features-section',
                        'type' => 'features',
                        'variant' => 'alternating',
                        'components' => [
                            [
                                'id' => 'features-header',
                                'type' => 'section-header',
                                'title' => 'Powerful features, simple interface',
                                'alignment' => 'center'
                            ],
                            [
                                'id' => 'feature-1',
                                'type' => 'feature-row',
                                'alignment' => 'left',
                                'image' => '/images/placeholder/app-screen-2.png',
                                'title' => 'Smart Task Management',
                                'description' => 'Organize tasks with intelligent categorization, priorities, and due dates. Never miss a deadline again.',
                                'features' => ['Smart reminders', 'Recurring tasks', 'Subtasks & checklists']
                            ],
                            [
                                'id' => 'feature-2',
                                'type' => 'feature-row',
                                'alignment' => 'right',
                                'image' => '/images/placeholder/app-screen-3.png',
                                'title' => 'Habit Tracking',
                                'description' => 'Build positive habits with streak tracking, statistics, and motivational insights.',
                                'features' => ['Daily streaks', 'Progress charts', 'Custom habits']
                            ],
                            [
                                'id' => 'feature-3',
                                'type' => 'feature-row',
                                'alignment' => 'left',
                                'image' => '/images/placeholder/app-screen-4.png',
                                'title' => 'Goal Setting',
                                'description' => 'Set meaningful goals and track your progress with visual milestones and achievements.',
                                'features' => ['Milestone tracking', 'Progress visualization', 'Achievement badges']
                            ]
                        ],
                        'settings' => ['padding' => ['top' => 24, 'bottom' => 24], 'background' => 'gray-50']
                    ],
                    [
                        'id' => 'screenshots-section',
                        'type' => 'gallery',
                        'variant' => 'carousel',
                        'components' => [
                            [
                                'id' => 'screenshots-header',
                                'type' => 'section-header',
                                'title' => 'See it in action',
                                'alignment' => 'center'
                            ],
                            [
                                'id' => 'screenshots-carousel',
                                'type' => 'screenshot-carousel',
                                'screenshots' => [
                                    ['src' => '/images/placeholder/screen-1.png', 'caption' => 'Dashboard'],
                                    ['src' => '/images/placeholder/screen-2.png', 'caption' => 'Tasks'],
                                    ['src' => '/images/placeholder/screen-3.png', 'caption' => 'Habits'],
                                    ['src' => '/images/placeholder/screen-4.png', 'caption' => 'Goals'],
                                    ['src' => '/images/placeholder/screen-5.png', 'caption' => 'Analytics']
                                ]
                            ]
                        ],
                        'settings' => ['padding' => ['top' => 20, 'bottom' => 20], 'background' => 'white']
                    ],
                    [
                        'id' => 'reviews-section',
                        'type' => 'reviews',
                        'components' => [
                            [
                                'id' => 'reviews-header',
                                'type' => 'section-header',
                                'title' => 'What users are saying',
                                'alignment' => 'center'
                            ],
                            [
                                'id' => 'reviews-grid',
                                'type' => 'review-grid',
                                'reviews' => [
                                    ['rating' => 5, 'text' => 'This app changed how I organize my day. So intuitive!', 'author' => 'Jessica M.', 'source' => 'App Store'],
                                    ['rating' => 5, 'text' => 'Finally an app that helps me stick to my habits. Love the streaks!', 'author' => 'David K.', 'source' => 'Google Play'],
                                    ['rating' => 5, 'text' => 'Beautiful design and super useful features. Highly recommend!', 'author' => 'Anna L.', 'source' => 'App Store']
                                ]
                            ]
                        ],
                        'settings' => ['padding' => ['top' => 20, 'bottom' => 20], 'background' => 'violet-50']
                    ],
                    [
                        'id' => 'download-section',
                        'type' => 'cta',
                        'variant' => 'centered',
                        'components' => [
                            [
                                'id' => 'download-heading',
                                'type' => 'heading',
                                'level' => 2,
                                'content' => 'Download now and start organizing',
                                'style' => ['fontSize' => '3xl', 'fontWeight' => 'bold', 'color' => 'gray-900']
                            ],
                            [
                                'id' => 'download-subheading',
                                'type' => 'text',
                                'content' => 'Available on iOS and Android. Free to download with premium features.',
                                'style' => ['color' => 'gray-600']
                            ],
                            [
                                'id' => 'download-buttons',
                                'type' => 'app-store-buttons',
                                'buttons' => [
                                    ['platform' => 'ios', 'url' => '#'],
                                    ['platform' => 'android', 'url' => '#']
                                ]
                            ]
                        ],
                        'settings' => ['padding' => ['top' => 20, 'bottom' => 20], 'background' => 'white']
                    ]
                ],
                'settings' => [
                    'theme' => [
                        'primaryColor' => '#8B5CF6',
                        'secondaryColor' => '#A78BFA',
                        'fontFamily' => 'Plus Jakarta Sans',
                        'borderRadius' => 'xl'
                    ]
                ],
                'meta' => [
                    'title' => 'App Name - Your Life, Organized',
                    'description' => 'Track habits, manage tasks, and achieve your goals with our mobile app.'
                ]
            ],

            // 3. Coming Soon Template
            [
                'category_id' => 3,
                'name' => 'Countdown Launch',
                'slug' => 'countdown-launch',
                'description' => 'Coming soon page with countdown timer, email signup, and social links. Perfect for product launches.',
                'thumbnail' => '/images/templates/countdown-thumb.jpg',
                'preview_image' => '/images/templates/countdown-preview.jpg',
                'usage_count' => 650,
                'rating' => 4.6,
                'rating_count' => 72,
                'tags' => ['minimal', 'dark', 'animated'],
                'content' => [
                    [
                        'id' => 'main-section',
                        'type' => 'coming-soon',
                        'variant' => 'centered',
                        'components' => [
                            [
                                'id' => 'logo',
                                'type' => 'image',
                                'src' => '/images/logo-white.svg',
                                'alt' => 'Logo',
                                'style' => ['height' => '40px']
                            ],
                            [
                                'id' => 'heading',
                                'type' => 'heading',
                                'level' => 1,
                                'content' => 'Something amazing is coming',
                                'style' => ['fontSize' => '5xl', 'fontWeight' => 'bold', 'color' => 'white']
                            ],
                            [
                                'id' => 'subheading',
                                'type' => 'text',
                                'content' => 'We are working hard to bring you something incredible. Stay tuned!',
                                'style' => ['fontSize' => 'xl', 'color' => 'gray-300']
                            ],
                            [
                                'id' => 'countdown',
                                'type' => 'countdown-timer',
                                'targetDate' => '2024-06-01T00:00:00',
                                'style' => [
                                    'numberSize' => '4xl',
                                    'numberColor' => 'white',
                                    'labelColor' => 'gray-400'
                                ]
                            ],
                            [
                                'id' => 'email-signup',
                                'type' => 'email-capture',
                                'placeholder' => 'Enter your email',
                                'buttonText' => 'Notify Me',
                                'style' => [
                                    'inputBackground' => 'white/10',
                                    'inputColor' => 'white',
                                    'buttonBackground' => 'white',
                                    'buttonColor' => 'gray-900'
                                ]
                            ],
                            [
                                'id' => 'social-links',
                                'type' => 'social-icons',
                                'links' => [
                                    ['platform' => 'twitter', 'url' => '#'],
                                    ['platform' => 'instagram', 'url' => '#'],
                                    ['platform' => 'facebook', 'url' => '#']
                                ],
                                'style' => ['color' => 'gray-400', 'hoverColor' => 'white']
                            ]
                        ],
                        'settings' => [
                            'minHeight' => '100vh',
                            'background' => 'gradient-to-br from-gray-900 via-purple-900 to-violet-900',
                            'backgroundImage' => '/images/backgrounds/particles.svg'
                        ]
                    ]
                ],
                'settings' => [
                    'theme' => [
                        'primaryColor' => '#8B5CF6',
                        'fontFamily' => 'Space Grotesk',
                        'borderRadius' => 'lg'
                    ],
                    'global' => [
                        'hideNavigation' => true,
                        'hideFooter' => true
                    ]
                ],
                'meta' => [
                    'title' => 'Coming Soon - Something Amazing',
                    'description' => 'We are launching soon. Sign up to be the first to know!'
                ]
            ],

            // 4. Lead Capture Template
            [
                'category_id' => 4,
                'name' => 'Lead Magnet Pro',
                'slug' => 'lead-magnet-pro',
                'description' => 'High-converting lead capture page with ebook/guide offer, benefits list, and optimized form.',
                'thumbnail' => '/images/templates/lead-magnet-thumb.jpg',
                'preview_image' => '/images/templates/lead-magnet-preview.jpg',
                'is_featured' => true,
                'usage_count' => 1100,
                'rating' => 4.9,
                'rating_count' => 134,
                'tags' => ['professional', 'bold', 'responsive'],
                'content' => [
                    [
                        'id' => 'hero-section',
                        'type' => 'hero',
                        'variant' => 'split-form',
                        'components' => [
                            [
                                'id' => 'hero-content',
                                'type' => 'container',
                                'children' => [
                                    [
                                        'id' => 'badge',
                                        'type' => 'badge',
                                        'content' => 'FREE GUIDE',
                                        'style' => ['background' => 'emerald-100', 'color' => 'emerald-700']
                                    ],
                                    [
                                        'id' => 'heading',
                                        'type' => 'heading',
                                        'level' => 1,
                                        'content' => 'The Ultimate Guide to Growing Your Business in 2024',
                                        'style' => ['fontSize' => '4xl', 'fontWeight' => 'bold', 'color' => 'gray-900']
                                    ],
                                    [
                                        'id' => 'description',
                                        'type' => 'text',
                                        'content' => 'Download our comprehensive 50-page guide packed with actionable strategies, case studies, and templates to scale your business.',
                                        'style' => ['fontSize' => 'lg', 'color' => 'gray-600']
                                    ],
                                    [
                                        'id' => 'benefits',
                                        'type' => 'checklist',
                                        'items' => [
                                            '10 proven growth strategies',
                                            'Real-world case studies',
                                            'Ready-to-use templates',
                                            'Step-by-step implementation guide',
                                            'Bonus: Growth metrics dashboard'
                                        ],
                                        'style' => ['iconColor' => 'emerald-500']
                                    ],
                                    [
                                        'id' => 'social-proof',
                                        'type' => 'social-proof',
                                        'text' => 'Join 10,000+ business owners who downloaded this guide',
                                        'avatars' => [
                                            '/images/avatars/1.jpg',
                                            '/images/avatars/2.jpg',
                                            '/images/avatars/3.jpg',
                                            '/images/avatars/4.jpg'
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'id' => 'lead-form',
                                'type' => 'lead-capture-form',
                                'title' => 'Get Your Free Copy',
                                'fields' => [
                                    ['name' => 'first_name', 'label' => 'First Name', 'type' => 'text', 'required' => true],
                                    ['name' => 'email', 'label' => 'Email Address', 'type' => 'email', 'required' => true],
                                    ['name' => 'company', 'label' => 'Company Name', 'type' => 'text', 'required' => false]
                                ],
                                'submitText' => 'Download Free Guide',
                                'privacyText' => 'We respect your privacy. Unsubscribe at any time.',
                                'style' => [
                                    'background' => 'white',
                                    'shadow' => 'xl',
                                    'buttonBackground' => 'emerald-600'
                                ]
                            ]
                        ],
                        'settings' => ['padding' => ['top' => 20, 'bottom' => 20], 'background' => 'gradient-to-br from-emerald-50 to-teal-50']
                    ],
                    [
                        'id' => 'preview-section',
                        'type' => 'content-preview',
                        'components' => [
                            [
                                'id' => 'preview-header',
                                'type' => 'section-header',
                                'title' => 'What\'s inside the guide',
                                'alignment' => 'center'
                            ],
                            [
                                'id' => 'preview-image',
                                'type' => 'image',
                                'src' => '/images/placeholder/ebook-preview.png',
                                'alt' => 'Guide Preview',
                                'style' => ['shadow' => '2xl', 'rounded' => 'lg']
                            ],
                            [
                                'id' => 'chapter-list',
                                'type' => 'chapter-grid',
                                'chapters' => [
                                    ['number' => '01', 'title' => 'Market Analysis', 'description' => 'Understanding your market and identifying opportunities'],
                                    ['number' => '02', 'title' => 'Growth Framework', 'description' => 'Our proven 5-step framework for sustainable growth'],
                                    ['number' => '03', 'title' => 'Customer Acquisition', 'description' => 'Strategies to attract and convert more customers'],
                                    ['number' => '04', 'title' => 'Scaling Operations', 'description' => 'Systems and processes for efficient scaling'],
                                    ['number' => '05', 'title' => 'Metrics & KPIs', 'description' => 'Track what matters for growth'],
                                    ['number' => '06', 'title' => 'Case Studies', 'description' => 'Real examples from successful businesses']
                                ]
                            ]
                        ],
                        'settings' => ['padding' => ['top' => 24, 'bottom' => 24], 'background' => 'white']
                    ],
                    [
                        'id' => 'author-section',
                        'type' => 'author',
                        'components' => [
                            [
                                'id' => 'author-card',
                                'type' => 'author-bio',
                                'image' => '/images/avatars/author.jpg',
                                'name' => 'John Smith',
                                'title' => 'Founder & Growth Strategist',
                                'bio' => 'John has helped over 500 businesses scale from startup to 7-figure revenue. His strategies have been featured in Forbes, Inc, and Entrepreneur.',
                                'credentials' => [
                                    '15+ years experience',
                                    '500+ businesses helped',
                                    'Featured in Forbes, Inc'
                                ]
                            ]
                        ],
                        'settings' => ['padding' => ['top' => 16, 'bottom' => 16], 'background' => 'gray-50']
                    ],
                    [
                        'id' => 'testimonials-section',
                        'type' => 'testimonials',
                        'components' => [
                            [
                                'id' => 'testimonials-header',
                                'type' => 'section-header',
                                'title' => 'What readers are saying',
                                'alignment' => 'center'
                            ],
                            [
                                'id' => 'testimonials-grid',
                                'type' => 'testimonial-grid',
                                'testimonials' => [
                                    [
                                        'quote' => 'This guide helped us 3x our revenue in 6 months. The frameworks are incredibly actionable.',
                                        'author' => 'Mark Thompson',
                                        'role' => 'CEO, TechStart',
                                        'result' => '3x revenue growth'
                                    ],
                                    [
                                        'quote' => 'Finally, a guide that goes beyond theory. We implemented chapter 3 and saw results in weeks.',
                                        'author' => 'Lisa Chen',
                                        'role' => 'Founder, GrowthLab',
                                        'result' => '150% lead increase'
                                    ],
                                    [
                                        'quote' => 'The templates alone are worth 10x the download. This is a must-read for any business owner.',
                                        'author' => 'James Wilson',
                                        'role' => 'COO, ScaleUp Co',
                                        'result' => 'Saved 20hrs/week'
                                    ]
                                ]
                            ]
                        ],
                        'settings' => ['padding' => ['top' => 20, 'bottom' => 20], 'background' => 'white']
                    ],
                    [
                        'id' => 'final-cta',
                        'type' => 'cta',
                        'variant' => 'centered',
                        'components' => [
                            [
                                'id' => 'cta-heading',
                                'type' => 'heading',
                                'level' => 2,
                                'content' => 'Ready to grow your business?',
                                'style' => ['fontSize' => '3xl', 'fontWeight' => 'bold', 'color' => 'white']
                            ],
                            [
                                'id' => 'cta-form',
                                'type' => 'inline-form',
                                'fields' => [
                                    ['name' => 'email', 'placeholder' => 'Enter your email', 'type' => 'email']
                                ],
                                'submitText' => 'Get Free Guide',
                                'style' => ['inputBackground' => 'white', 'buttonBackground' => 'gray-900']
                            ]
                        ],
                        'settings' => ['padding' => ['top' => 16, 'bottom' => 16], 'background' => 'emerald-600']
                    ]
                ],
                'settings' => [
                    'theme' => [
                        'primaryColor' => '#059669',
                        'secondaryColor' => '#10B981',
                        'fontFamily' => 'Inter',
                        'borderRadius' => 'lg'
                    ]
                ],
                'meta' => [
                    'title' => 'Free Guide: Grow Your Business in 2024',
                    'description' => 'Download our comprehensive guide with strategies, case studies, and templates.'
                ]
            ],

            // 5. E-commerce Product Launch
            [
                'category_id' => 5,
                'name' => 'Product Launch',
                'slug' => 'product-launch',
                'description' => 'Product launch page with hero, features, gallery, reviews, and purchase CTA. Perfect for physical or digital products.',
                'thumbnail' => '/images/templates/product-launch-thumb.jpg',
                'preview_image' => '/images/templates/product-launch-preview.jpg',
                'usage_count' => 720,
                'rating' => 4.7,
                'rating_count' => 89,
                'tags' => ['modern', 'bold', 'light'],
                'content' => [
                    [
                        'id' => 'hero-section',
                        'type' => 'hero',
                        'variant' => 'product',
                        'components' => [
                            [
                                'id' => 'product-gallery',
                                'type' => 'product-gallery',
                                'images' => [
                                    ['src' => '/images/placeholder/product-1.jpg', 'alt' => 'Product main'],
                                    ['src' => '/images/placeholder/product-2.jpg', 'alt' => 'Product angle'],
                                    ['src' => '/images/placeholder/product-3.jpg', 'alt' => 'Product detail'],
                                    ['src' => '/images/placeholder/product-4.jpg', 'alt' => 'Product in use']
                                ]
                            ],
                            [
                                'id' => 'product-info',
                                'type' => 'product-details',
                                'badge' => 'NEW ARRIVAL',
                                'name' => 'Premium Wireless Headphones',
                                'tagline' => 'Immersive sound, unmatched comfort',
                                'price' => 299,
                                'originalPrice' => 399,
                                'rating' => 4.9,
                                'reviewCount' => 2847,
                                'description' => 'Experience audio like never before with our flagship wireless headphones. Featuring active noise cancellation, 40-hour battery life, and premium materials.',
                                'features' => [
                                    'Active Noise Cancellation',
                                    '40-hour battery life',
                                    'Premium memory foam',
                                    'Hi-Res Audio certified'
                                ],
                                'cta' => [
                                    'text' => 'Buy Now - $299',
                                    'url' => '#purchase'
                                ]
                            ]
                        ],
                        'settings' => ['padding' => ['top' => 16, 'bottom' => 16], 'background' => 'white']
                    ],
                    [
                        'id' => 'features-section',
                        'type' => 'features',
                        'variant' => 'icon-grid',
                        'components' => [
                            [
                                'id' => 'features-header',
                                'type' => 'section-header',
                                'title' => 'Engineered for excellence',
                                'alignment' => 'center'
                            ],
                            [
                                'id' => 'features-grid',
                                'type' => 'icon-feature-grid',
                                'features' => [
                                    [
                                        'icon' => 'speaker-wave',
                                        'title' => 'Hi-Res Audio',
                                        'description' => 'Custom 40mm drivers deliver exceptional clarity and depth'
                                    ],
                                    [
                                        'icon' => 'signal-slash',
                                        'title' => 'Noise Cancellation',
                                        'description' => 'Advanced ANC blocks out distractions for pure immersion'
                                    ],
                                    [
                                        'icon' => 'battery-100',
                                        'title' => '40hr Battery',
                                        'description' => 'All-day listening with quick charge support'
                                    ],
                                    [
                                        'icon' => 'bluetooth',
                                        'title' => 'Multipoint Connect',
                                        'description' => 'Seamlessly switch between two devices'
                                    ]
                                ]
                            ]
                        ],
                        'settings' => ['padding' => ['top' => 20, 'bottom' => 20], 'background' => 'gray-50']
                    ],
                    [
                        'id' => 'comparison-section',
                        'type' => 'comparison',
                        'components' => [
                            [
                                'id' => 'comparison-header',
                                'type' => 'section-header',
                                'title' => 'See how we compare',
                                'alignment' => 'center'
                            ],
                            [
                                'id' => 'comparison-table',
                                'type' => 'comparison-table',
                                'products' => ['Our Product', 'Competitor A', 'Competitor B'],
                                'features' => [
                                    ['name' => 'Battery Life', 'values' => ['40 hours', '30 hours', '25 hours']],
                                    ['name' => 'Noise Cancellation', 'values' => [true, true, false]],
                                    ['name' => 'Hi-Res Audio', 'values' => [true, false, false]],
                                    ['name' => 'Multipoint', 'values' => [true, true, true]],
                                    ['name' => 'Price', 'values' => ['$299', '$349', '$279']]
                                ]
                            ]
                        ],
                        'settings' => ['padding' => ['top' => 20, 'bottom' => 20], 'background' => 'white']
                    ],
                    [
                        'id' => 'reviews-section',
                        'type' => 'reviews',
                        'components' => [
                            [
                                'id' => 'reviews-header',
                                'type' => 'section-header',
                                'title' => 'Customer reviews',
                                'alignment' => 'center'
                            ],
                            [
                                'id' => 'reviews-summary',
                                'type' => 'review-summary',
                                'rating' => 4.9,
                                'totalReviews' => 2847,
                                'distribution' => [
                                    ['stars' => 5, 'percentage' => 85],
                                    ['stars' => 4, 'percentage' => 10],
                                    ['stars' => 3, 'percentage' => 3],
                                    ['stars' => 2, 'percentage' => 1],
                                    ['stars' => 1, 'percentage' => 1]
                                ]
                            ],
                            [
                                'id' => 'reviews-list',
                                'type' => 'review-list',
                                'reviews' => [
                                    [
                                        'rating' => 5,
                                        'title' => 'Best headphones I\'ve ever owned',
                                        'text' => 'The sound quality is incredible and the noise cancellation is on another level. Worth every penny!',
                                        'author' => 'Alex M.',
                                        'verified' => true,
                                        'date' => '2024-01-15'
                                    ],
                                    [
                                        'rating' => 5,
                                        'title' => 'Perfect for work and travel',
                                        'text' => 'Battery life is amazing and they\'re so comfortable I forget I\'m wearing them.',
                                        'author' => 'Sarah K.',
                                        'verified' => true,
                                        'date' => '2024-01-10'
                                    ]
                                ]
                            ]
                        ],
                        'settings' => ['padding' => ['top' => 20, 'bottom' => 20], 'background' => 'gray-50']
                    ],
                    [
                        'id' => 'faq-section',
                        'type' => 'faq',
                        'components' => [
                            [
                                'id' => 'faq-header',
                                'type' => 'section-header',
                                'title' => 'Frequently asked questions',
                                'alignment' => 'center'
                            ],
                            [
                                'id' => 'faq-list',
                                'type' => 'accordion',
                                'items' => [
                                    [
                                        'question' => 'What\'s included in the box?',
                                        'answer' => 'Headphones, carrying case, USB-C charging cable, 3.5mm audio cable, and user guide.'
                                    ],
                                    [
                                        'question' => 'How long does shipping take?',
                                        'answer' => 'Standard shipping takes 5-7 business days. Express shipping (2-3 days) is available at checkout.'
                                    ],
                                    [
                                        'question' => 'What\'s the return policy?',
                                        'answer' => 'We offer a 30-day money-back guarantee. If you\'re not satisfied, return them for a full refund.'
                                    ],
                                    [
                                        'question' => 'Is there a warranty?',
                                        'answer' => 'Yes, all headphones come with a 2-year manufacturer warranty.'
                                    ]
                                ]
                            ]
                        ],
                        'settings' => ['padding' => ['top' => 20, 'bottom' => 20], 'background' => 'white']
                    ],
                    [
                        'id' => 'purchase-section',
                        'type' => 'cta',
                        'variant' => 'product-cta',
                        'components' => [
                            [
                                'id' => 'purchase-card',
                                'type' => 'purchase-card',
                                'productName' => 'Premium Wireless Headphones',
                                'price' => 299,
                                'originalPrice' => 399,
                                'savings' => 100,
                                'features' => [
                                    'Free shipping',
                                    '30-day returns',
                                    '2-year warranty'
                                ],
                                'cta' => 'Add to Cart',
                                'urgency' => 'Only 23 left in stock'
                            ]
                        ],
                        'settings' => ['padding' => ['top' => 16, 'bottom' => 16], 'background' => 'gradient-to-r from-gray-900 to-gray-800']
                    ]
                ],
                'settings' => [
                    'theme' => [
                        'primaryColor' => '#111827',
                        'secondaryColor' => '#374151',
                        'accentColor' => '#F59E0B',
                        'fontFamily' => 'Inter',
                        'borderRadius' => 'lg'
                    ]
                ],
                'meta' => [
                    'title' => 'Premium Wireless Headphones - Buy Now',
                    'description' => 'Experience immersive sound with active noise cancellation and 40-hour battery life.'
                ]
            ]
        ];
    }
}
```

---

## Routes

```php
// routes/web.php

use App\Http\Controllers\TemplateController;

Route::middleware(['auth'])->group(function () {
    // Template Gallery
    Route::get('/templates', [TemplateController::class, 'index'])->name('templates.index');
    Route::get('/templates/filter', [TemplateController::class, 'filter'])->name('templates.filter');

    // Template Operations
    Route::get('/templates/{template}', [TemplateController::class, 'show'])->name('templates.show');
    Route::get('/templates/{template}/preview', [TemplateController::class, 'preview'])->name('templates.preview');
    Route::post('/templates/{template}/apply', [TemplateController::class, 'apply'])->name('templates.apply');

    // Save as Template
    Route::post('/templates', [TemplateController::class, 'store'])->name('templates.store');
    Route::get('/templates/user/list', [TemplateController::class, 'userTemplates'])->name('templates.user');
    Route::delete('/templates/{template}', [TemplateController::class, 'destroy'])->name('templates.destroy');
});
```

---

## Usage Examples

### Display Template Gallery

```php
// In your controller or view
$categories = TemplateCategory::active()->ordered()->get();
$templates = Template::public()->featured()->with('category')->paginate(12);
```

### Apply Template to Create Landing Page

```javascript
// Frontend JavaScript
async function applyTemplate(templateId, pageName) {
    const response = await fetch(`/templates/${templateId}/apply`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ name: pageName })
    });

    const data = await response.json();
    if (data.success) {
        window.location.href = data.landing_page.edit_url;
    }
}
```

### Save Landing Page as Template

```javascript
// Trigger save modal
Alpine.store('saveTemplate', {
    show: true,
    landingPageId: currentPageId
});
```

---

## Additional Notes

### Template Content Structure

Each template's `content` array contains sections with:
- `id`: Unique identifier
- `type`: Section type (hero, features, pricing, etc.)
- `variant`: Style variant
- `components`: Array of component objects
- `settings`: Section-specific settings

### Template Settings Structure

The `settings` object contains:
- `theme`: Colors, fonts, border radius
- `global`: Max width, container padding, navigation/footer visibility

### Best Practices

1. **Thumbnails**: Generate thumbnails automatically when templates are created
2. **Caching**: Cache template queries for better performance
3. **Search**: Implement full-text search for better template discovery
4. **Analytics**: Track template usage and popularity
5. **Versioning**: Consider template versioning for updates

### Running the Seeder

```bash
php artisan db:seed --class=TemplateSeeder
```

Or add to DatabaseSeeder:

```php
public function run(): void
{
    $this->call([
        TemplateSeeder::class,
    ]);
}
```
