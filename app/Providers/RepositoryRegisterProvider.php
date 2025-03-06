<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Knplabs\Github\Client as GitHubClient;
use Http\Client\HttpClient;
use App\Repositories\Users\UserTokenRepository;

class RepositoryRegisterProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $repositories = glob(app_path('Repositories/Users/*.php'));
        foreach ($repositories as $repository) {
            $this->app->bindIf($repository, $repository);
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
