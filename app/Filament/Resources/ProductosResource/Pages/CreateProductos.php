<?php

namespace App\Filament\Resources\ProductosResource\Pages;

use App\Filament\Resources\ProductosResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProductos extends CreateRecord
{
    // Redireccionar al listado después de crear
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
    protected static string $resource = ProductosResource::class;
}
