<?php

namespace App\Filament\Resources\EnviosResource\Pages;

use App\Filament\Resources\EnviosResource;
use App\Models\DetalleEnvio;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEnvios extends CreateRecord
{
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
    protected static string $resource = EnviosResource::class;

    public function afterCreate()
    {
        $data = $this->form->getState();
        $envios = $this->record;


        foreach ($data['envios'] as $envio) {
            DetalleEnvio::create([
                'id_envio' => $envios->id_envio,
                'id_producto' => $envio['id_producto'] ,
                'cantidad' => $envio['cantidad']
            ]);

        }


    }
}
