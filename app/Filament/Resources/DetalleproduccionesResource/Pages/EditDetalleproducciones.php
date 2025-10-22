<?php

namespace App\Filament\Resources\DetalleproduccionesResource\Pages;

use App\Filament\Resources\DetalleproduccionesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDetalleproducciones extends EditRecord
{
    protected static string $resource = DetalleproduccionesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
