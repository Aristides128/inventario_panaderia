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
                                                            ->preload(),

                                                        Forms\Components\TextInput::make('cantidad')
                                                            ->label('Cantidad de productos')
                                                            ->placeholder('Cantidad a enviar')
                                                            ->hint('Cantidad de productos a enviar')
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
                            ->icon('heroicon-o-cube')
                            ->schema([



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
