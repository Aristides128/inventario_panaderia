<?php

namespace App\Filament\Resources\CategoriasResource\Pages;

use App\Filament\Resources\CategoriasResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCategorias extends CreateRecord
{
    // Redireccionar al listado después de crear
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
    protected static string $resource = CategoriasResource::class;
}
