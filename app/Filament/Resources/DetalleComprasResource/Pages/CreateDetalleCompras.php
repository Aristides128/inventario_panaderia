<?php

namespace App\Filament\Resources\DetalleComprasResource\Pages;

use App\Filament\Resources\DetalleComprasResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Carbon\Carbon;
use App\Models\lotes;
use App\Models\Compras;
use App\Models\detalle_lotes;
use App\Models\DetalleCompras;
class CreateDetalleCompras extends CreateRecord
{
    protected static string $resource = DetalleComprasResource::class;
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Detalle de compra creado exitosamente';
    }
    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        // Crear compra
        $compra = Compras::create([
            'fecha_compra' => $data['fecha_compra'],
            'id_sucursal' => $data['id_sucursal'],
            'estado_compra' => $data['estado_compra'],
            'observaciones' => $data['observaciones'],
            'total' => collect($data['Produccion'] ?? [])->sum('subtotal'),
        ]);

        // Buscar o crear lote
        $fechaCompra = Carbon::parse($data['fecha_compra']);
        $lote = Lotes::firstOrCreate([
            'semana' => $fechaCompra->weekOfYear,
            'anio' => $fechaCompra->year
        ], [
            'mes' => $fechaCompra->month,
        ]);

        // Servicio de inventario
        $inventarioService = new \App\Services\InventarioService();

        // Crear detalles y registrar movimientos de inventario
        foreach ($data['Produccion'] as $producto) {
            $cantidadTotal = $producto['cantidad_producto'] * ($producto['cantidad_paquetes'] ?? 1);
            
            DetalleCompras::create([
                'id_compra' => $compra->id_compra,
                'id_proveedor' => $producto['id_proveedor'],
                'id_producto' => $producto['id_producto'],
                'cantidad_paquetes' => $producto['cantidad_paquetes'] ?? 1,
                'cantidad_producto' => $producto['cantidad_producto'],
                'precio_unitario' => $producto['precio_unitario'],
                'subtotal' => $producto['subtotal'],
                'fecha_vencimiento' => $producto['fecha_vencimiento'] ?? null,
            ]);

            // Registrar entrada de inventario (esto actualiza stock, detalle_lotes y crea movimiento)
            if ($data['estado_compra'] === 'Recibido') {
                $inventarioService->registrarEntrada(
                    idProducto: $producto['id_producto'],
                    idLote: $lote->id_lote,
                    cantidad: $cantidadTotal,
                    referenciaType: 'COMPRA',
                    referenciaId: $compra->id_compra,
                    observaciones: "Compra #{$compra->id_compra} - " . ($data['observaciones'] ?? '')
            );
            }
        }
        
        return $compra;
    }
}