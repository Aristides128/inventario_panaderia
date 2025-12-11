<?php

namespace App\Observers;

use App\Models\Producciones;
use App\Models\MovimientoInventario;
use App\Services\InventarioService;

class ProduccionesObserver
{
    /**
     * Handle the Producciones "deleting" event (soft delete).
     * Se ejecuta antes de que se marque como eliminado.
     */
    public function deleting(Producciones $produccion)
    {
        $this->revertirInventario($produccion);
    }

    /**
     * Handle the Producciones "forceDeleting" event (permanent delete).
     * Se ejecuta antes de que se elimine permanentemente.
     */
    public function forceDeleting(Producciones $produccion)
    {
        $this->revertirInventario($produccion);
    }

    /**
     * Revierte el inventario de todos los detalles de una producción
     */
    private function revertirInventario(Producciones $produccion)
    {
        $inventarioService = new InventarioService();

        // Obtener todos los detalles de producción asociados
        $detalles = $produccion->detalles()->get();

        foreach ($detalles as $detalle) {
            // Buscar el movimiento de inventario asociado a este detalle
            $movimiento = MovimientoInventario::where('referencia_tipo', 'PRODUCCION')
                ->where('referencia_id', $produccion->id_produccion)
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
                    \Log::error("Error al revertir movimiento de producción #{$produccion->id_produccion}: " . $e->getMessage());
                }
            }
        }
    }
}
