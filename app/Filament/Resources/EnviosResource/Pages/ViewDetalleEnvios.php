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
                Infolists\Components\Grid::make(['default' => 1, 'lg' => 3])
                    ->schema([
                        Infolists\Components\Group::make([
                            Infolists\Components\Section::make('Información del Envío')
                                ->description('Detalles logísticos y temporales')
                                ->icon('heroicon-m-truck')
                                ->schema([
                                    Infolists\Components\TextEntry::make('Sucursales.nombre')
                                        ->label('Sucursal Origen')
                                        ->weight('bold')
                                        ->color('primary')
                                        ->icon('heroicon-o-home-modern')
                                        ->iconColor('primary'),
                                    
                                    Infolists\Components\TextEntry::make('sucursal_destino_id')
                                        ->label('Sucursal Destino')
                                        ->formatStateUsing(fn ($state) => Sucursales::find($state)?->nombre ?? 'Desconocida')
                                        ->weight('bold')
                                        ->color('success')
                                        ->icon('heroicon-o-map-pin')
                                        ->iconColor('success'),

                                    Infolists\Components\TextEntry::make('fecha_envio')
                                        ->label('Fecha de Envío')
                                        ->date('d/M/Y')
                                        ->icon('heroicon-o-calendar-days')
                                        ->color('info'),

                                    Infolists\Components\TextEntry::make('observaciones')
                                        ->label('Observaciones')
                                        ->placeholder('Sin observaciones registradas.')
                                        ->columnSpanFull()
                                        ->prose(),
                                ])->columns(['default' => 1, 'sm' => 2]),
                        ])->columnSpan(['default' => 1, 'lg' => 2]),

                        Infolists\Components\Group::make([
                            Infolists\Components\Section::make('Resumen')
                                ->schema([
                                    Infolists\Components\TextEntry::make('detalleEnvios_count')
                                        ->label('Total de Productos')
                                        ->state(fn ($record) => $record->detalleEnvios()->count())
                                        ->weight('black')
                                        ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                        ->color('warning')
                                        ->suffix(' ítems únicos'),
                                        
                                    Infolists\Components\TextEntry::make('id_envio')
                                        ->label('Folio de Envío')
                                        ->prefix('#')
                                        ->fontFamily('mono')
                                        ->color('gray'),
                                ])
                        ])->columnSpan(['default' => 1, 'lg' => 1]),

                        Infolists\Components\Section::make('Productos Enviados')
                            ->description('Listado de mercancía en tránsito')
                            ->headerActions([
                                Infolists\Components\Actions\Action::make('print')
                                    ->label('Imprimir Tabular')
                                    ->icon('heroicon-m-printer')
                                    ->action(fn() => null)
                                    ->color('gray')
                            ])
                            ->schema([
                                Infolists\Components\RepeatableEntry::make('detalleEnvios')
                                    ->hiddenLabel()
                                    ->schema([
                                        Infolists\Components\Grid::make(['default' => 1, 'sm' => 2])
                                            ->schema([
                                                Infolists\Components\TextEntry::make('producto.nombre')
                                                    ->label('Producto')
                                                    ->weight('bold')
                                                    ->icon('heroicon-o-tag')
                                                    ->default('Producto no encontrado'),
                                                    
                                                Infolists\Components\TextEntry::make('cantidad')
                                                    ->label('Cantidad Enviada')
                                                    ->badge()
                                                    ->color('success')
                                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                                    ->icon('heroicon-m-archive-box'),
                                            ])
                                    ])
                                    ->grid(['default' => 1, 'sm' => 2])
                                    ->columnSpanFull(),
                            ])->columnSpanFull(),
                    ])
            ]);
    }
}
