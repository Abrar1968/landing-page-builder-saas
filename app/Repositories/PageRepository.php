<?php

namespace App\Repositories;

use App\Models\Page;
use Illuminate\Pagination\LengthAwarePaginator;

class PageRepository extends BaseRepository
{
    public function __construct(Page $model)
    {
        $this->model = $model;
    }

    public function getByUser(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->model
            ->where('user_id', $userId)
            ->with(['template'])
            ->latest()
            ->paginate($perPage);
    }

    public function findBySlug(string $slug): ?Page
    {
        return $this->model->where('slug', $slug)->first();
    }

    public function getPublished()
    {
        return $this->model->published()->get();
    }

    public function getPublishedByUser(int $userId)
    {
        return $this->model
            ->where('user_id', $userId)
            ->published()
            ->get();
    }

    public function countByUser(int $userId): int
    {
        return $this->model->where('user_id', $userId)->count();
    }

    public function search(int $userId, string $term, int $perPage = 10): LengthAwarePaginator
    {
        return $this->model
            ->where('user_id', $userId)
            ->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('slug', 'like', "%{$term}%");
            })
            ->latest()
            ->paginate($perPage);
    }
}
