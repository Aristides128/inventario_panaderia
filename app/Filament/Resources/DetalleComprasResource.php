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
                              
                              // Calcular subtotal: (cantidad_paquetes * cantidad_producto) * precio_unitario
                              $subtotal = ($detalleActual->cantidad_paquetes ?? 1) * $detalleActual->cantidad_producto * $detalleActual->precio_unitario;
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
                          ->required()
                          ->reactive()
                          ->hint('Cantidad de paquetes para calcular el total')
                          ->hintIcon('heroicon-m-information-circle')
                          ->prefixIcon('heroicon-o-clipboard-document-list')
                          ->afterStateUpdated(function ($state, callable $set, $get) {
                            $cantidadPaquetes = max(1, $state ?? 1);
                            $cantidadProducto = max(1, $get('cantidad_producto') ?? 1);
                            $precioUnitario = $get('precio_unitario') ?? 0;
                            $set('subtotal', ($cantidadPaquetes * $cantidadProducto) * $precioUnitario);
                          })
                          ->columnSpan(['md' => 1]),

                        Forms\Components\TextInput::make('cantidad_producto')
                          ->label('Cantidad de productos')
                          ->placeholder('Ingrese la cantidad que contiene cada paquete')
                          ->numeric()
                          ->default(1)
                          ->minValue(1)
                          ->required()
                          ->reactive()
                          ->hint('Unidades por paquete. El total se calcula: Paquetes × Unidades/Paquete × Precio')
                          ->hintIcon('heroicon-m-information-circle')
                          ->prefixIcon('heroicon-o-clipboard-document-list')
                          ->afterStateUpdated(function ($state, callable $set, $get) {
                            $cantidad = max(1, $state);
                            $set('cantidad_producto', $cantidad);
                            $cantidadPaquetes = max(1, $get('cantidad_paquetes') ?? 1);
                            $precioUnitario = $get('precio_unitario') ?? 0;
                            $set('subtotal', ($cantidadPaquetes * $cantidad) * $precioUnitario);
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
                            $cantidadPaquetes = max(1, $get('cantidad_paquetes') ?? 1);
                            $cantidadProducto = max(1, $get('cantidad_producto') ?? 1);
                            $set('subtotal', ($cantidadPaquetes * $cantidadProducto) * $state);
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
                    Forms\Components\Placeholder::make('resumen_titulo')
                      ->label('')
                      ->content(new \Illuminate\Support\HtmlString('<h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">📋 Resumen de la Compra</h2>'))
                      ->columnSpanFull(),
              
                    // Observaciones
                    Forms\Components\Card::make()
                      ->schema([
                        Forms\Components\Textarea::make('observaciones')
                          ->label('📝 Observaciones')
                          ->hint('Observaciones de la compra')
                          ->placeholder('Ingrese observaciones durante la compra')
                          ->hintColor('primary')
                          ->hintIcon('heroicon-m-information-circle')
                          ->rows(3),
                      ])
                      ->columnSpanFull(),
                    
                   
                    // Botón para descargar PDF (solo visible en modo view)
                    Forms\Components\Placeholder::make('descargar_pdf')
                      ->label('')
                      ->content(function ($livewire) {
                        // Detectar si estamos en modo "view" (observar)
                        $isViewMode = str_contains(get_class($livewire), 'ViewDetalleCompras');
                        
                        // Si no estamos en modo view, no mostrar nada
                        if (!$isViewMode) {
                          return null;
                        }
                        
                        $recordId = $livewire->record->id_compra ?? null;
                        
                        if (!$recordId) {
                          return new \Illuminate\Support\HtmlString('
                            <div class="flex justify-center mt-6">
                              <div class="inline-flex items-center px-6 py-3 bg-gray-400 text-white font-semibold rounded-lg shadow-md">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                No se puede generar el PDF
                              </div>
                            </div>
                          ');
                        }
                        
                        $pdfUrl = route('compras.pdf', ['id' => $recordId]);
                        
                        return new \Illuminate\Support\HtmlString('
                          <div class="flex justify-center mt-6">
                            <a 
                              href="' . $pdfUrl . '" 
                              target="_blank"
                              class="inline-flex items-center px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg shadow-md transition duration-150 ease-in-out"
                            >
                              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                              </svg>
                              Descargar Reporte en PDF
                            </a>
                          </div>
                        ');
                      })
                      ->columnSpanFull()
                      ->hidden(fn ($livewire) => !str_contains(get_class($livewire), 'ViewDetalleCompras')),
                  ])
                  ->columnSpanFull(),
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
      'view' => Pages\ViewDetalleCompras::route('/{record}'),
      'edit' => Pages\EditDetalleCompras::route('/{record}/edit'),
    ];
  }
}
