<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnviosResource\Pages;
use App\Filament\Resources\EnviosResource\RelationManagers;
use App\Models\Envios;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Filters\TrashedFilter;
use Schema;

class EnviosResource extends Resource
{
    protected static ?string $model = Envios::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = "🚚 Gestión de envíos";

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('id_producto')
                                    ->required()
                                    ->searchable()
                                    ->prefixIcon('heroicon-o-shopping-bag')
                                    ->hint('Seleccione un producto')
                                    ->hintIcon('heroicon-m-information-circle')
                                    ->relationship('producto', 'nombre')
                                    ->preload(),

                                Forms\Components\Select::make('id_sucursal')
                                    ->required()
                                    ->searchable()
                                    ->hint('Seleccione una sucursal')
                                    ->hintIcon('heroicon-m-information-circle')
                                    ->prefixIcon('heroicon-o-building-storefront')
                                    ->relationship('sucursal', 'nombre')
                                    ->preload(),

                                Forms\Components\Select::make('sucursal_destino_id')
                                    ->required()
                                    ->searchable()
                                    ->hint('Seleccione una sucursal destino')
                                    ->hintIcon('heroicon-m-information-circle')
                                    ->prefixIcon('heroicon-o-building-storefront')
                                    ->relationship('sucursal_destino', 'nombre')
                                    ->preload(),

                                Forms\Components\TextInput::make('cantidad')
                                    ->label('Cantidad de productos')
                                    ->placeholder('Cantidad a enviar')
                                    ->hint('Cantidad de productos a enviar')
                                    ->hintIcon('heroicon-m-information-circle')
                                    ->prefixIcon('heroicon-o-clipboard-document-list')
                                    ->required()
                                    ->numeric()
                                    ->default(0),

                                Forms\Components\DatePicker::make('fecha_vencimiento')
                                    ->label('Fecha de vencimiento')
                                    ->hint('Fecha de vencimiento del producto')
                                    ->hintIcon('heroicon-m-information-circle')
                                    ->prefixIcon('heroicon-o-calendar')
                                    ->required(),

                                Forms\Components\Textarea::make('observaciones')
                                    ->placeholder('Observaciones del envío')
                                    ->maxLength(255)
                                    ->columnSpanFull()
                                    ->default(null)
                                   
                            ]), // Cierre de Grid::make
                    ]), // Cierre de Card::make
            ]); // Cierre de schema y form
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

            ]);
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
