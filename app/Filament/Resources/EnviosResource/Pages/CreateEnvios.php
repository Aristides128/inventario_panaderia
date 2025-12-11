<?php

namespace App\Filament\Resources\EnviosResource\Pages;

use App\Filament\Resources\EnviosResource;
use App\Models\DetalleEnvio;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEnvios extends CreateRecord
{
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
    protected static string $resource = EnviosResource::class;

    public function afterCreate()
    {
        $data = $this->form->getState();
        $envios = $this->record;

        // Servicio de inventario
        $inventarioService = new \App\Services\InventarioService();

        foreach ($data['envios'] as $envio) {
            DetalleEnvio::create([
                'id_envio' => $envios->id_envio,
                'id_producto' => $envio['id_producto'],
                'cantidad' => $envio['cantidad']
            ]);

            // Registrar salida de inventario usando FIFO
            try {
                $inventarioService->registrarSalida(
                    idProducto: $envio['id_producto'],
                    cantidad: $envio['cantidad'],
                    referenciaType: 'ENVIO',
                    referenciaId: $envios->id_envio,
                    observaciones: "Envío #{$envios->id_envio} a sucursal"
                );
            } catch (\Exception $e) {
                // Si hay error (stock insuficiente), lanzar excepción
                throw new \Exception("Error al procesar producto: " . $e->getMessage());
            }
        }
    }
}
