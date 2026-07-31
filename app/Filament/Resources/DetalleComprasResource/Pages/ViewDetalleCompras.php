<?php

namespace App\Filament\Resources\DetalleComprasResource\Pages;

use App\Filament\Resources\DetalleComprasResource;
use App\Models\Productos;
use App\Models\Proveedores;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewDetalleCompras extends ViewRecord
{
    protected static string $resource = DetalleComprasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Editar Compra')
                ->icon('heroicon-o-pencil')
                ->color('warning'),
            Actions\Action::make('descargar_pdf')
                ->label('Descargar PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn ($record) => route('compras.pdf', ['id' => $record->id_compra]))
                ->openUrlInNewTab(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Header con título
                Infolists\Components\Section::make('Información de la Compra')
                    ->description('Detalles completos de la compra realizada')
                    ->icon('heroicon-o-shopping-cart')
                    ->schema([
                        Infolists\Components\Grid::make(['default' => 1, 'sm' => 3])
                            ->schema([
                                Infolists\Components\TextEntry::make('id_compra')
                                    ->label('ID de Compra')
                                    ->badge()
                                    ->color('primary')
                                    ->prefix('#'),
                                
                                Infolists\Components\TextEntry::make('fecha_compra')
                                    ->label('Fecha de Compra')
                                    ->date('d/m/Y')
                                    ->icon('heroicon-o-calendar'),
                                
                                Infolists\Components\TextEntry::make('estado_compra')
                                    ->label('Estado')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'pendiente' => 'warning',
                                        'Recibido' => 'success',
                                        'cancelado' => 'danger',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'pendiente' => '⏳ Pendiente a recibir',
                                        'Recibido' => '✅ Pedido recibido',
                                        'cancelado' => '❌ Pedido cancelado',
                                        default => $state,
                                    }),
                            ]),
                        
                        Infolists\Components\Grid::make(['default' => 1, 'sm' => 2])
                            ->schema([
                                Infolists\Components\TextEntry::make('Sucursales.nombre')
                                    ->label('Sucursal')
                                    ->icon('heroicon-o-building-storefront')
                                    ->color('info'),
                                
                                Infolists\Components\TextEntry::make('total')
                                    ->label('Total de la Compra')
                                    ->money('USD')
                                    ->icon('heroicon-o-currency-dollar')
                                    ->color('success')
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                    ->weight('bold'),
                            ]),
                        
                        Infolists\Components\TextEntry::make('observaciones')
                            ->label('Observaciones')
                            ->icon('heroicon-o-document-text')
                            ->placeholder('Sin observaciones')
                            ->columnSpanFull(),
                    ]),
                
                // Sección de productos
                Infolists\Components\Section::make('Detalle de Productos')
                    ->description('Lista de productos incluidos en esta compra')
                    ->icon('heroicon-o-cube')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('detalleCompras')
                            ->hiddenLabel()
                            ->schema([
                                Infolists\Components\Card::make()
                                    ->schema([
                                        Infolists\Components\Grid::make(['default' => 1, 'md' => 2])
                                            ->schema([
                                                Infolists\Components\Group::make([
                                                    Infolists\Components\TextEntry::make('producto.nombre')
                                                        ->label('Producto')
                                                        ->icon('heroicon-o-shopping-bag')
                                                        ->color('primary')
                                                        ->weight('bold')
                                                        ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                                                    
                                                    Infolists\Components\TextEntry::make('proveedor.nombre')
                                                        ->label('Proveedor')
                                                        ->icon('heroicon-o-user')
                                                        ->color('gray'),
                                                ]),

                                                Infolists\Components\Group::make([
                                                    Infolists\Components\Grid::make(2)
                                                        ->schema([
                                                            Infolists\Components\TextEntry::make('cantidad_producto')
                                                                ->label('Unid. por Paquete')
                                                                ->badge()
                                                                ->color('info'),

                                                            Infolists\Components\TextEntry::make('cantidad_paquetes')
                                                                ->label('Paquetes')
                                                                ->badge()
                                                                ->color('warning'),
                                                        ]),
                                                    
                                                    Infolists\Components\TextEntry::make('total_unidades')
                                                        ->label('Total Unidades')
                                                        ->state(fn ($record) => ($record->cantidad_paquetes ?? 1) * $record->cantidad_producto)
                                                        ->weight('bold')
                                                        ->suffix(' unidades'),
                                                ]),
                                            ]),
                                        
                                        Infolists\Components\Grid::make(['default' => 1, 'sm' => 3])
                                            ->schema([
                                                Infolists\Components\TextEntry::make('precio_unitario')
                                                    ->label('Precio Unitario')
                                                    ->money('USD')
                                                    ->icon('heroicon-o-tag'),
                                                
                                                Infolists\Components\TextEntry::make('subtotal')
                                                    ->label('Subtotal Item')
                                                    ->money('USD')
                                                    ->weight('black')
                                                    ->color('success')
                                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),

                                                Infolists\Components\TextEntry::make('fecha_vencimiento')
                                                    ->label('Vencimiento')
                                                    ->date('d/m/Y')
                                                    ->icon('heroicon-o-calendar-days')
                                                    ->color(fn ($state) => $state && \Carbon\Carbon::parse($state)->isPast() ? 'danger' : 'gray'),
                                            ])->extraAttributes(['class' => 'mt-4 pt-4 border-t border-gray-100 dark:border-gray-800']),
                                    ])
                            ])
                            ->grid(1)
                            ->columnSpanFull(),
                    ]),
                
                // Sección de resumen final
                Infolists\Components\Section::make('Resumen Total')
                    ->icon('heroicon-o-calculator')
                    ->schema([
                        Infolists\Components\Grid::make(['default' => 1, 'sm' => 3])
                            ->schema([
                                Infolists\Components\TextEntry::make('detalleCompras')
                                    ->label('Total de Productos')
                                    ->state(function ($record) {
                                        return $record->detalleCompras->count();
                                    })
                                    ->badge()
                                    ->color('info')
                                    ->icon('heroicon-o-cube'),
                                
                                Infolists\Components\TextEntry::make('detalleCompras')
                                    ->label('Cantidad Total de Items')
                                    ->state(function ($record) {
                                        return $record->detalleCompras->sum('cantidad_producto');
                                    })
                                    ->badge()
                                    ->color('warning')
                                    ->icon('heroicon-o-clipboard-document-list'),
                                
                                Infolists\Components\TextEntry::make('total')
                                    ->label('TOTAL GENERAL')
                                    ->money('USD')
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                    ->weight('bold')
                                    ->color('success')
                                    ->icon('heroicon-o-currency-dollar'),
                            ]),
                    ]),
            ]);
    }
}
