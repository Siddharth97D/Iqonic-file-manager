<?php

namespace Iqonic\FileManager;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Iqonic\FileManager\Services\FileManagerService;
use Iqonic\FileManager\Listeners\ActivityLogger;

class FileManagerServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/file-manager.php', 'file-manager'
        );

        $this->app->bind('file-manager', function ($app) {
            return new FileManagerService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/file-manager.php' => config_path('file-manager.php'),
        ], 'file-manager-config');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'file-manager');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/file-manager'),
        ], 'file-manager-views');

        Event::subscribe(ActivityLogger::class);

        // Register Policy
        \Illuminate\Support\Facades\Gate::policy(\Iqonic\FileManager\Models\File::class, \Iqonic\FileManager\Policies\FilePolicy::class);
    }
}
