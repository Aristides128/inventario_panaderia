<?php

namespace App\Filament\Resources\DetalleComprasResource\Pages;

use App\Filament\Resources\DetalleComprasResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDetalleCompras extends ListRecords
{
    protected static string $resource = DetalleComprasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
