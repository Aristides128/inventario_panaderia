<?php

namespace App\Filament\Resources\EnviosResource\Pages;

use App\Filament\Resources\EnviosResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use App\Models\Sucursales;

class ViewDetalleEnvios extends ViewRecord
{
    protected static string $resource = EnviosResource::class;

      protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Editar Envio')
                ->icon('heroicon-o-pencil')
                ->color('warning'),
            Actions\Action::make('descargar_pdf')
                ->label('Descargar PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn ($record) => route('envios.pdf', ['id' => $record->id_envio]))
                ->openUrlInNewTab(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Información del Envío')
                    ->schema([
                        Infolists\Components\TextEntry::make('Sucursales.nombre')
                            ->label('Sucursal Origen')
                            ->icon('heroicon-o-building-office'),
                        
                        Infolists\Components\TextEntry::make('sucursal_destino_id')
                            ->label('Sucursal Destino')
                            ->formatStateUsing(function ($state) {
                                return Sucursales::find($state)?->nombre ?? 'Desconocida';
                            })
                            ->icon('heroicon-o-building-office-2'),

                        Infolists\Components\TextEntry::make('fecha_envio')
                            ->label('Fecha de Envío')
                            ->date('d/m/Y')
                            ->icon('heroicon-o-calendar'),

                        Infolists\Components\TextEntry::make('observaciones')
                            ->label('Observaciones')
                            ->columnSpanFull(),
                    ])->columns(2),

                Infolists\Components\Section::make('Productos Enviados')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('detalleEnvios')
                            ->schema([
                                Infolists\Components\TextEntry::make('producto.nombre')
                                    ->label('Producto')
                                    ->default('Producto no encontrado'),
                                    
                                Infolists\Components\TextEntry::make('cantidad')
                                    ->label('Cantidad'),
                            ])
                            ->columns(2)
                    ]),
            ]);
    }
}
