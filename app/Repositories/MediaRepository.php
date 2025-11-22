<?php

namespace App\Repositories;

use App\Models\Media;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class MediaRepository extends BaseRepository
{
    public function __construct(Media $model)
    {
        $this->model = $model;
    }

    public function getByUser(int $userId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->model
            ->where('user_id', $userId)
            ->latest()
            ->paginate($perPage);
    }

    public function getByUserAndFolder(int $userId, ?string $folder, int $perPage = 20): LengthAwarePaginator
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('folder', $folder)
            ->latest()
            ->paginate($perPage);
    }

    public function getImagesByUser(int $userId): Collection
    {
        return $this->model
            ->where('user_id', $userId)
            ->images()
            ->latest()
            ->get();
    }

    public function getTotalSizeByUser(int $userId): int
    {
        return $this->model
            ->where('user_id', $userId)
            ->sum('size');
    }

    public function getFoldersByUser(int $userId): Collection
    {
        return $this->model
            ->where('user_id', $userId)
            ->whereNotNull('folder')
            ->distinct()
            ->pluck('folder');
    }
}
