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

    protected function afterCreate(): void
    {
        $compra = $this->record;
        
        // Recalcular total sumando los subtotales de los detalles ya guardados por Filament
        $total = $compra->detalleCompras()->sum('subtotal');
        $compra->update(['total' => $total]);

        // Registrar entrada de inventario si el estado es 'Recibido'
        if ($compra->estado_compra === 'Recibido') {
            $fechaCompra = Carbon::parse($compra->fecha_compra);
            
            $lote = lotes::firstOrCreate([
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
                    observaciones: "Compra #{$compra->id_compra} - " . ($compra->observaciones ?? '')
                );
            }
        }
    }
}