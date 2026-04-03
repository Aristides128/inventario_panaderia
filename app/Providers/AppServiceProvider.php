<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Producciones;
use App\Models\DetalleProducciones;
use App\Models\Envios;
use App\Models\DetalleEnvio;
use App\Models\Compras;
use App\Models\DetalleCompras;
use App\Observers\ProduccionesObserver;
use App\Observers\DetalleProduccionesObserver;
use App\Observers\EnviosObserver;
use App\Observers\DetalleEnvioObserver;
use App\Observers\ComprasObserver;
use App\Observers\DetalleComprasObserver;

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

    // Registrar observers para restauración automática de inventario
    Producciones::observe(ProduccionesObserver::class);
    DetalleProducciones::observe(DetalleProduccionesObserver::class);
    Envios::observe(EnviosObserver::class);
    DetalleEnvio::observe(DetalleEnvioObserver::class);

    // Anulación de compras al borrar definitivamente
    Compras::observe(ComprasObserver::class);
    DetalleCompras::observe(DetalleComprasObserver::class);

    }
}
