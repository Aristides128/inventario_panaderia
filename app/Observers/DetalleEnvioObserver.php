<?php

namespace App\Observers;

use App\Models\DetalleEnvio;
use App\Models\MovimientoInventario;
use App\Services\InventarioService;

class DetalleEnvioObserver
{
    /**
     * Handle the DetalleEnvio "deleting" event (soft delete).
     */
    public function deleting(DetalleEnvio $detalle)
    {
        $this->revertirInventario($detalle);
    }

    /**
     * Handle the DetalleEnvio "forceDeleting" event (permanent delete).
     */
    public function forceDeleting(DetalleEnvio $detalle)
    {
        $this->revertirInventario($detalle);
    }

    /**
     * Revierte el inventario de un detalle de envío específico
     */
    private function revertirInventario(DetalleEnvio $detalle)
    {
        $inventarioService = new InventarioService();

        // Buscar los movimientos de inventario asociados a este detalle
        // Nota: Un detalle de envío puede generar múltiples movimientos por FIFO
        $movimientos = MovimientoInventario::where('referencia_tipo', 'ENVIO')
            ->where('referencia_id', $detalle->id_envio)
            ->where('id_producto', $detalle->id_producto)
            ->where('tipo_movimiento', 'SALIDA')
            ->get();

        foreach ($movimientos as $movimiento) {
            try {
                // Revertir el movimiento (restaurar stock)
                $inventarioService->revertirMovimiento($movimiento);
            } catch (\Exception $e) {
                // Log del error pero no detener el proceso
                \Log::error("Error al revertir movimiento de detalle envío #{$detalle->id_detalle_envio}: " . $e->getMessage());
            }
        }
    }
}
