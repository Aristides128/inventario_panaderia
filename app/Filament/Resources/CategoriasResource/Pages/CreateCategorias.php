<?php

namespace App\Filament\Resources\CategoriasResource\Pages;

use App\Filament\Resources\CategoriasResource;

use Filament\Resources\Pages\CreateRecord;

class CreateCategorias extends CreateRecord
{
    protected static string $resource = CategoriasResource::class;

    // Redireccionar al listado después de crear
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
