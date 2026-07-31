<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnviosResource\Pages;
use App\Models\Envios;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Repeater;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Table;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Filters\TrashedFilter;
use App\Models\Productos;
use App\Models\DetalleEnvio;
use App\Models\Sucursales;

class EnviosResource extends Resource
{
    protected static ?string $model = Envios::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = "🚚 Gestión de envíos";

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    protected static ?string $modelLabel = 'nuevo envio';
    protected static ?string $pluralModelLabel = 'Listado de envios';

    protected static ?int $navigationSort = 2;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    // ── Paso 1: Datos Generales ──────────────────────────────────────
                    Forms\Components\Wizard\Step::make('Datos generales')
                        ->label('Datos generales')
                        ->description('Fecha y sucursales origen/destino')
                        ->icon('heroicon-o-truck')
                        ->completedIcon('heroicon-o-check-circle')
                        ->schema([
                            Forms\Components\Grid::make(['default' => 1, 'md' => 3])
                                ->schema([
                                    DatePicker::make('fecha_envio')
                                        ->label('Fecha de envío')
                                        ->placeholder('Seleccione la fecha')
                                        ->required()
                                        ->prefixIcon('heroicon-o-calendar')
                                        ->default(now())
                                        ->closeOnDateSelection()
                                        ->native(false)
                                        ->minDate(now()),

                                    Forms\Components\Select::make('id_sucursal')
                                        ->label('Sucursal Origen')
                                        ->relationship('Sucursales', 'nombre')
                                        ->placeholder('Seleccione sucursal de origen')
                                        ->searchable()
                                        ->prefixIcon('heroicon-o-building-storefront')
                                        ->preload()
                                        ->required(),

                                    Forms\Components\Select::make('sucursal_destino_id')
                                        ->label('Sucursal Destino')
                                        ->relationship('Sucursales', 'nombre')
                                        ->placeholder('Seleccione sucursal de destino')
                                        ->searchable()
                                        ->prefixIcon('heroicon-o-map-pin')
                                        ->preload()
                                        ->required(),
                                ]),
                        ]),

                    // ── Paso 2: Productos ────────────────────────────────────────────
                    Forms\Components\Wizard\Step::make('Productos')
                        ->label('Agregar productos')
                        ->description('Detalle de los productos a enviar')
                        ->icon('heroicon-o-cube')
                        ->completedIcon('heroicon-o-check-circle')
                        ->schema([
                            Forms\Components\Repeater::make('envios')
                                ->label('')
                                ->schema([
                                    Forms\Components\Grid::make(12)
                                        ->schema([
                                            Forms\Components\Select::make('id_producto')
                                                ->label('Producto a enviar')
                                                ->required()
                                                ->searchable()
                                                ->prefixIcon('heroicon-o-shopping-bag')
                                                ->placeholder('Seleccione un producto')
                                                ->options(function () {
                                                    return \App\Models\Productos::all()->mapWithKeys(function ($producto) {
                                                        $formato = $producto->stock_actual > 0 
                                                            ? "{$producto->nombre} (Stock: {$producto->stock_actual})" 
                                                            : "{$producto->nombre} (SIN STOCK)";
                                                        return [$producto->id_producto => $formato];
                                                    });
                                                })
                                                ->afterStateUpdated(function ($state, callable $set) {
                                                    if (!$state) return;
                                                    $set('cantidad', null);
                                                })
                                                ->reactive()
                                                ->preload()
                                                ->columnSpan(['default' => 12, 'md' => 8]),

                                            Forms\Components\TextInput::make('cantidad')
                                                ->label('Cantidad a enviar')
                                                ->placeholder('Ej: 10')
                                                ->prefixIcon('heroicon-o-clipboard-document-list')
                                                ->required()
                                                ->numeric()
                                                ->default(1)
                                                ->minValue(1)
                                                ->rules([
                                                    fn (Forms\Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                                        $productoId = $get('id_producto');
                                                        if (!$productoId) return;

                                                        $producto = \App\Models\Productos::find($productoId);
                                                        if (!$producto) return;

                                                        if ($value > $producto->stock_actual) {
                                                            $fail("Stock insuficiente en lotes. Máximo disponible: {$producto->stock_actual}");
                                                        }
                                                    },
                                                ])
                                                ->columnSpan(['default' => 12, 'md' => 4]),
                                        ])
                                ])
                                ->cloneable()
                                ->createItemButtonLabel('➕ Agregar otro producto')
                                ->defaultItems(1)
                                ->minItems(1)
                                ->collapsible()
                                ->itemLabel(function (array $state): string {
                                    $producto = $state['id_producto'] ? \App\Models\Productos::find($state['id_producto']) : null;
                                    return $producto?->nombre ?? 'Producto no seleccionado';
                                })
                                ->columnSpanFull(),
                        ]),

                    // ── Paso 3: Resumen ──────────────────────────────────────────────
                    Forms\Components\Wizard\Step::make('Resumen')
                        ->label('Resumen y observaciones')
                        ->description('Revisión final antes de guardar')
                        ->icon('heroicon-o-document-check')
                        ->completedIcon('heroicon-o-check-circle')
                        ->schema([
                            Forms\Components\Textarea::make('observaciones')
                                ->label('📝 Observaciones')
                                ->placeholder('Ej: Productos frágiles, horario de entrega preferente, etc.')
                                ->required()
                                ->rows(4)
                                ->columnSpanFull(),
                        ]),

                ])
                ->skippable()
                ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('Sucursales.nombre')
                    ->label('Sucursal Origen')
                    ->sortable()
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('sucursal_destino_id')
                    ->label('Sucursal Destino')
                    ->formatStateUsing(fn ($state) => \App\Models\Sucursales::find($state)?->nombre ?? 'N/A')
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('fecha_envio')
                    ->label('Fecha de Envío')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Fecha de Actualización')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Ver')
                        ->tooltip('Ver envío')
                        ->icon('heroicon-o-eye')
                        ->color('primary'),

                    Tables\Actions\EditAction::make()
                        ->label('Editar')
                        ->tooltip('Editar envío')
                        ->color('success')
                        ->visible(fn (Envios $record) => $record->deleted_at === null)
                        ->icon('heroicon-o-pencil'),

                    RestoreAction::make()
                        ->tooltip('Restaurar envío')
                        ->visible(fn (Envios $record) => $record->deleted_at !== null),

                    Tables\Actions\DeleteAction::make()
                        ->label('Eliminar')
                        ->tooltip('Eliminar envío')
                        ->color('danger')
                        ->visible(fn (Envios $record) => $record->deleted_at === null),

                    ForceDeleteAction::make()
                        ->label('Borrar definitivamente')
                        ->tooltip('Eliminar definitivamente envío')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('¿Eliminar envío?')
                        ->modalDescription('¿Estás seguro de que deseas eliminar esta envío? Esta acción no se puede deshacer.')
                        ->modalSubmitActionLabel('Sí, eliminar')
                        ->modalCancelActionLabel('Cancelar')
                        ->action(fn (Envios $record) => $record->forceDelete()),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\RestoreBulkAction::make()
                        ->color('success')
                        ->label('Restaurar registros')
                        ->tooltip('Restaurar envío'),

                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->color('danger')
                        ->label('Borrar registros definitivamente')
                        ->tooltip('Borrar definitivamente envío'),
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
            'index' => Pages\ListEnvios::route('/'),
            'create' => Pages\CreateEnvios::route('/create'),
            'edit' => Pages\EditEnvios::route('/{record}/edit'),
            'view' => Pages\ViewDetalleEnvios::route('/{record}'),
        ];
    }
}
