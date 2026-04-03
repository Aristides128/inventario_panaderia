<?php

namespace App\Observers;

use App\Models\DetalleCompras;
use App\Models\MovimientoInventario;
use App\Models\Productos;
use App\Models\detalle_lotes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DetalleComprasObserver
{
    /**
     * Solo se activa al borrar definitivamente (forceDelete).
     * El soft delete normal NO registra movimiento.
     */
    public function forceDeleting(DetalleCompras $detalle)
    {
        $this->anularCompra($detalle);
    }

    /**
     * Registra la anulación de compra en movimientos de inventario y actualiza el stock.
     */
    private function anularCompra(DetalleCompras $detalle)
    {
        try {
            DB::transaction(function () use ($detalle) {
                $producto = Productos::find($detalle->id_producto);

                if (!$producto) {
                    Log::warning("Anulación de compra: producto #{$detalle->id_producto} no encontrado.");
                    return;
                }

                // La cantidad total que entró con esta compra
                $cantidadAnular = ($detalle->cantidad_paquetes ?? 1) * ($detalle->cantidad_producto ?? 0);

                if ($cantidadAnular <= 0) {
                    return;
                }

                $stockAnterior = $producto->stock_actual;
                $stockNuevo = max(0, $stockAnterior - $cantidadAnular);

                // Actualizar el stock del producto
                $producto->update(['stock_actual' => $stockNuevo]);

                // Buscar el lote asociado al movimiento original de esta compra
                $movimientoOriginal = MovimientoInventario::where('referencia_tipo', 'COMPRA')
                    ->where('referencia_id', $detalle->id_compra)
                    ->where('id_producto', $detalle->id_producto)
                    ->where('tipo_movimiento', 'ENTRADA')
                    ->first();

                $idLote = $movimientoOriginal?->id_lote ?? null;

                // Si tenemos el lote, reducir la cantidad en detalle_lotes
                if ($idLote) {
                    $detalleLote = detalle_lotes::where('id_lote', $idLote)
                        ->where('id_producto', $detalle->id_producto)
                        ->first();

                    if ($detalleLote) {
                        $nueva = max(0, $detalleLote->cantidad - $cantidadAnular);
                        $detalleLote->update(['cantidad' => $nueva]);
                    }
                }

                // Registrar movimiento de anulación de compra
                MovimientoInventario::create([
                    'id_producto'      => $detalle->id_producto,
                    'id_lote'          => $idLote,
                    'tipo_movimiento'  => 'SALIDA',
                    'cantidad'         => $cantidadAnular,
                    'cantidad_anterior' => $stockAnterior,
                    'cantidad_nueva'   => $stockNuevo,
                    'referencia_tipo'  => 'COMPRA',
                    'referencia_id'    => $detalle->id_compra,
                    'observaciones'    => "Anulación de compra #{$detalle->id_compra} — Detalle #{$detalle->id_detalle}",
                    'usuario_id'       => Auth::id(),
                ]);
            });
        } catch (\Exception $e) {
            Log::error("Error al registrar anulación de compra para detalle #{$detalle->id_detalle}: " . $e->getMessage());
        }
    }
}
