<?php

namespace App\Services;

use App\Models\Page;
use App\Models\User;
use App\Repositories\PageRepository;
use Illuminate\Support\Str;

class PageService
{
    public function __construct(
        protected PageRepository $repository
    ) {}

    public function create(User $user, array $data): Page
    {
        $data['user_id'] = $user->id;
        $data['slug'] = $this->generateUniqueSlug($data['title'] ?? 'untitled');
        $data['status'] = $data['status'] ?? 'draft';
        $data['content'] = $data['content'] ?? [];
        $data['settings'] = $data['settings'] ?? [];

        return $this->repository->create($data);
    }

    public function update(Page $page, array $data): bool
    {
        if (isset($data['title']) && $data['title'] !== $page->title) {
            $data['slug'] = $this->generateUniqueSlug($data['title'], $page->id);
        }

        return $this->repository->update($page, $data);
    }

    public function delete(Page $page): bool
    {
        return $this->repository->delete($page);
    }

    public function duplicate(Page $page): Page
    {
        return $this->create($page->user, [
            'title' => $page->title . ' (Copy)',
            'content' => $page->content,
            'settings' => $page->settings,
            'template_id' => $page->template_id,
        ]);
    }

    public function publish(Page $page): bool
    {
        \Illuminate\Support\Facades\Log::info('Publishing page: ' . $page->id);
        $result = $this->repository->update($page, [
            'status' => 'published',
            'published_at' => now(),
            'published_content' => $page->content, // Save current content as published version
        ]);
        \Illuminate\Support\Facades\Log::info('Publish result: ' . ($result ? 'success' : 'failure'));
        return $result;
    }

    public function unpublish(Page $page): bool
    {
        return $this->repository->update($page, [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    public function createVersion(Page $page): void
    {
        $page->versions()->create([
            'content' => $page->content,
            'settings' => $page->settings,
        ]);
    }

    protected function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $count = 1;

        $query = Page::where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $originalSlug . '-' . $count;
            $query = Page::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            $count++;
        }

        return $slug;
    }

    public function getUserStats(User $user): array
    {
        return [
            'total_pages' => $user->pages()->count(),
            'published_pages' => $this->repository->countByStatus($user, 'published'),
            'draft_pages' => $this->repository->countByStatus($user, 'draft'),
            'total_views' => $this->repository->getTotalViews($user),
            'total_conversions' => $this->repository->getTotalConversions($user),
        ];
    }
}
