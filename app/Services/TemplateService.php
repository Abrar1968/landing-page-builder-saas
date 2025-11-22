<?php

namespace App\Services;

use App\Models\Template;
use App\Models\TemplateTag;
use App\Models\Page;
use App\Models\User;
use App\Repositories\TemplateRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TemplateService
{
    public function __construct(
        protected TemplateRepository $repository
    ) {}

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
     * Apply template to create a new page
     */
    public function applyTemplate(
        Template $template,
        User $user,
        string $name,
        ?int $projectId = null
    ): Page {
        return DB::transaction(function () use ($template, $user, $name) {
            // Create page from template
            $page = Page::create([
                'user_id' => $user->id,
                'template_id' => $template->id,
                'title' => $name,
                'slug' => Str::slug($name) . '-' . Str::random(6),
                'content' => $this->processTemplateContent($template->content),
                'settings' => array_merge(
                    $this->getDefaultSettings(),
                    $template->settings ?? []
                ),
                'status' => 'draft',
            ]);

            // Increment template usage
            $template->incrementUsage();

            return $page;
        });
    }

    /**
     * Create template from existing page
     */
    public function createFromPage(
        Page $page,
        User $user,
        array $data
    ): Template {
        return DB::transaction(function () use ($page, $user, $data) {
            // Create template
            $template = Template::create([
                'category_id' => $data['category_id'],
                'user_id' => $user->id,
                'name' => $data['name'],
                'slug' => Str::slug($data['name']) . '-' . Str::random(6),
                'description' => $data['description'] ?? null,
                'thumbnail' => null,
                'content' => $page->content,
                'settings' => $page->settings,
                'meta' => $page->settings['seo'] ?? null,
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

    /**
     * Get default page settings
     */
    protected function getDefaultSettings(): array
    {
        return [
            'font' => 'Inter',
            'primaryColor' => '#3B82F6',
            'backgroundColor' => '#FFFFFF',
        ];
    }

    /**
     * Get user's custom templates
     */
    public function getUserTemplates(User $user, int $perPage = 10): LengthAwarePaginator
    {
        return Template::where('user_id', $user->id)
            ->with('category')
            ->latest()
            ->paginate($perPage);
    }
}
