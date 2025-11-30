<?php

namespace App\Filament\Resources\ComprasResource\Pages;

use App\Models\DetalleCompras;
use App\Models\lotes;
use App\Models\detalle_lotes;
use Filament\Actions\CreateAction;
use App\Filament\Resources\ComprasResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Carbon\Carbon;
class CreateCompras extends CreateRecord
{
    protected static string $resource = ComprasResource::class;


    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }


    protected function afterCreate(): void
    {
        $data = $this->form->getState();
        $compra = $this->record;

        $totalCompra = 0;


        // Detalles de la compra
        foreach ($data['Produccion'] as $producto) {

            $subtotal = $producto['cantidad_producto'] * $producto['precio_unitario'];
            $totalCompra += $subtotal;

            DetalleCompras::create([
                'id_compra' => $compra->id_compra,
                'id_producto' => $producto['id_producto'],
                'id_proveedor' => $producto['proveedor'],
                'cantidad_producto' => $producto['cantidad_producto'],
                'cantidad_paquetes' => $producto['cantidad_paquetes'] ?? 1,
                'precio_unitario' => $producto['precio_unitario'],
                'subtotal' => $subtotal,
                'fecha_vencimiento' => $producto['fecha_vencimiento'] ?? null,
            ]);
        }

        // Lotes
        $hoy = Carbon::now();
        $lote = Lotes::firstOrCreate([
            'semana' => $hoy->weekOfYear,
            'mes' => $hoy->month,
            'anio' => $hoy->year
        ]);

        foreach ($data['Produccion'] as $producto) {
            detalle_lotes::create([
                'id_lote' => $lote->id_lote,
                'id_producto' => $producto['id_producto'],
                'cantidad' => $producto['cantidad_producto'],
                'fecha_vencimiento' => $producto['fecha_vencimiento'] ?? null,
            ]);
        }

        // Actualizar total
        $compra->update([
            'total' => $totalCompra
        ]);
    }
}

