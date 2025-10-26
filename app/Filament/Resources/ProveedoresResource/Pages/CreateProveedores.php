<?php

namespace App\Filament\Resources\ProveedoresResource\Pages;

use App\Filament\Resources\ProveedoresResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProveedores extends CreateRecord
{
    // Redireccionar al listado después de crear
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
    protected static string $resource = ProveedoresResource::class;
}
