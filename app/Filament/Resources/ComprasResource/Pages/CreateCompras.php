<?php

namespace App\Filament\Resources\ComprasResource\Pages;

use App\Models\lotes;
use App\Models\detalle_lotes;
use App\Models\Compras;
use Filament\Actions\CreateAction;
use App\Filament\Resources\ComprasResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCompras extends CreateRecord
{
    protected static string $resource = ComprasResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
{
    // Los datos del formulario están en $data
    // Puedes procesarlos aquí antes de crear el registro
    // Por ejemplo:
    // $data['total'] = calcularTotal($data);
    
    return $data;
}


}

