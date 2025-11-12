<?php

namespace App\Providers;

use App\Services\Glpi\GlpiService;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registrar GlpiService como singleton
        $this->app->singleton(GlpiService::class, function ($app) {
            return new GlpiService();
        });

        // Alias para facilitar el uso
        $this->app->alias(GlpiService::class, 'glpi');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
