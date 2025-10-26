<?php

namespace App\Filament\Resources\DetalleproduccionesResource\Pages;

use App\Filament\Resources\DetalleproduccionesResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDetalleproducciones extends CreateRecord
{
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    protected static string $resource = DetalleproduccionesResource::class;
}
