<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ComprasResource\Pages;
use App\Filament\Resources\ComprasResource\RelationManagers;
use App\Models\Compras;
use App\Models\Productos;
use App\Models\Proveedores;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Filters\TrashedFilter;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ComprasResource extends Resource
{
    protected static ?string $model = Compras::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = "📦 Gestión de productos";

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'nueva compra';
    protected static ?string $pluralModelLabel = 'Listado de compras';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Tabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Datos')
                            ->icon('heroicon-o-cube')
                            ->schema([
                                Forms\Components\Card::make('Datos generales')
                                    ->schema([
                                        DatePicker::make('fecha_compra')
                                            ->label('Fecha de la compra')
                                            ->placeholder('Ingrese fecha de la compra')
                                            ->prefixIcon('heroicon-o-calendar')
                                            ->hint('Fecha de la compra')
                                            ->hintIcon('heroicon-m-information-circle')
                                            ->columnSpan(['md' => 2])
                                            ->required()
                                            ->closeOnDateSelection()
                                            ->default(now())
                                            ->native(false),

                                        Select::make('id_sucursal')
                                            ->label('Sucursal a realizar la compra')
                                            ->placeholder('Seleccione una sucursal')
                                            ->relationship('Sucursales', 'nombre')
                                            ->searchable()
                                            ->preload()
                                            ->required(),

                                        Select::make('estado_compra')
                                            ->label('Estado de la compra')
                                            ->placeholder('Seleccione el estado de la compra')
                                            ->options([
                                                'pendiente' => 'Pendiente a recibir',
                                                'Recibido' => 'Pedido recibido',
                                                'cancelado' => 'Pedido cancelado',
                                            ])
                                            ->default('pendiente')
                                            ->required(),
                                    ])
                            ]),

                        // Pestaña de Productos
                        Forms\Components\Tabs\Tab::make('Agregar Productos')
                            ->icon('heroicon-o-shopping-bag')
                            ->schema([
                                Forms\Components\Card::make()
                                    ->schema([
                                        Forms\Components\Repeater::make('Produccion')
                                            
                                            ->label('')
                                            ->schema([
                                                Forms\Components\Select::make('proveedor')
                                                    ->label('Proveedor del producto')
                                                    ->placeholder('Seleccione un proveedor')
                                                    ->options(Proveedores::all()->pluck('nombre', 'id_proveedor'))
                                                    ->searchable()
                                                    ->preload()
                                                    ->required()
                                                    ->hint('Seleccione el proveedor antes de elegir el producto')
                                                    ->hintIcon('heroicon-m-information-circle')
                                                    ->prefixIcon('heroicon-o-user')
                                                    ->afterStateUpdated(fn($state, callable $set) => $set('id_producto', null))
                                                    ->columnSpan(['md' => 2]),


                                                Forms\Components\Select::make('id_producto')
                                                    ->label('Producto disponibles')
                                                    ->placeholder('Seleccione un producto')
                                                     ->options(Productos::all()->pluck('nombre', 'id_producto'))
                                                    ->searchable()
                                                    ->preload()
                                                    ->required()
                                                    ->hint('Seleccione un producto para calcular el precio total')
                                                    ->hintIcon('heroicon-m-information-circle')
                                                    ->prefixIcon('heroicon-o-shopping-bag')

                                                    ->columnSpan(['md' => 2]),

                                                Forms\Components\TextInput::make('cantidad_paquetes')
                                                    ->label('Cantidad de paquetes')
                                                    ->placeholder('Ingrese la cantidad de paquetes')
                                                    ->numeric()
                                                    ->default(1)
                                                    ->minValue(1)
                                                    ->hint('Cantidad de paquetes para calcular el total')
                                                    ->hintIcon('heroicon-m-information-circle')
                                                    ->prefixIcon('heroicon-o-clipboard-document-list')
                                                    ->columnSpan(['md' => 1]),

                                                Forms\Components\TextInput::make('cantidad_producto')
                                                    ->label('Cantidad de productos')
                                                    ->placeholder('Ingrese la cantidad que contiene cada paquete')
                                                    ->numeric()
                                                    ->default(1)
                                                    ->minValue(1)
                                                    ->required()
                                                    ->reactive()
                                                    ->hint('Cantidad de productos por cada paquete. Asegúrese de que sea un valor mayor a 0 para calcular el precio total')
                                                    ->hintIcon('heroicon-m-information-circle')
                                                    ->prefixIcon('heroicon-o-clipboard-document-list')
                                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                                        $cantidad = max(1, (float) $state);
                                                        $set('cantidad_producto', $cantidad);
                                                        $set('precio_total', ($get('precio_unitario') ?? 0) * $cantidad);
                                                    })
                                                    ->afterStateHydrated(function (callable $set, $state) {
                                                        if ((float) $state < 1)
                                                            $set('cantidad_producto', 1);
                                                    })
                                                    ->columnSpan(['md' => 1]),


                                                Forms\Components\TextInput::make('precio_unitario')
                                                    ->label('Precio unitario (Q)')
                                                    ->numeric()
                                                    ->default(1)
                                                    ->minValue(1)
                                                    ->required()
                                                    ->reactive()
                                                    ->hint('Precio unitario del producto')
                                                    ->hintIcon('heroicon-m-information-circle')
                                                    ->prefixIcon('heroicon-o-currency-dollar')
                                                    ->afterStateUpdated(function ($state, callable $set, $get) {
                                                        $set('precio_total', $state * ($get('cantidad_producto') ?? 1));
                                                    })
                                                    ->columnSpan(['md' => 1]),

                                                Forms\Components\TextInput::make('subtotal')
                                                    ->label('Total (Q)')
                                                    ->placeholder('Precio total calculado automáticamente')
                                                    ->numeric()
                                                    ->disabled()
                                                    ->prefixIcon('heroicon-o-currency-dollar')
                                                    ->columnSpan(['md' => 1]),

                                                Forms\Components\DatePicker::make('fecha_vencimiento')
                                                    ->label('Fecha de vencimiento')
                                                    ->columnSpan(['md' => 1])
                                                    ->minDate(now())
                                                    ->placeholder('Ingrese fecha de vencimiento')
                                                    ->displayFormat('d/m/Y')
                                                    ->hint('Requerido para productos perecederos')
                                                    ->hintIcon('heroicon-m-information-circle')
                                                    ->prefixIcon('heroicon-o-calendar')
                                                    ->closeOnDateSelection()
                                                    ->native(false),
                                            ])
                                            ->columns(2)
                                            ->createItemButtonLabel('Agregar producto')

                                            ->defaultItems(1)
                                            ->minItems(1)
                                            ->collapsible()
                                            ->itemLabel(function (array $state): string {
                                                $producto = $state['id_producto'] ? Productos::find($state['id_producto']) : null;
                                                $proveedor = $state['proveedor'] ? Proveedores::find($state['proveedor']) : null;

                                                $nombreProducto = $producto?->nombre ?? ' Producto no seleccionado: ';
                                                $nombreProveedor = $proveedor?->nombre ? " ({$proveedor->nombre})" : '';

                                                return $nombreProducto . ' ' . $nombreProveedor . ' ' . ($state['cantidad'] ?? 1);
                                            })
                                            ->columnSpan('full'),
                                    ]),



                            ]),

                        Forms\Components\Tabs\Tab::make('Resumen')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Forms\Components\Card::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('total_compra')
                                            ->label('Precio total de la compra')
                                            ->numeric()
                                            ->readonly()
                                            ->disabled()
                                            ->reactive()
                                            ->hint('Total compra')
                                            ->hintColor('primary')
                                            ->hintIcon('heroicon-m-information-circle')
                                            ->prefixIcon('heroicon-o-currency-dollar'),
                                    ]),
                                Forms\Components\Textarea::make('observaciones')
                                    ->label('Observaciones')
                                    ->hint('Observaciones de la compra')
                                    ->placeholder('Ingrese observaciones durante la compra ')
                                    ->hintColor('primary')
                                    ->hintIcon('heroicon-m-information-circle')


                            ]),


                    ])
                    ->columnSpan('lg')
                    ->persistTabInQueryString()



            ])
            ->columns(1);


    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
            
                Tables\Columns\TextColumn::make('fecha_compra')
                    ->label('Fecha de compra')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('Sucursales.nombre')
                    ->label('Sucursal')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->label('Precio total de la compra')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado_compra')
                    ->label('Estado de la compra')
                    ->sortable(),

             
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Tables\Actions\EditAction::make()
                    ->label('Editar')
                    ->tooltip('Editar compras')
                    ->visible(function (Compras $record) {
                        return $record->deleted_at === null;
                    })
                    ->icon('heroicon-o-pencil'),

                RestoreAction::make()
                    ->tooltip('Restaurar compras')
                    ->visible(function (Compras $record) {
                        return $record->deleted_at !== null;
                    }),

                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar')
                    ->tooltip('Eliminar compras')
                    ->visible(function (Compras $record) {
                        return $record->deleted_at === null;
                    })
                    ->icon('heroicon-o-trash'),

                Tables\Actions\ViewAction::make()
                    ->label('Ver')
                    ->tooltip('Ver compras')
                    ->icon('heroicon-o-eye')
                    ->color('info'),
                ForceDeleteAction::make()
                    ->label('Borrado definitivo')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('¿Eliminar compras?')
                    ->modalDescription('¿Estás seguro de que deseas eliminar esta compra? Esta acción no se puede deshacer.')
                    ->modalSubmitActionLabel('Sí, eliminar')
                    ->modalCancelActionLabel('Cancelar')
                    ->action(function (Compras $record) {
                        $record->forceDelete();
                    })
                    ->tooltip('Eliminar definitivamente'),
            ])
            ->bulkActions([
                // Restauración multiple de datos eliminados logícamente
                Tables\Actions\RestoreBulkAction::make()
                    ->color('success')
                    ->label('Restaurar registros')
                    ->tooltip('Restaurar compras'),

                // Borrado definitivo multiple de datos eliminados logícamente
                Tables\Actions\ForceDeleteBulkAction::make()
                    ->color('danger')
                    ->label('Borrar registros definitivamente')
                    ->tooltip('Borrar definitivamente compras')
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
            'index' => Pages\ListCompras::route('/'),
            'create' => Pages\CreateCompras::route('/create'),
            'edit' => Pages\EditCompras::route('/{record}/edit'),
        ];
    }
}
