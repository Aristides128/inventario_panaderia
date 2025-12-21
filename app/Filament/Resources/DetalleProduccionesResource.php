<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DetalleProduccionesResource\Pages;
use App\Filament\Resources\DetalleProduccionesResource\RelationManagers;
use App\Models\DetalleProducciones;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Filters\TrashedFilter;

class DetalleProduccionesResource extends Resource
{
    protected static ?string $model = DetalleProducciones::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';


    protected static ?string $navigationGroup = '⚙️ Gestión de producciones';

    protected static ?int $navigationSort = 3;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('id_produccion')
                                    ->label('Producción')
                                    ->placeholder('Seleccione una producción')
                                    ->relationship('Produccion', 'observaciones')
                                    ->prefixIcon('heroicon-o-calendar')
                                    ->hint('Seleccione la producción asociada')
                                    ->hintIcon('heroicon-m-information-circle')
                                    ->hintColor('primary')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Forms\Components\Select::make('id_producto')
                                    ->label('Producto')
                                    ->relationship('Producto', 'nombre')
                                    ->placeholder('Seleccione un producto')
                                    ->prefixIcon('heroicon-o-shopping-bag')
                                    ->hint('Seleccione el producto utilizado')
                                    ->hintIcon('heroicon-m-information-circle')
                                    ->hintColor('primary')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                 Forms\Components\Select::make('id_empleado')
                                    ->label('Empleado que Recibe')
                                    ->relationship('Empleado', 'nombre')
                                    ->placeholder('Seleccione el empleado')
                                    ->prefixIcon('heroicon-o-user')
                                    ->hint('Empleado responsable de recibir el producto')
                                    ->hintIcon('heroicon-m-information-circle')
                                    ->hintColor('primary')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Forms\Components\TextInput::make('cantidad_utilizada')
                                    ->label('Cantidad Utilizada')
                                    ->placeholder('Ingrese la cantidad utilizada')
                                    ->prefixIcon('heroicon-o-clipboard-document-list')
                                    ->hint('Cantidad de producto utilizado en la producción')
                                    ->hintIcon('heroicon-m-information-circle')
                                    ->hintColor('primary')
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->required()
                                    ->columnSpan('full'),
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
                    Tables\Columns\TextColumn::make('Produccion.observaciones')
                        ->label('Producción')
                        ->sortable()
                        ->searchable(),
                    Tables\Columns\TextColumn::make('Producto.nombre')
                        ->label('Producto')
                        ->sortable()
                        ->searchable(),
                    Tables\Columns\TextColumn::make('Empleado.nombre')
                        ->label('Recibido por')
                        ->sortable()
                        ->searchable(),
                    Tables\Columns\TextColumn::make('cantidad_utilizada')
                        ->label('Cantidad')
                        ->numeric()
                        ->sortable(),
                    Tables\Columns\TextColumn::make('created_at')
                        ->dateTime()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                    Tables\Columns\TextColumn::make('updated_at')
                        ->dateTime()
                        ->sortable()
                        ->toggleable(isToggledHiddenByDefault: true),
                    Tables\Columns\TextColumn::make('deleted_at')
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
                    ->tooltip('Editar detalle')
                    ->color('success')
                    ->visible(function (DetalleProducciones $record) {
                        return $record->deleted_at === null;
                    })
                    ->icon('heroicon-o-pencil'),
                RestoreAction::make()
                    ->tooltip('Restaurar detalle')
                    ->visible(function (DetalleProducciones $record) {
                        return $record->deleted_at !== null;
                    }),
                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar')
                    ->tooltip('Eliminar detalle')
                    ->color('danger')
                    ->visible(function (DetalleProducciones $record) {
                        return $record->deleted_at === null;
                    }),

                ForceDeleteAction::make()
                    ->label('Borrar definitivamente')
                    ->tooltip('Eliminar definitivamente detalle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('¿Eliminar detalle?')
                    ->modalDescription('¿Estás seguro de que deseas eliminar este detalle? Esta acción no se puede deshacer.')
                    ->modalSubmitActionLabel('Sí, eliminar')
                    ->modalCancelActionLabel('Cancelar')
                    ->action(function (DetalleProducciones $record) {
                        $record->forceDelete();
                    }),
            ])
            ->bulkActions([
                // Restauración multiple de datos eliminados logícamente
                Tables\Actions\RestoreBulkAction::make()
                    ->color('success')
                    ->label('Restaurar registros')
                    ->tooltip('Restaurar detalles')
                ,

                // Borrado definitivo multiple de datos eliminados logícamente
                Tables\Actions\ForceDeleteBulkAction::make()
                    ->color('danger')
                    ->label('Borrar registros definitivamente')
                    ->tooltip('Borrar definitivamente detalles')

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
            'index' => Pages\ListDetalleProducciones::route('/'),
            'create' => Pages\CreateDetalleProducciones::route('/create'),
            'edit' => Pages\EditDetalleProducciones::route('/{record}/edit'),
        ];
    }
}
