<?php

namespace App\Contracts\Repositories;

use App\Models\User;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    public function findByEmail(string $email): ?User;

    public function updateLastLogin(int $id): User;
}
