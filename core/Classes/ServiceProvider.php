<?php

namespace Core\Classes;

abstract class ServiceProvider
{
    protected App $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * Register bindings in the container.
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
        //
    }
}
