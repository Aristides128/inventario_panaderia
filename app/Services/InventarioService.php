<?php

namespace App\Services;

use App\Models\Productos;
use App\Models\lotes;
use App\Models\detalle_lotes;
use App\Models\MovimientoInventario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InventarioService
{
    /**
     * Registra una entrada de inventario (compra o producción)
     * 
     * @param int $idProducto
     * @param int $idLote
     * @param int $cantidad
     * @param string $referenciaType (COMPRA, PRODUCCION)
     * @param int|null $referenciaId
     * @param string|null $observaciones
     * @return MovimientoInventario
     */
    
    public function registrarEntrada(
        int $idProducto,
        int $idLote,
        int $cantidad,
        string $referenciaType,
        ?int $referenciaId = null,
        ?string $observaciones = null
    ): MovimientoInventario {
        return DB::transaction(function () use ($idProducto, $idLote, $cantidad, $referenciaType, $referenciaId, $observaciones) {
            // Obtener producto
            $producto = Productos::findOrFail($idProducto);
            $stockAnterior = $producto->stock_actual;
            $stockNuevo = $stockAnterior + $cantidad;

            // Actualizar stock del producto
            $producto->update(['stock_actual' => $stockNuevo]);

            // Actualizar o crear detalle de lote
            $detalleLote = detalle_lotes::where('id_lote', $idLote)
                ->where('id_producto', $idProducto)
                ->first();

            if ($detalleLote) {
                $detalleLote->increment('cantidad', $cantidad);
            } else {
                detalle_lotes::create([
                    'id_lote' => $idLote,
                    'id_producto' => $idProducto,
                    'cantidad' => $cantidad,
                    'fecha_vencimiento' => null, // Se puede configurar después
                ]);
            }

            // Registrar movimiento
            return MovimientoInventario::create([
                'id_producto' => $idProducto,
                'id_lote' => $idLote,
                'tipo_movimiento' => 'ENTRADA',
                'cantidad' => $cantidad,
                'cantidad_anterior' => $stockAnterior,
                'cantidad_nueva' => $stockNuevo,
                'referencia_tipo' => $referenciaType,
                'referencia_id' => $referenciaId,
                'observaciones' => $observaciones,
                'usuario_id' => Auth::id(),
            ]);
        });
    }

    /**
     * Registra una salida de inventario (envío) usando FIFO
     * 
     * @param int $idProducto
     * @param int $cantidad
     * @param string $referenciaType (ENVIO, AJUSTE)
     * @param int|null $referenciaId
     * @param string|null $observaciones
     * @return array Array de MovimientoInventario
     * @throws \Exception
     */
    public function registrarSalida(
        int $idProducto,
        int $cantidad,
        string $referenciaType,
        ?int $referenciaId = null,
        ?string $observaciones = null
    ): array {
        return DB::transaction(function () use ($idProducto, $cantidad, $referenciaType, $referenciaId, $observaciones) {
            // Obtener producto
            $producto = Productos::findOrFail($idProducto);
            $stockAnterior = $producto->stock_actual;

            // Validar que hay suficiente stock
            if ($stockAnterior < $cantidad) {
                throw new \Exception("Stock insuficiente. Disponible: {$stockAnterior}, Solicitado: {$cantidad}");
            }

            $cantidadRestante = $cantidad;
            $movimientos = [];

            // Obtener lotes con stock disponible ordenados por fecha (FIFO)
            // Primero por fecha de vencimiento (FEFO), luego por fecha de creación
            $detalleLotes = detalle_lotes::where('id_producto', $idProducto)
                ->where('cantidad', '>', 0)
                ->join('lotes', 'detalle_lotes.id_lote', '=', 'lotes.id_lote')
                ->orderBy('detalle_lotes.fecha_vencimiento', 'asc')
                ->orderBy('detalle_lotes.created_at', 'asc')
                ->select('detalle_lotes.*')
                ->get();

            if ($detalleLotes->isEmpty()) {
                throw new \Exception("No hay lotes disponibles para este producto");
            }

            // Descontar de cada lote usando FIFO
            foreach ($detalleLotes as $detalleLote) {
                if ($cantidadRestante <= 0) {
                    break;
                }

                $cantidadADescontar = min($cantidadRestante, $detalleLote->cantidad);
                
                // Actualizar cantidad en el lote
                $detalleLote->decrement('cantidad', $cantidadADescontar);
                
                // Registrar movimiento para este lote
                $movimientos[] = MovimientoInventario::create([
                    'id_producto' => $idProducto,
                    'id_lote' => $detalleLote->id_lote,
                    'tipo_movimiento' => 'SALIDA',
                    'cantidad' => $cantidadADescontar,
                    'cantidad_anterior' => $stockAnterior,
                    'cantidad_nueva' => $stockAnterior - $cantidadADescontar,
                    'referencia_tipo' => $referenciaType,
                    'referencia_id' => $referenciaId,
                    'observaciones' => $observaciones,
                    'usuario_id' => Auth::id(),
                ]);

                $cantidadRestante -= $cantidadADescontar;
                $stockAnterior -= $cantidadADescontar;
            }

            // Actualizar stock total del producto
            $stockNuevo = $producto->stock_actual - $cantidad;
            $producto->update(['stock_actual' => $stockNuevo]);

            return $movimientos;
        });
    }

    /**
     * Obtiene el stock disponible por lote para un producto
     * 
     * @param int $idProducto
     * @return \Illuminate\Support\Collection
     */
    public function obtenerStockPorLote(int $idProducto)
    {
        return detalle_lotes::where('id_producto', $idProducto)
            ->where('cantidad', '>', 0)
            ->join('lotes', 'detalle_lotes.id_lote', '=', 'lotes.id_lote')
            ->orderBy('detalle_lotes.fecha_vencimiento', 'asc')
            ->orderBy('detalle_lotes.created_at', 'asc')
            ->select(
                'detalle_lotes.*',
                'lotes.semana',
                'lotes.mes',
                'lotes.anio'
            )
            ->get();
    }

    /**
     * Obtiene el historial de movimientos de un producto
     * 
     * @param int $idProducto
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function obtenerHistorialMovimientos(int $idProducto, int $limit = 50)
    {
        return MovimientoInventario::where('id_producto', $idProducto)
            ->with(['lote', 'usuario'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Revierte un movimiento de inventario (restaura stock cuando se elimina una producción o envío)
     * 
     * @param MovimientoInventario $movimientoOriginal
     * @return MovimientoInventario
     */
    public function revertirMovimiento(MovimientoInventario $movimientoOriginal): MovimientoInventario
    {
        return DB::transaction(function () use ($movimientoOriginal) {
            // Solo se pueden revertir movimientos de SALIDA
            if ($movimientoOriginal->tipo_movimiento !== 'SALIDA') {
                throw new \Exception("Solo se pueden revertir movimientos de tipo SALIDA");
            }

            // Obtener producto
            $producto = Productos::findOrFail($movimientoOriginal->id_producto);
            $stockAnterior = $producto->stock_actual;
            $stockNuevo = $stockAnterior + $movimientoOriginal->cantidad;

            // Actualizar stock del producto
            $producto->update(['stock_actual' => $stockNuevo]);

            // Restaurar cantidad en el lote original
            $detalleLote = detalle_lotes::where('id_lote', $movimientoOriginal->id_lote)
                ->where('id_producto', $movimientoOriginal->id_producto)
                ->first();

            if ($detalleLote) {
                $detalleLote->increment('cantidad', $movimientoOriginal->cantidad);
            } else {
                // Si el detalle de lote no existe, crearlo
                detalle_lotes::create([
                    'id_lote' => $movimientoOriginal->id_lote,
                    'id_producto' => $movimientoOriginal->id_producto,
                    'cantidad' => $movimientoOriginal->cantidad,
                    'fecha_vencimiento' => null,
                ]);
            }

            // Registrar movimiento de reversión (ENTRADA)
            return MovimientoInventario::create([
                'id_producto' => $movimientoOriginal->id_producto,
                'id_lote' => $movimientoOriginal->id_lote,
                'tipo_movimiento' => 'ENTRADA',
                'cantidad' => $movimientoOriginal->cantidad,
                'cantidad_anterior' => $stockAnterior,
                'cantidad_nueva' => $stockNuevo,
                'referencia_tipo' => $movimientoOriginal->referencia_tipo,
                'referencia_id' => $movimientoOriginal->referencia_id,
                'observaciones' => "Reversión de movimiento - {$movimientoOriginal->referencia_tipo} #{$movimientoOriginal->referencia_id} eliminado",
                'usuario_id' => Auth::id(),
            ]);
        });
    }
}
