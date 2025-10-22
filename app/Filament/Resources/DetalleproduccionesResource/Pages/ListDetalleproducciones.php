<?php

namespace App\Filament\Resources\DetalleproduccionesResource\Pages;

use App\Filament\Resources\DetalleproduccionesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDetalleproducciones extends ListRecords
{
    protected static string $resource = DetalleproduccionesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
