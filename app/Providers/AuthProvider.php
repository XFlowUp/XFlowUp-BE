<?php

namespace App\Providers;

use App\Services\Auth\AuthService;
use App\Services\Github\RepositoryService;
use Illuminate\Support\ServiceProvider;

class AuthProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bindIf(AuthService::class, AuthService::class);
        $this->app->bindIf(RepositoryService::class, RepositoryService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
