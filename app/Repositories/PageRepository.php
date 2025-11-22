<?php

namespace App\Repositories;

use App\Models\Page;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PageRepository
{
    public function __construct(
        protected Page $model
    ) {}

    public function create(array $data): Page
    {
        return $this->model->create($data);
    }

    public function update(Page $page, array $data): bool
    {
        return $page->update($data);
    }

    public function delete(Page $page): bool
    {
        return $page->delete();
    }

    public function find(int $id): ?Page
    {
        return $this->model->find($id);
    }

    public function findBySlug(string $slug): ?Page
    {
        return $this->model->where('slug', $slug)->first();
    }

    public function getUserPages(User $user, int $perPage = 10): LengthAwarePaginator
    {
        return $user->pages()
            ->latest('updated_at')
            ->paginate($perPage);
    }

    public function getUserPagesCollection(User $user): Collection
    {
        return $user->pages()
            ->latest('updated_at')
            ->get();
    }

    public function getRecentPages(User $user, int $limit = 6): Collection
    {
        return $user->pages()
            ->latest('updated_at')
            ->take($limit)
            ->get();
    }

    public function countByStatus(User $user, string $status): int
    {
        return $user->pages()
            ->where('status', $status)
            ->count();
    }

    public function getTotalViews(User $user): int
    {
        return $user->pages()->sum('views') ?? 0;
    }

    public function getTotalConversions(User $user): int
    {
        return $user->pages()->sum('conversions') ?? 0;
    }
}
