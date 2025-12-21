<?php

namespace App\Filament\Resources\ProduccionesResource\Pages;

use App\Filament\Resources\ProduccionesResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProducciones extends ViewRecord
{
    protected static string $resource = ProduccionesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Editar Producción')
                ->icon('heroicon-o-pencil')
                ->color('warning'),
            Actions\Action::make('descargar_pdf')
                ->label('Descargar PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn ($record) => route('producciones.pdf', ['id' => $record->id_produccion]))
                ->openUrlInNewTab(),
        ];
    }
}
