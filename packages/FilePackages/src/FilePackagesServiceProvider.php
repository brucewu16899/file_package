<?php

namespace FilePackages;

use Illuminate\Support\ServiceProvider;

class FilePackagesServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->make(FileController::class);
        include(__DIR__ . '/routes.php');
        $this->loadViewsFrom(__DIR__ . '/views', 'FilePackages');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
