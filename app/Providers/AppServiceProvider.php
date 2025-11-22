<?php

namespace App\Providers;

use App\Models\Media;
use App\Models\Page;
use App\Models\User;
use App\Observers\MediaObserver;
use App\Observers\PageObserver;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        User::observe(UserObserver::class);
        Page::observe(PageObserver::class);
        Media::observe(MediaObserver::class);
    }
}
