<?php

namespace App\Filament\Resources\EnviosResource\Pages;

use App\Filament\Resources\EnviosResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEnvios extends CreateRecord
{
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
    protected static string $resource = EnviosResource::class;
}
