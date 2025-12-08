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

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = "🚚 Gestión de envíos";

    protected static ?string $modelLabel = 'nuevo envio';
    protected static ?string $pluralModelLabel = 'Listado de envios';

    protected static ?int $navigationSort = 2;
    protected static ?string $view = '';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Tabs')
                    ->tabs([


                        // Datos generales del envío
                        Forms\Components\Tabs\Tab::make('Datos_envio')
                            ->icon('heroicon-o-truck')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\Card::make()
                                            ->schema([
                                                Forms\Components\Select::make('id_sucursal')
                                                    ->label('Sucursal Origen')
                                                    ->relationship('Sucursales', 'nombre')
                                                    ->searchable()
                                                    ->prefixIcon('heroicon-o-truck')

                                                    ->hint('Ingrese sucursal de origen')
                                                    ->hintIcon('heroicon-m-information-circle')
                                                    ->hintColor('primary')
                                                    ->preload()
                                                    ->required()
                                                    ->columnSpanFull(),
                                            ]),

                                        Forms\Components\Card::make()
                                            ->schema([
                                                Forms\Components\Select::make('sucursal_destino_id')
                                                    ->label('Sucursal Destino')
                                                    ->relationship('Sucursales', 'nombre')
                                                    ->searchable()
                                                    ->prefixIcon('heroicon-o-truck')

                                                    ->hint('Ingrese sucursal de destino')
                                                    ->hintColor('primary')
                                                    ->hintIcon('heroicon-m-information-circle')

                                                    ->preload()
                                                    ->required()
                                                    ->columnSpanFull(),
                                            ]),
                                    ]),

                                Forms\Components\Card::make()
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                DatePicker::make('fecha_envio')
                                                    ->label('Fecha de envío')
                                                    ->required()
                                                    ->prefixIcon('heroicon-o-calendar')
                                                    ->default(now())
                                                    ->closeOnDateSelection()
                                                    ->hint('Ingrese fecha de envio')
                                                    ->hintColor('primary')
                                                    ->hintIcon('heroicon-m-information-circle')
                                                    ->native(false)
                                                    ->minDate(now()),
                                            ]),
                                    ]),

                                Forms\Components\Card::make()
                                    ->schema([
                                        Forms\Components\Textarea::make('observaciones')
                                            ->label('Observaciones')
                                            ->hint('Ingrese sucursal de destino')
                                            ->hintColor('primary')
                                            ->hintIcon('heroicon-m-information-circle')
                                            ->placeholder('Ingrese cualquier observación relevante sobre el envío')
                                            ->helperText('Ej: Productos frágiles, horario de entrega preferente, etc.')
                                            ->required()
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // Segundo paso
                        Forms\Components\Tabs\Tab::make('Productos')
                            ->icon('heroicon-o-cube')
                            ->schema([
                                Forms\Components\Card::make()
                                    ->schema([
                                        Forms\Components\Repeater::make('envios')
                                            ->schema([
                                                Forms\Components\Grid::make(2)
                                                    ->schema([
                                                        Forms\Components\Select::make('id_producto')
                                                        ->label('Productos disponibles')
                                                            ->required()
                                                            ->searchable()
                                                            ->prefixIcon('heroicon-o-shopping-bag')
                                                            ->placeholder('Seleccione un producto')
                                                            ->hintIcon('heroicon-m-information-circle')
                                                            ->options(Productos::all()->pluck('nombre', 'id_producto'))
                                                            ->afterStateUpdated(function ($state, callable $set, callable $get, $livewire) {
                          
                            if (!isset($livewire->record)) {
                              return; // Estamos creando, no autocompletar
                            }
                            
                            if (!$state) {
                              // Si no hay producto seleccionado, limpiar campos
                              $set('cantidad', null);
                              return;
                            }
                            
                            // Buscar el detalle de este producto en el envío actual
                            $detalleActual = DetalleEnvio::where('id_producto', $state)
                              ->where('id_envio', $livewire->record->id_envio)
                              ->first();
                            
                            if ($detalleActual) {
                              // Autocompletar con la cantidad registrada en este envío
                              $set('cantidad', $detalleActual->cantidad);
                            } else {
                              // Si es un producto nuevo en este envío, limpiar
                              $set('cantidad', null);
                            }
                          })
                                                            ->reactive()
                                                            ->preload(),

                                                        Forms\Components\TextInput::make('cantidad')
                                                            ->label('Cantidad de productos')
                                                            ->placeholder('Cantidad a enviar')
                                                            ->hintIcon('heroicon-m-information-circle')
                                                            ->prefixIcon('heroicon-o-clipboard-document-list')
                                                            ->required()
                                                            ->numeric()
                                                            ->default(0)

                                                    ])
                                            ])
                                            ->createItemButtonLabel('Agregar otro envío')
                                            ->defaultItems(2)
                                            ->addActionLabel('Agregar otro envío')
                                            ->columns(1)
                                            ->columnSpanFull()
                                            ->collapsible(),

                                    ]),
                            ]),

                        Forms\Components\Tabs\Tab::make('Generar reporte de envio')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Forms\Components\Card::make()
                                    ->schema([
                                        Forms\Components\Placeholder::make('resumen_titulo')
                                            ->label('')
                                            ->content(new \Illuminate\Support\HtmlString('<h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">📋 Resumen del Envío</h2>'))
                                            ->columnSpanFull(),
                                        
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                // Información de Sucursales
                                                Forms\Components\Card::make()
                                                    ->schema([
                                                        Forms\Components\Placeholder::make('sucursal_origen_resumen')
                                                            ->label('🏢 Sucursal Origen')
                                                            ->content(function ($get) {
                                                                $sucursalId = $get('id_sucursal');
                                                                if ($sucursalId) {
                                                                    $sucursal = \App\Models\Sucursales::find($sucursalId);
                                                                    return $sucursal ? $sucursal->nombre : 'No seleccionada';
                                                                }
                                                                return 'No seleccionada';
                                                            }),
                                                        
                                                        Forms\Components\Placeholder::make('sucursal_destino_resumen')
                                                            ->label('🎯 Sucursal Destino')
                                                            ->content(function ($get) {
                                                                $sucursalId = $get('sucursal_destino_id');
                                                                if ($sucursalId) {
                                                                    $sucursal = \App\Models\Sucursales::find($sucursalId);
                                                                    return $sucursal ? $sucursal->nombre : 'No seleccionada';
                                                                }
                                                                return 'No seleccionada';
                                                            }),
                                                    ]),
                                                
                                                // Información de Fecha y Observaciones
                                                Forms\Components\Card::make()
                                                    ->schema([
                                                        Forms\Components\Placeholder::make('fecha_envio_resumen')
                                                            ->label('📅 Fecha de Envío')
                                                            ->content(function ($get) {
                                                                $fecha = $get('fecha_envio');
                                                                return $fecha ? \Carbon\Carbon::parse($fecha)->format('d/m/Y') : 'No especificada';
                                                            }),
                                                        
                                                        Forms\Components\Placeholder::make('observaciones_resumen')
                                                            ->label('📝 Observaciones')
                                                            ->content(function ($get) {
                                                                $obs = $get('observaciones');
                                                                return $obs ?: 'Sin observaciones';
                                                            }),
                                                    ]),
                                            ]),
                                        
                                        // Resumen de Productos
                                        Forms\Components\Card::make()
                                            ->schema([
                                                Forms\Components\Placeholder::make('productos_resumen')
                                                    ->label('📦 Productos a Enviar')
                                                    ->content(function ($get) {
                                                        $envios = $get('envios');
                                                        if (!$envios || count($envios) === 0) {
                                                            return new \Illuminate\Support\HtmlString('<p class="text-gray-500">No hay productos agregados</p>');
                                                        }
                                                        
                                                        $html = '<div class="space-y-2">';
                                                        $html .= '<table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">';
                                                        $html .= '<thead class="bg-gray-50 dark:bg-gray-800">';
                                                        $html .= '<tr>';
                                                        $html .= '<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Producto</th>';
                                                        $html .= '<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cantidad</th>';
                                                        $html .= '</tr>';
                                                        $html .= '</thead>';
                                                        $html .= '<tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">';
                                                        
                                                        $totalProductos = 0;
                                                        foreach ($envios as $envio) {
                                                            if (isset($envio['id_producto']) && isset($envio['cantidad'])) {
                                                                $producto = \App\Models\Productos::find($envio['id_producto']);
                                                                $nombreProducto = $producto ? $producto->nombre : 'Producto desconocido';
                                                                $cantidad = $envio['cantidad'];
                                                                $totalProductos += $cantidad;
                                                                
                                                                $html .= '<tr>';
                                                                $html .= '<td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">' . htmlspecialchars($nombreProducto) . '</td>';
                                                                $html .= '<td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">' . htmlspecialchars($cantidad) . '</td>';
                                                                $html .= '</tr>';
                                                            }
                                                        }
                                                        
                                                        $html .= '</tbody>';
                                                        $html .= '<tfoot class="bg-gray-50 dark:bg-gray-800">';
                                                        $html .= '<tr>';
                                                        $html .= '<td class="px-4 py-3 text-sm font-bold text-gray-900 dark:text-gray-100">Total de Productos</td>';
                                                        $html .= '<td class="px-4 py-3 text-sm font-bold text-gray-900 dark:text-gray-100">' . $totalProductos . '</td>';
                                                        $html .= '</tr>';
                                                        $html .= '</tfoot>';
                                                        $html .= '</table>';
                                                        $html .= '</div>';
                                                        
                                                        return new \Illuminate\Support\HtmlString($html);
                                                    })
                                                    ->columnSpanFull(),
                                            ])
                                            ->columnSpanFull(),
                                        
                                        // Botón para descargar PDF
                                        Forms\Components\Placeholder::make('descargar_pdf')
                                            ->label('')
                                            ->content(function ($livewire) {
                                                $recordId = $livewire->record->id_envio ?? null;
                                                
                                                if (!$recordId) {
                                                    return new \Illuminate\Support\HtmlString('
                                                        <div class="flex justify-center mt-6">
                                                            <div class="inline-flex items-center px-6 py-3 bg-gray-400 text-white font-semibold rounded-lg shadow-md">
                                                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                                </svg>
                                                                Guarde el envío primero para descargar el PDF
                                                            </div>
                                                        </div>
                                                    ');
                                                }
                                                
                                                $pdfUrl = route('envios.pdf', ['id' => $recordId]);
                                                
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
                                            ->columnSpanFull(),
                                    ])
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpan('full')
                    ->persistTabInQueryString(),

            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id_producto')
                    ->label('Producto')
                    ->sortable(),
                Tables\Columns\TextColumn::make('id_sucursal')
                    ->label('Sucursal')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sucursal_destino_id')
                    ->label('Sucursal Destino')
                    ->sortable(),
                Tables\Columns\TextColumn::make('cantidad')
                    ->label('Cantidad')
                    ->sortable(),
                Tables\Columns\TextColumn::make('observaciones')
                    ->label('Observaciones')
                    ->searchable(),
                Tables\Columns\TextColumn::make('fecha_vencimiento')
                    ->label('Fecha de Vencimiento')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Fecha de Actualización')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([
                //
                TrashedFilter::make(),
            ])
            ->actions([

                Tables\Actions\ViewAction::make()
                    ->label('Ver')
                    ->tooltip('Ver envío')
                    ->icon('heroicon-o-eye')
                    ->color('primary'),

                Tables\Actions\EditAction::make()
                    ->label('Editar')
                    ->tooltip('Editar envío')
                    ->color('success')
                    ->visible(function (Envios $record) {
                        return $record->deleted_at === null;
                    })
                    ->icon('heroicon-o-pencil'),
                RestoreAction::make()
                    ->tooltip('Restaurar envío')
                    ->visible(function (Envios $record) {
                        return $record->deleted_at !== null;
                    }),
                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar')
                    ->tooltip('Eliminar envío')
                    ->color('danger')
                    ->visible(function (Envios $record) {
                        return $record->deleted_at === null;
                    }),

                ForceDeleteAction::make()
                    ->label('Borrar definitivamente')
                    ->tooltip('Eliminar definitivamente envío')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('¿Eliminar envío?')
                    ->modalDescription('¿Estás seguro de que deseas eliminar esta envío? Esta acción no se puede deshacer.')
                    ->modalSubmitActionLabel('Sí, eliminar')
                    ->modalCancelActionLabel('Cancelar')
                    ->action(function (Envios $record) {
                        $record->forceDelete();
                    }),
            ])
            ->bulkActions([
                // Restauración multiple de datos eliminados logícamente
                Tables\Actions\RestoreBulkAction::make()
                    ->color('success')
                    ->label('Restaurar registros')
                    ->tooltip('Restaurar envío')
                ,

                // Borrado definitivo multiple de datos eliminados logícamente
                Tables\Actions\ForceDeleteBulkAction::make()
                    ->color('danger')
                    ->label('Borrar registros definitivamente')
                    ->tooltip('Borrar definitivamente envío')

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
        ];
    }
}
