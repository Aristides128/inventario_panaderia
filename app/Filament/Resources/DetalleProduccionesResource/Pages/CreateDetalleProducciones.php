<?php

namespace App\Filament\Resources\DetalleProduccionesResource\Pages;

use App\Filament\Resources\DetalleProduccionesResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDetalleProducciones extends CreateRecord
{
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected static string $resource = DetalleProduccionesResource::class;
}
