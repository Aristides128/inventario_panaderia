<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductosResource\Pages;
use App\Models\Productos;
use App\Models\Categorias;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ForceDeleteAction;
use App\Models\MovimientoInventario;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Actions\Action;
use App\Models\lotes;
use App\Models\detalle_lotes;
use Carbon\Carbon;


class ProductosResource extends Resource
{
    protected static ?string $model = Productos::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = "📦 Gestión de productos";

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Grid::make(['default' => 1, 'sm' => 2])
                            ->schema([
                                Forms\Components\TextInput::make('nombre')
                                    ->label('Nombre del Producto')
                                    ->placeholder('Ej: Pan Integral, Pastel de Chocolate...')
                                    ->prefixIcon('heroicon-o-user')
                                    ->required()
                                    ->maxLength(100),

                                Forms\Components\Select::make('id_categoria')
                                    ->label('Categoría')
                                    ->placeholder('Seleccione una categoría')
                                    ->relationship('categoria', 'nombre')
                                    ->preload()
                                    ->searchable()
                                    ->required()
                                    ->prefixIcon('heroicon-o-user'),

                                Forms\Components\Select::make('unidad_medida')
                                    ->placeholder('Seleccione una unidad de medida')
                                    ->label('Unidad de Medida')
                                    ->prefixIcon('heroicon-o-user')
                                    ->options([
                                        'unidad' => 'Unidad',
                                        'kilogramo' => 'Kilogramos (kg)',
                                        'libra' => 'Libras (lb)',
                                        'onza' => 'Onzas (oz)',
                                        'gramo' => 'Gramos (g)',
                                        'litro' => 'Litros (l)',
                                    ])
                                    ->default('unidad')
                                    ->required(),

                                Forms\Components\TextInput::make('precio_base')
                                    ->label('Precio Base (Q)')
                                    ->numeric()
                                    ->prefixIcon('heroicon-o-currency-dollar')
                                    ->required()
                                    ->default(0),

                                Forms\Components\Textarea::make('descripcion')
                                    ->label('Descripción')
                                    ->required()
                                    ->placeholder('Ingrese una descripción detallada del producto')
                                    ->maxLength(255)
                                    ->columnSpan('full')
                                    ->rows(3),
                            ]),
                    ])
                    ->columnSpan('lg'),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre del Producto')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-building-storefront')
                    ->iconColor('primary')
                    ->wrap(),
                Tables\Columns\TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('stock_actual')
                    ->label('Stock')
                    ->numeric()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unidad_medida')
                    ->label('Unidad de Medida')
                    ->searchable()
                    ->sortable()
                    ->visibleFrom('sm'),
                Tables\Columns\TextColumn::make('precio_base')
                    ->label('Precio Base ($)')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-calendar')
                    ->color('gray')
                    ->description(fn(Productos $record) => 'Creado: ' . $record->created_at->diffForHumans())
                    ->tooltip(fn(Productos $record) => 'Creado el ' . $record->created_at->format('d/m/Y \a \l\a\s H:i')),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Fecha de Actualización')
                    ->dateTime()
                    ->sortable()
                    ->icon('heroicon-o-calendar')
                    ->iconColor('gray')
                    ->description(fn(Productos $record) => 'Actualizado: ' . $record->updated_at->diffForHumans())
                    ->tooltip(fn(Productos $record) => 'Actualizado el ' . $record->updated_at->format('d/m/Y \a \l\a\s H:i'))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('ajustar_stock')
                        ->label('Ajustar Stock')
                        ->icon('heroicon-o-adjustments-horizontal')
                        ->color('warning')
                        ->modalHeading('Ajustar Stock del Producto')
                        ->modalDescription('Gestione el stock por lotes (Semanal).')
                        ->modalWidth('lg')
                        ->form([
                            Forms\Components\Select::make('tipo_movimiento')
                                ->label('Tipo de Movimiento')
                                ->options([
                                    'ENTRADA' => 'Entrada (Agregar Stock)',
                                    'SALIDA' => 'Salida (Reducir Stock)',
                                ])
                                ->default('ENTRADA')
                                ->reactive()
                                ->required(),
                            
                            // Solo para entradas: Fecha de Vencimiento
                            Forms\Components\DatePicker::make('fecha_vencimiento')
                                ->label('Fecha de Vencimiento')
                                ->visible(fn (Forms\Get $get) => $get('tipo_movimiento') === 'ENTRADA'),

                            // Solo para salidas: Selección de Lote
                            Forms\Components\Select::make('id_lote_origen')
                                ->label('Seleccionar Lote de Origen')
                                ->options(function (Productos $record) {
                                    return detalle_lotes::where('id_producto', $record->id_producto)
                                        ->where('cantidad', '>', 0)
                                        ->join('lotes', 'detalle_lotes.id_lote', '=', 'lotes.id_lote')
                                        ->orderBy('lotes.anio', 'asc')
                                        ->orderBy('lotes.semana', 'asc')
                                        ->get()
                                        ->mapWithKeys(function ($item) {
                                            return [$item->id_lote => "Lote #{$item->id_lote} (Sem: {$item->semana}, Año: {$item->anio}) - Disp: {$item->cantidad}"];
                                        });
                                })
                                ->required(fn (Forms\Get $get) => $get('tipo_movimiento') === 'SALIDA')
                                ->visible(fn (Forms\Get $get) => $get('tipo_movimiento') === 'SALIDA')
                                ->searchable()
                                ->preload(),

                            Forms\Components\TextInput::make('cantidad')
                                ->label('Cantidad')
                                ->numeric()
                                ->required()
                                ->minValue(1),
                                
                            Forms\Components\Textarea::make('observaciones')
                                ->label('Observaciones')
                                ->rows(2),
                        ])
                        ->action(function (Productos $record, array $data): void {
                            $cantidad = (int) $data['cantidad'];
                            $tipo = $data['tipo_movimiento'];
                            $cantidadAnteriorProducto = $record->stock_actual;
                            $now = Carbon::now();
                            
                            $loteId = null;

                            if ($tipo === 'ENTRADA') {
                                $semana = $now->weekOfYear;
                                $mes = $now->month;
                                $anio = $now->year;

                                $lote = lotes::firstOrCreate(
                                    ['semana' => $semana, 'anio' => $anio],
                                    ['mes' => $mes]
                                );
                                $loteId = $lote->id_lote;

                                $detalleLote = detalle_lotes::firstOrCreate(
                                    ['id_lote' => $lote->id_lote, 'id_producto' => $record->id_producto],
                                    ['cantidad' => 0, 'fecha_vencimiento' => $data['fecha_vencimiento'] ?? null]
                                );

                                $detalleLote->increment('cantidad', $cantidad);
                                
                                $cantidadNuevaProducto = $cantidadAnteriorProducto + $cantidad;
                                $record->update(['stock_actual' => $cantidadNuevaProducto]);

                            } else {
                                $loteId = $data['id_lote_origen'];
                                $detalleLote = detalle_lotes::where('id_lote', $loteId)
                                    ->where('id_producto', $record->id_producto)
                                    ->first();

                                if (!$detalleLote || $detalleLote->cantidad < $cantidad) {
                                    Notification::make()
                                        ->title('Error de Stock')
                                        ->body("El lote seleccionado ya no tiene suficiente cantidad.")
                                        ->danger()
                                        ->send();
                                    return;
                                }

                                $detalleLote->decrement('cantidad', $cantidad);

                                $cantidadNuevaProducto = $cantidadAnteriorProducto - $cantidad;
                                $record->update(['stock_actual' => $cantidadNuevaProducto]);
                            }

                            MovimientoInventario::create([
                                'id_producto' => $record->id_producto,
                                'id_lote' => $loteId,
                                'tipo_movimiento' => $tipo,
                                'cantidad' => $cantidad,
                                'cantidad_anterior' => $cantidadAnteriorProducto,
                                'cantidad_nueva' => $cantidadNuevaProducto,
                                'referencia_tipo' => 'AJUSTE',
                                'observaciones' => $data['observaciones'] ?? 'Ajuste manual de stock',
                                'usuario_id' => Auth::id(),
                            ]);

                            Notification::make()
                                ->title('Stock actualizado correctamente')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\ViewAction::make()
                        ->label('Ver')
                        ->tooltip('Ver Producto')
                        ->icon('heroicon-o-eye')
                        ->color('primary'),

                    Tables\Actions\EditAction::make()
                        ->label('Editar')
                        ->tooltip('Editar Producto')
                        ->color('success')
                        ->visible(fn (Productos $record) => $record->deleted_at === null)
                        ->icon('heroicon-o-pencil'),

                    RestoreAction::make()
                        ->tooltip('Restaurar Producto')
                        ->visible(fn (Productos $record) => $record->deleted_at !== null),

                    Tables\Actions\DeleteAction::make()
                        ->label('Eliminar')
                        ->tooltip('Eliminar Producto')
                        ->color('danger')
                        ->visible(fn (Productos $record) => $record->deleted_at === null)
                        ->icon('heroicon-o-trash'),

                    ForceDeleteAction::make()
                        ->label('Borrado definitivo')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('¿Eliminar Producto?')
                        ->modalDescription('¿Estás seguro de que deseas eliminar este Producto? Esta acción no se puede deshacer.')
                        ->modalSubmitActionLabel('Sí, eliminar')
                        ->modalCancelActionLabel('Cancelar')
                        ->action(fn (Productos $record) => $record->forceDelete())
                        ->tooltip('Eliminar definitivamente'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\RestoreBulkAction::make()
                        ->color('success')
                        ->label('Restaurar registros')
                        ->tooltip('Restaurar Productos'),

                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->color('danger')
                        ->label('Borrar registros definitivamente')
                        ->tooltip('Borrar definitivamente Productos')
                ]),
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
            'index' => Pages\ListProductos::route('/'),
            'create' => Pages\CreateProductos::route('/create'),
            'edit' => Pages\EditProductos::route('/{record}/edit'),
        ];
    }
}
