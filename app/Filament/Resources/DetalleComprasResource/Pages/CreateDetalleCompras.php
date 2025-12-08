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
    protected function handleRecordCreation(array $data): DetalleCompras
    {
        
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

        // Crear detalles
        foreach ($data['Produccion'] as $producto) {
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

            detalle_lotes::create([
                'id_lote' => $lote->id_lote,
                'id_producto' => $producto['id_producto'],
                'cantidad' => $producto['cantidad_producto'] * ($producto['cantidad_paquetes'] ?? 1),
                'fecha_vencimiento' => $producto['fecha_vencimiento'] ?? null,
            ]);
        }
        $detalle_compra = DetalleCompras::latest()->first();
        return $detalle_compra;
    }

}
