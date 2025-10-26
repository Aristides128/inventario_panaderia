<?php

namespace App\Filament\Resources\ComprasResource\Pages;

use App\Filament\Resources\ComprasResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCompras extends CreateRecord
{
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
    protected static string $resource = ComprasResource::class;
}
