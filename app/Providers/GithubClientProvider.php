<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Github\Client as GitHubClient;
use Http\Client\HttpClient;
use App\Services\Github\GithubUserClient;

class GithubClientProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(GitHubClient::class, function ($app) {
            return GithubUserClient::getClientForUser();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
