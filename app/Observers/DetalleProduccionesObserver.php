<?php

namespace App\Observers;

use App\Models\DetalleProducciones;
use App\Models\MovimientoInventario;
use App\Services\InventarioService;

class DetalleProduccionesObserver
{
    /**
     * Handle the DetalleProducciones "deleting" event (soft delete).
     */
    public function deleting(DetalleProducciones $detalle)
    {
        $this->revertirInventario($detalle);
    }

    /**
     * Handle the DetalleProducciones "forceDeleting" event (permanent delete).
     */
    public function forceDeleting(DetalleProducciones $detalle)
    {
        $this->revertirInventario($detalle);
    }

    /**
     * Revierte el inventario de un detalle de producción específico
     */
    private function revertirInventario(DetalleProducciones $detalle)
    {
        $inventarioService = new InventarioService();

        // Buscar el movimiento de inventario asociado a este detalle
        $movimiento = MovimientoInventario::where('referencia_tipo', 'PRODUCCION')
            ->where('referencia_id', $detalle->id_produccion)
            ->where('id_producto', $detalle->id_producto)
            ->where('tipo_movimiento', 'SALIDA')
            ->where('cantidad', $detalle->cantidad_utilizada)
            ->first();

        if ($movimiento) {
            try {
                // Revertir el movimiento (restaurar stock)
                $inventarioService->revertirMovimiento($movimiento);
            } catch (\Exception $e) {
                // Log del error pero no detener el proceso
                \Log::error("Error al revertir movimiento de detalle producción #{$detalle->id_detalle}: " . $e->getMessage());
            }
        }
    }
}
