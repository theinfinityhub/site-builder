<?php

namespace OneClx\SiteBuilder;

use Illuminate\Cookie\CookieJar;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class SiteEditorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->make('OneClx\SiteBuilder\Http\Controllers\SiteEditorController');
        $this->loadViewsFrom(__DIR__ . '/Views', 'editor');
        $this->app['router']->aliasMiddleware('csrf', \OneClx\SiteBuilder\Http\Middleware\VerifyCsrfTokenMiddleware::class);

        $this->publishes([
            __DIR__ . '/assets' => public_path('vendor/site-editor'),
        ], 'public');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Route::prefix('pcx/editor')->group(__DIR__.'/routes/editor.php');
    }
}
