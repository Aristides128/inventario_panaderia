<?php

namespace App\Filament\Resources\DetalleProduccionesResource\Pages;

use App\Filament\Resources\DetalleProduccionesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDetalleProducciones extends EditRecord
{
    protected static string $resource = DetalleProduccionesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
