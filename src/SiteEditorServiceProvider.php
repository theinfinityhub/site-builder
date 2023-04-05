<?php

namespace Kavi\SiteEditor;

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
        $this->app->make('Kavi\SiteEditor\Http\Controllers\SiteEditorController');
        $this->loadViewsFrom(__DIR__ . '/Views', 'editor');
        $this->app['router']->aliasMiddleware('csrf', \Kavi\SiteEditor\Http\Middleware\VerifyCsrfTokenMiddleware::class);

        $this->publishes([
            __DIR__ . '/Views/siteEditor' => base_path('resources/views/vendor/siteEditor'),
        ]);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/assets' => public_path('vendor/site-editor'),
        ], 'public');
        
        Route::prefix('pcx/editor')->group(__DIR__.'/routes/editor.php');
    }
}
