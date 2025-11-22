<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserService
{
    public function __construct(
        protected UserRepository $repository
    ) {}

    /**
     * Register a new user.
     */
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = $this->repository->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            Log::info('User registered via service', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return $user;
        });
    }

    /**
     * Update user profile.
     */
    public function updateProfile(User $user, array $data): User
    {
        $updateData = array_filter([
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'avatar' => $data['avatar'] ?? null,
            'timezone' => $data['timezone'] ?? null,
        ], fn($value) => $value !== null);

        return $this->repository->update($user->id, $updateData);
    }

    /**
     * Update user password.
     */
    public function updatePassword(User $user, string $password): User
    {
        return $this->repository->update($user->id, [
            'password' => Hash::make($password),
        ]);
    }

    /**
     * Record user login.
     */
    public function recordLogin(User $user): void
    {
        $this->repository->updateLastLogin($user->id);

        Log::info('User logged in', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
    }

    /**
     * Find user by email.
     */
    public function findByEmail(string $email): ?User
    {
        return $this->repository->findByEmail($email);
    }

    /**
     * Delete user account.
     */
    public function deleteAccount(User $user): bool
    {
        return DB::transaction(function () use ($user) {
            // Delete related data
            $user->pages()->delete();
            $user->media()->delete();
            $user->domains()->delete();

            Log::info('User account deleted', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return $this->repository->delete($user->id);
        });
    }
}
