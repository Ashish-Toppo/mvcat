<?php

namespace Modules\Auth\Providers;

use Core\Classes\ServiceProvider;
use Core\Classes\View;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind any Auth-specific services here
    }

    public function boot(): void
    {
        // 1. Register the View namespace so we can use view('auth::login')
        View::addNamespace('auth', __DIR__ . '/../Views');

        // 2. Load the Auth module's routes
        $this->app->getRouter()->group('', [], function ($router) {
            require __DIR__ . '/../Routes/web.php';
        });
    }
}
