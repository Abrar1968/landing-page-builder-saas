<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register repository bindings.
     */
    public function register(): void
    {
        // Bind repository interfaces to implementations
        // Example:
        // $this->app->bind(
        //     \App\Contracts\Repositories\UserRepositoryInterface::class,
        //     \App\Repositories\UserRepository::class
        // );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
