<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DetalleComprasResource\Pages;
use App\Filament\Resources\DetalleComprasResource\RelationManagers;
use App\Models\DetalleCompras;
use App\Models\Productos;
use App\Models\Proveedores;
use App\Models\Sucursales;
use App\Models\Compras;
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

class DetalleComprasResource extends Resource
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
                      ->options(Sucursales::all()->pluck('nombre', 'id_sucursal'))
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
                        Select::make('id_proveedor')
                          ->label('Proveedor del producto')
                          ->placeholder('Seleccione un proveedor')
                          ->options(fn() => Proveedores::all()->pluck('nombre', 'id_proveedor'))
                          ->searchable()
                          ->preload()
                          ->required()
                          ->live()
                          ->hint('Seleccione el proveedor antes de elegir el producto')
                          ->hintIcon('heroicon-m-information-circle')
                          ->prefixIcon('heroicon-o-user')
                          ->afterStateUpdated(fn($state, callable $set) => $set('id_producto', null))
                          ->columnSpan(['md' => 2]),


                        Select::make('id_producto')
                          ->label('Producto disponibles')
                          ->placeholder('Seleccione un producto')
                          ->options(fn() => Productos::all()->pluck('nombre', 'id_producto'))
                          ->searchable()
                          ->preload()
                          ->required()
                          ->live()
                          ->hint('Seleccione un producto para calcular el precio total')
                          ->hintIcon('heroicon-m-information-circle')
                          ->prefixIcon('heroicon-o-shopping-bag')
                          ->afterStateUpdated(function ($state, callable $set, callable $get, $livewire) {
                            // Solo autocompletar cuando estamos EDITANDO (no al crear)
                            if (!isset($livewire->record)) {
                              return; // Estamos creando, no autocompletar
                            }
                            
                            if (!$state) {
                              // Si no hay producto seleccionado, limpiar campos
                              $set('id_proveedor', null);
                              $set('precio_unitario', 1);
                              $set('cantidad_producto', 1);
                              $set('cantidad_paquetes', 1);
                              $set('subtotal', 1);
                              return;
                            }
                            
                            // Buscar el detalle de este producto en la compra actual
                            $detalleActual = DetalleCompras::where('id_producto', $state)
                              ->where('id_compra', $livewire->record->id_compra)
                              ->first();
                            
                            if ($detalleActual) {
                              // Autocompletar con los datos de esta compra
                              $set('id_proveedor', $detalleActual->id_proveedor);
                              $set('precio_unitario', $detalleActual->precio_unitario);
                              $set('cantidad_producto', $detalleActual->cantidad_producto);
                              $set('cantidad_paquetes', $detalleActual->cantidad_paquetes ?? 1);
                              
                              // Calcular subtotal
                              $subtotal = $detalleActual->precio_unitario * $detalleActual->cantidad_producto;
                              $set('subtotal', $subtotal);
                            } else {
                              // Si es un producto nuevo en esta compra, valores por defecto
                              $set('id_proveedor', null);
                              $set('precio_unitario', 1);
                              $set('cantidad_producto', 1);
                              $set('cantidad_paquetes', 1);
                              $set('subtotal', 1);
                            }
                          })
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
                            $cantidad = max(1, $state);
                            $set('cantidad_producto', $cantidad);
                            $set('subtotal', ($get('precio_unitario') ?? 0) * $cantidad);
                          })
                          ->afterStateHydrated(function (callable $set, $state) {
                            if ($state < 1)
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
                            $set('subtotal', $state * ($get('cantidad_producto') ?? 1));
                          })
                          ->columnSpan(['md' => 1]),

                        Forms\Components\TextInput::make('subtotal')
                          ->label('Subtotal (Q)')
                          ->placeholder('Precio total calculado automáticamente')
                          ->numeric()
                        
                          ->prefixIcon('heroicon-o-currency-dollar')
                          ->columnSpan(['md' => 1]),

                        Forms\Components\DatePicker::make('fecha_vencimiento')
                          ->label('Fecha de vencimiento')
                          ->columnSpan(['md' => 1])

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
                        $proveedor = $state['id_proveedor'] ? Proveedores::find($state['id_proveedor']) : null;

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
      'index' => Pages\ListDetalleCompras::route('/'),
      'create' => Pages\CreateDetalleCompras::route('/create'),
      'edit' => Pages\EditDetalleCompras::route('/{record}/edit'),
    ];
  }
}
