<?php

namespace App\Filament\Resources\DetalleProduccionesResource\Pages;

use App\Filament\Resources\DetalleProduccionesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDetalleProducciones extends ListRecords
{
    protected static string $resource = DetalleProduccionesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
