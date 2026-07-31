<?php

namespace App\Filament\Resources\MovimientoInventarioResource\Pages;

use App\Filament\Resources\MovimientoInventarioResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms;

class ListMovimientoInventarios extends ListRecords
{
    protected static string $resource = MovimientoInventarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generar_pdf_semanal')
                ->label('Generar Reporte PDF Semanal')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->form([
                    Forms\Components\Grid::make(['default' => 1, 'sm' => 2])
                        ->schema([
                            Forms\Components\TextInput::make('semana')
                                ->label('Semana')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(53)
                                ->default(now()->weekOfYear)
                                ->required()
                                ->prefixIcon('heroicon-o-calendar'),
                            
                            Forms\Components\TextInput::make('anio')
                                ->label('Año')
                                ->numeric()
                                ->minValue(2020)
                                ->maxValue(2100)
                                ->default(now()->year)
                                ->required()
                                ->prefixIcon('heroicon-o-calendar'),
                        ]),
                ])
                ->action(function (array $data) {
                    // Redirigir a la ruta del PDF con los parámetros
                    return redirect()->route('movimientos.pdf', [
                        'semana' => $data['semana'],
                        'anio' => $data['anio'],
                    ]);
                })
                ->modalHeading('Generar Reporte de Movimientos')
                ->modalDescription('Seleccione la semana y el año para generar el reporte PDF de movimientos de inventario.')
                ->modalSubmitActionLabel('Generar PDF')
                ->modalCancelActionLabel('Cancelar')
                ->modalWidth('md'),
        ];
    }
}
