<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
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
        // Forzar idioma a español
    app()->setLocale('es');
    
    if (class_exists('Filament\Filament')) {
        \Filament\Facades\Filament::setLocale('es');
    }

    }
}
