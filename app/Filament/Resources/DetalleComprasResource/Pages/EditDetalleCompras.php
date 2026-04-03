<?php

namespace App\Filament\Resources\DetalleComprasResource\Pages;

use App\Filament\Resources\DetalleComprasResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDetalleCompras extends EditRecord
{
    protected static string $resource = DetalleComprasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function afterSave(): void
    {
        $compra = $this->record;

        // Recalcular el total basado en los productos actualizados por Filament
        $total = $compra->detalleCompras()->sum('subtotal');
        if ($compra->total !== $total) {
            $compra->updateQuietly(['total' => $total]);
        }

        // Registrar entrada de inventario SÓLO si el estado ACABA de cambiar a 'Recibido'
        if ($compra->wasChanged('estado_compra') && $compra->estado_compra === 'Recibido') {
            $fechaCompra = \Carbon\Carbon::parse($compra->fecha_compra);
            
            $lote = \App\Models\Lotes::firstOrCreate([
                'semana' => $fechaCompra->weekOfYear,
                'anio' => $fechaCompra->year
            ], [
                'mes' => $fechaCompra->month,
            ]);

            $inventarioService = new \App\Services\InventarioService();

            foreach ($compra->detalleCompras as $producto) {
                $cantidadTotal = $producto->cantidad_producto * ($producto->cantidad_paquetes ?? 1);
                
                $inventarioService->registrarEntrada(
                    idProducto: $producto->id_producto,
                    idLote: $lote->id_lote,
                    cantidad: $cantidadTotal,
                    referenciaType: 'COMPRA',
                    referenciaId: $compra->id_compra,
                    observaciones: "Compra #{$compra->id_compra} (Edición) - " . ($compra->observaciones ?? '')
                );
            }
        }
    }
}
