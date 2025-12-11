<?php

namespace App\Observers;

use App\Models\Envios;
use App\Models\MovimientoInventario;
use App\Services\InventarioService;

class EnviosObserver
{
    /**
     * Handle the Envios "deleting" event (soft delete).
     * Se ejecuta antes de que se marque como eliminado.
     */
    public function deleting(Envios $envio)
    {
        $this->revertirInventario($envio);
    }

    /**
     * Handle the Envios "forceDeleting" event (permanent delete).
     * Se ejecuta antes de que se elimine permanentemente.
     */
    public function forceDeleting(Envios $envio)
    {
        $this->revertirInventario($envio);
    }

    /**
     * Revierte el inventario de todos los detalles de un envío
     */
    private function revertirInventario(Envios $envio)
    {
        $inventarioService = new InventarioService();

        // Obtener todos los detalles de envío asociados
        $detalles = $envio->detalleEnvios()->get();

        foreach ($detalles as $detalle) {
            // Buscar los movimientos de inventario asociados a este detalle
            // Nota: Un envío puede generar múltiples movimientos por FIFO
            $movimientos = MovimientoInventario::where('referencia_tipo', 'ENVIO')
                ->where('referencia_id', $envio->id_envio)
                ->where('id_producto', $detalle->id_producto)
                ->where('tipo_movimiento', 'SALIDA')
                ->get();

            foreach ($movimientos as $movimiento) {
                try {
                    // Revertir el movimiento (restaurar stock)
                    $inventarioService->revertirMovimiento($movimiento);
                } catch (\Exception $e) {
                    // Log del error pero no detener el proceso
                    \Log::error("Error al revertir movimiento de envío #{$envio->id_envio}: " . $e->getMessage());
                }
            }
        }
    }
}
