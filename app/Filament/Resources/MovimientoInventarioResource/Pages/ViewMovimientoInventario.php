<?php

namespace App\Filament\Resources\MovimientoInventarioResource\Pages;

use App\Filament\Resources\MovimientoInventarioResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewMovimientoInventario extends ViewRecord
{
    protected static string $resource = MovimientoInventarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No actions - solo visualización de auditoría
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Header con información principal del movimiento
                Infolists\Components\Section::make('Información del Movimiento')
                    ->description('Detalles completos del movimiento de inventario')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('id')
                                    ->label('ID de Movimiento')
                                    ->badge()
                                    ->color('primary')
                                    ->prefix('#'),
                                
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Fecha y Hora')
                                    ->dateTime('d/m/Y H:i:s')
                                    ->icon('heroicon-o-calendar')
                                    ->color('gray'),
                                
                                Infolists\Components\TextEntry::make('tipo_movimiento')
                                    ->label('Tipo de Movimiento')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'ENTRADA' => 'success',
                                        'SALIDA' => 'danger',
                                        default => 'gray',
                                    })
                                    ->icon(fn (string $state): string => match ($state) {
                                        'ENTRADA' => 'heroicon-o-arrow-down-tray',
                                        'SALIDA' => 'heroicon-o-arrow-up-tray',
                                        default => 'heroicon-o-arrows-right-left',
                                    })
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'ENTRADA' => '⬇️ ENTRADA',
                                        'SALIDA' => '⬆️ SALIDA',
                                        default => $state,
                                    })
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                    ->weight('bold'),
                            ]),
                    ]),

                // Sección de producto
                Infolists\Components\Section::make('Información del Producto')
                    ->description('Detalles del producto afectado por este movimiento')
                    ->icon('heroicon-o-cube')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('producto.nombre')
                                    ->label('Producto')
                                    ->icon('heroicon-o-shopping-bag')
                                    ->color('primary')
                                    ->weight('bold')
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                                
                                Infolists\Components\TextEntry::make('producto.categoria.nombre')
                                    ->label('Categoría')
                                    ->icon('heroicon-o-tag')
                                    ->color('info')
                                    ->placeholder('Sin categoría'),
                            ]),
                        
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('producto.unidad_medida')
                                    ->label('Unidad de Medida')
                                    ->icon('heroicon-o-scale')
                                    ->badge()
                                    ->color('gray'),
                                
                                Infolists\Components\TextEntry::make('producto.stock_actual')
                                    ->label('Stock Actual')
                                    ->icon('heroicon-o-cube')
                                    ->badge()
                                    ->color('success')
                                    ->suffix(' unidades'),
                            ]),
                    ]),

                // Sección de cantidades
                Infolists\Components\Section::make('Detalle de Cantidades')
                    ->description('Cambios en el inventario')
                    ->icon('heroicon-o-calculator')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('cantidad_anterior')
                                    ->label('Stock Anterior')
                                    ->icon('heroicon-o-arrow-left')
                                    ->badge()
                                    ->color('gray')
                                    ->suffix(' unidades')
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                                
                                Infolists\Components\TextEntry::make('cantidad')
                                    ->label('Cantidad Movida')
                                    ->icon('heroicon-o-arrows-right-left')
                                    ->badge()
                                    ->color(fn ($record): string => $record->tipo_movimiento === 'ENTRADA' ? 'success' : 'danger')
                                    ->formatStateUsing(fn ($state, $record): string => 
                                        ($record->tipo_movimiento === 'ENTRADA' ? '+' : '-') . $state
                                    )
                                    ->suffix(' unidades')
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                                    ->weight('bold'),
                                
                                Infolists\Components\TextEntry::make('cantidad_nueva')
                                    ->label('Stock Nuevo')
                                    ->icon('heroicon-o-arrow-right')
                                    ->badge()
                                    ->color('success')
                                    ->suffix(' unidades')
                                    ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                            ]),
                    ]),

                // Sección de lote
                Infolists\Components\Section::make('Información del Lote')
                    ->description('Lote asociado a este movimiento')
                    ->icon('heroicon-o-archive-box')
                    ->schema([
                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('lote.id_lote')
                                    ->label('ID de Lote')
                                    ->badge()
                                    ->color('warning')
                                    ->prefix('#'),
                                
                                Infolists\Components\TextEntry::make('lote.semana')
                                    ->label('Semana')
                                    ->icon('heroicon-o-calendar')
                                    ->badge()
                                    ->color('info'),
                                
                                Infolists\Components\TextEntry::make('lote.mes')
                                    ->label('Mes')
                                    ->icon('heroicon-o-calendar')
                                    ->badge()
                                    ->color('info'),
                                
                                Infolists\Components\TextEntry::make('lote.anio')
                                    ->label('Año')
                                    ->icon('heroicon-o-calendar')
                                    ->badge()
                                    ->color('info'),
                            ]),
                    ]),

                // Sección de referencia
                Infolists\Components\Section::make('Referencia del Movimiento')
                    ->description('Origen o motivo del movimiento')
                    ->icon('heroicon-o-link')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('referencia_tipo')
                                    ->label('Tipo de Referencia')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'COMPRA' => 'primary',
                                        'PRODUCCION' => 'warning',
                                        'ENVIO' => 'info',
                                        'AJUSTE' => 'secondary',
                                        default => 'gray',
                                    })
                                    ->icon(fn (string $state): string => match ($state) {
                                        'COMPRA' => 'heroicon-o-shopping-cart',
                                        'PRODUCCION' => 'heroicon-o-archive-box',
                                        'ENVIO' => 'heroicon-o-truck',
                                        'AJUSTE' => 'heroicon-o-wrench-screwdriver',
                                        default => 'heroicon-o-question-mark-circle',
                                    })
                                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                                        'COMPRA' => '🛒 Compra',
                                        'PRODUCCION' => '🏭 Producción',
                                        'ENVIO' => '🚚 Envío',
                                        'AJUSTE' => '🔧 Ajuste Manual',
                                        default => $state ?? 'Sin referencia',
                                    })
                                    ->placeholder('Sin referencia'),
                                
                                Infolists\Components\TextEntry::make('referencia_id')
                                    ->label('ID de Referencia')
                                    ->badge()
                                    ->color('gray')
                                    ->prefix('#')
                                    ->placeholder('N/A'),
                            ]),
                        
                        Infolists\Components\TextEntry::make('observaciones')
                            ->label('Observaciones')
                            ->icon('heroicon-o-document-text')
                            ->placeholder('Sin observaciones')
                            ->columnSpanFull()
                            ->color('gray'),
                    ]),

                // Sección de usuario
                Infolists\Components\Section::make('Información del Usuario')
                    ->description('Usuario que realizó este movimiento')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('usuario.name')
                                    ->label('Usuario')
                                    ->icon('heroicon-o-user-circle')
                                    ->color('primary')
                                    ->weight('bold')
                                    ->placeholder('Sistema'),
                                
                                Infolists\Components\TextEntry::make('usuario.email')
                                    ->label('Correo Electrónico')
                                    ->icon('heroicon-o-envelope')
                                    ->color('gray')
                                    ->placeholder('N/A'),
                            ]),
                    ]),

                // Sección de metadatos
                Infolists\Components\Section::make('Metadatos del Registro')
                    ->description('Información técnica del registro')
                    ->icon('heroicon-o-information-circle')
                    ->collapsed()
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Fecha de Creación')
                                    ->dateTime('d/m/Y H:i:s')
                                    ->icon('heroicon-o-clock')
                                    ->color('gray'),
                                
                                Infolists\Components\TextEntry::make('updated_at')
                                    ->label('Última Actualización')
                                    ->dateTime('d/m/Y H:i:s')
                                    ->icon('heroicon-o-clock')
                                    ->color('gray'),
                                
                                Infolists\Components\TextEntry::make('id')
                                    ->label('ID del Sistema')
                                    ->badge()
                                    ->color('gray')
                                    ->prefix('DB-'),
                            ]),
                    ]),
            ]);
    }
}
