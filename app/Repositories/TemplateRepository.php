<?php

namespace App\Repositories;

use App\Models\Template;
use Illuminate\Database\Eloquent\Collection;

class TemplateRepository extends BaseRepository
{
    public function __construct(Template $model)
    {
        $this->model = $model;
    }

    public function getPublished(): Collection
    {
        return $this->model->published()->get();
    }

    public function getFeatured(): Collection
    {
        return $this->model
            ->where('is_featured', true)
            ->where('is_published', true)
            ->get();
    }

    public function getSystemTemplates(): Collection
    {
        return $this->model
            ->where('is_system', true)
            ->where('is_published', true)
            ->get();
    }

    public function getByCategory(string $category): Collection
    {
        return $this->model
            ->where('category', $category)
            ->where('is_published', true)
            ->get();
    }

    public function getUserTemplates(int $userId): Collection
    {
        return $this->model
            ->where('user_id', $userId)
            ->get();
    }

    public function search(string $term): Collection
    {
        return $this->model
            ->where('is_published', true)
            ->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%");
            })
            ->get();
    }
}
