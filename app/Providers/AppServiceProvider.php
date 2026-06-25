<?php

namespace App\Providers;

use Core\Classes\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind things into the DI container here.
        // Example:
        // $this->app->container->bind(\App\Contracts\PaymentGateway::class, \App\Services\StripeGateway::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Perform post-registration booting of services here.
    }
}
