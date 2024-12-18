<?php

namespace App\Modules\Auth;

use App\Modules\Auth\Http\Middleware\LastSeenMiddleware;
use Illuminate\Foundation\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use App\Modules\Auth\Contracts\UserRepositoryInterface;
use App\Modules\Auth\Repositories\UserRepository;

class AuthServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
    }

    public function boot(Kernel $kernel)
    {
        $kernel->pushMiddleware(LastSeenMiddleware::class);
        $this->loadRoutesFrom(__DIR__ . '/routes.php');
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
    }
}
