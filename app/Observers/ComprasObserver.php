<?php

namespace App\Observers;

use App\Models\Compras;
use Illuminate\Support\Facades\Log;

class ComprasObserver
{
    /**
     * Al borrar definitivamente una Compra, también borra definitivamente
     * cada uno de sus detalles, disparando DetalleComprasObserver::forceDeleting
     * y registrando la anulación en movimientos_inventario.
     */
    public function forceDeleting(Compras $compra)
    {
        // Incluir también los soft-deleted para limpiar todo
        $detalles = $compra->detalleCompras()->withTrashed()->get();

        foreach ($detalles as $detalle) {
            try {
                // forceDelete dispara DetalleComprasObserver::forceDeleting
                $detalle->forceDelete();
            } catch (\Exception $e) {
                Log::error("Error al borrar definitivamente detalle #{$detalle->id_detalle} de compra #{$compra->id_compra}: " . $e->getMessage());
            }
        }
    }
}
