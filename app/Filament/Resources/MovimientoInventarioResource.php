<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MovimientoInventarioResource\Pages;
use App\Filament\Resources\MovimientoInventarioResource\RelationManagers;
use App\Models\MovimientoInventario;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MovimientoInventarioResource extends Resource
{
    protected static ?string $model = MovimientoInventario::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    
    protected static ?string $navigationGroup = '📊 Reportes';
    
    protected static ?string $modelLabel = 'movimiento de inventario';
    protected static ?string $pluralModelLabel = 'Historial de Movimientos';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('id_producto')
                                    ->label('Producto')
                                    ->relationship('producto', 'nombre')
                                    ->disabled()
                                    ->required(),
                                Forms\Components\Select::make('id_lote')
                                    ->label('Lote')
                                    ->relationship('lote', 'id_lote')
                                    ->disabled(),
                                Forms\Components\Select::make('tipo_movimiento')
                                    ->label('Tipo de Movimiento')
                                    ->options([
                                        'ENTRADA' => 'Entrada',
                                        'SALIDA' => 'Salida',
                                    ])
                                    ->disabled()
                                    ->required(),
                                Forms\Components\TextInput::make('cantidad')
                                    ->label('Cantidad')
                                    ->disabled()
                                    ->required()
                                    ->numeric(),
                                Forms\Components\TextInput::make('cantidad_anterior')
                                    ->label('Stock Anterior')
                                    ->disabled()
                                    ->required()
                                    ->numeric(),
                                Forms\Components\TextInput::make('cantidad_nueva')
                                    ->label('Stock Nuevo')
                                    ->disabled()
                                    ->required()
                                    ->numeric(),
                                Forms\Components\Select::make('referencia_tipo')
                                    ->label('Tipo de Referencia')
                                    ->options([
                                        'COMPRA' => 'Compra',
                                        'PRODUCCION' => 'Producción',
                                        'ENVIO' => 'Envío',
                                        'AJUSTE' => 'Ajuste',
                                    ])
                                    ->disabled(),
                                Forms\Components\TextInput::make('referencia_id')
                                    ->label('ID de Referencia')
                                    ->disabled()
                                    ->numeric(),
                                Forms\Components\Textarea::make('observaciones')
                                    ->label('Observaciones')
                                    ->disabled()
                                    ->columnSpanFull(),
                                Forms\Components\Select::make('usuario_id')
                                    ->label('Usuario')
                                    ->relationship('usuario', 'name')
                                    ->disabled(),
                            ]),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->icon('heroicon-o-calendar')
                    ->color('gray'),
                Tables\Columns\TextColumn::make('producto.nombre')
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-cube')
                    ->color('primary'),
                Tables\Columns\BadgeColumn::make('tipo_movimiento')
                    ->label('Tipo')
                    ->colors([
                        'success' => 'ENTRADA',
                        'danger' => 'SALIDA',
                    ])
                    ->icons([
                        'heroicon-o-arrow-down-tray' => 'ENTRADA',
                        'heroicon-o-arrow-up-tray' => 'SALIDA',
                    ]),
                Tables\Columns\TextColumn::make('cantidad')
                    ->label('Cantidad')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cantidad_anterior')
                    ->label('Stock Anterior')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('cantidad_nueva')
                    ->label('Stock Nuevo')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('referencia_tipo')
                    ->label('Referencia')
                    ->colors([
                        'primary' => 'COMPRA',
                        'warning' => 'PRODUCCION',
                        'info' => 'ENVIO',
                        'secondary' => 'AJUSTE',
                    ]),
                Tables\Columns\TextColumn::make('referencia_id')
                    ->label('ID Ref.')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('lote.id_lote')
                    ->label('Lote')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('usuario.name')
                    ->label('Usuario')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('tipo_movimiento')
                    ->label('Tipo de Movimiento')
                    ->options([
                        'ENTRADA' => 'Entrada',
                        'SALIDA' => 'Salida',
                    ]),
                Tables\Filters\SelectFilter::make('referencia_tipo')
                    ->label('Tipo de Referencia')
                    ->options([
                        'COMPRA' => 'Compra',
                        'PRODUCCION' => 'Producción',
                        'ENVIO' => 'Envío',
                        'AJUSTE' => 'Ajuste',
                    ]),
                Tables\Filters\SelectFilter::make('id_producto')
                    ->label('Producto')
                    ->relationship('producto', 'nombre')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->color('primary'),
            ])
            ->bulkActions([
                // No bulk actions - estos son registros de auditoría
            ])
            ->recordUrl(null)
            ->recordAction(null);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMovimientoInventarios::route('/'),
            'view' => Pages\ViewMovimientoInventario::route('/{record}'),
        ];
    }
    
    public static function canCreate(): bool
    {
        return false; // No permitir crear movimientos manualmente
    }
}
