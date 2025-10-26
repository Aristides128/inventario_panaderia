<?php

namespace App\Filament\Resources\ProduccionesResource\Pages;

use App\Filament\Resources\ProduccionesResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProducciones extends CreateRecord
{
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
    protected static string $resource = ProduccionesResource::class;
}
