<?php

namespace App\Filament\Resources\DetalleProduccionesResource\Pages;

use App\Filament\Resources\DetalleProduccionesResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDetalleProducciones extends CreateRecord
{
    protected static string $resource = DetalleProduccionesResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Detalle de producción creado exitosamente';
    }

    public function afterCreate()
    {
        $data = $this->form->getState();
        $detalle = $this->record;

        // Servicio de inventario
        $inventarioService = new \App\Services\InventarioService();

        // Registrar salida de inventario (consumo de materia prima)
        try {
            $inventarioService->registrarSalida(
                idProducto: $detalle->id_producto,
                cantidad: $detalle->cantidad_utilizada,
                referenciaType: 'PRODUCCION',
                referenciaId: $detalle->id_produccion,
                observaciones: "Producción #{$detalle->id_produccion} - Consumo de materia prima"
            );
        } catch (\Exception $e) {
            // Si hay error (stock insuficiente), lanzar excepción
            throw new \Exception("Error al procesar producción: " . $e->getMessage());
        }
    }
}
