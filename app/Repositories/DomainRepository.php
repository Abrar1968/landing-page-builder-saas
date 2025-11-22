<?php

namespace App\Repositories;

use App\Models\Domain;
use Illuminate\Database\Eloquent\Collection;

class DomainRepository extends BaseRepository
{
    public function __construct(Domain $model)
    {
        $this->model = $model;
    }

    public function getByUser(int $userId): Collection
    {
        return $this->model
            ->where('user_id', $userId)
            ->with(['page'])
            ->get();
    }

    public function findByDomain(string $domain): ?Domain
    {
        return $this->model->where('domain', $domain)->first();
    }

    public function getVerifiedByUser(int $userId): Collection
    {
        return $this->model
            ->where('user_id', $userId)
            ->verified()
            ->get();
    }

    public function getPendingVerification(): Collection
    {
        return $this->model
            ->where('status', 'pending')
            ->get();
    }

    public function countByUser(int $userId): int
    {
        return $this->model->where('user_id', $userId)->count();
    }
}
