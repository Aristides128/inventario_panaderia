<?php

namespace App\Filament\Resources\DetalleProduccionesResource\Pages;

use App\Filament\Resources\DetalleProduccionesResource;
use App\Models\DetalleProducciones;
use App\Models\Productos;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateDetalleProducciones extends CreateRecord
{
    protected static string $resource = DetalleProduccionesResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

 
    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        // Obtener el id de producción
        $idProduccion = $data['id_produccion'];
        
        // Servicio de inventario
        $inventarioService = new \App\Services\InventarioService();
        
        // PASO 1: Validar stock de todos los productos ANTES de crear registros
        foreach ($data['detalles'] as $index => $detalle) {
            // Obtener el producto para verificar stock disponible
            $producto = Productos::find($detalle['id_producto']);
            
            if (!$producto) {
                Notification::make()
                    ->title('Producto no encontrado')
                    ->body("El producto con ID {$detalle['id_producto']} no existe.")
                    ->danger()
                    ->send();
                
                $this->halt();
            }
            
            // Verificar si hay stock suficiente
            if ($producto->stock < $detalle['cantidad_utilizada']) {
                Notification::make()
                    ->title('Stock insuficiente')
                    ->body(
                        "No hay suficiente stock para el producto '{$producto->nombre}'. " .
                        "Stock disponible: {$producto->stock_actual}, Cantidad requerida: {$detalle['cantidad_utilizada']}"
                    )
                    ->danger()
                    ->send();
                
                $this->halt();
            }
        }
        
        // PASO 2: Si todas las validaciones pasaron, proceder a crear los registros
        $primerDetalle = null;
        $registrosCreados = 0;
        
        foreach ($data['detalles'] as $detalle) {
            // Registrar salida de inventario (consumo de materia prima)
            $inventarioService->registrarSalida(
                idProducto: $detalle['id_producto'],
                cantidad: $detalle['cantidad_utilizada'],
                referenciaType: 'PRODUCCION',
                referenciaId: $idProduccion,
                observaciones: "Producción #{$idProduccion} - Consumo de materia prima"
            );
            
            // Crear el registro de detalle de producción
            $detalleProduccion = DetalleProducciones::create([
                'id_produccion' => $idProduccion,
                'id_producto' => $detalle['id_producto'],
                'id_empleado' => $detalle['id_empleado'],
                'cantidad_utilizada' => $detalle['cantidad_utilizada'],
            ]);
            
            // Guardar el primer registro para retornarlo
            if ($primerDetalle === null) {
                $primerDetalle = $detalleProduccion;
            }
            
            $registrosCreados++;
        }
        
        // Notificación de éxito con el total de registros creados
        if ($registrosCreados > 1) {
            Notification::make()
                ->title('Detalles creados')
                ->body("Se crearon {$registrosCreados} detalles de producción exitosamente")
                ->success()
                ->send();
        }
        
        // Retornar el primer detalle creado (requerido por Filament)
        return $primerDetalle;
    }
}
