<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DetalleproduccionesResource\Pages;
use App\Models\Detalleproducciones;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Filters\TrashedFilter;

class DetalleproduccionesResource extends Resource
{
    protected static ?string $model = Detalleproducciones::class;

    protected static ?string $navigationIcon = 'heroicon-o-scale';

    protected static ?string $navigationGroup = '⚙️ Gestión de producciones';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                ->schema([
                Forms\Components\Select::make('id_produccion')
                    ->label('Seleccione la Producción')
                    ->hint('')
                    ->placeholder('Seleccione una producción')
                    ->hintIcon('')
                    ->prefixIcon('')
                    ->required()
                    ->preload()
                    ->relationship('produccion', 'observaciones')
                    ->searchable()
                    ->columnSpan('full'),
                Forms\Components\Select::make('id_producto')
                    ->label('Seleccione producto utilizado')
                    ->hint('')
                    ->placeholder('Seleccione un producto')
                    ->hintIcon('')
                    ->prefixIcon('')
                    ->required()
                    ->preload()
                    ->relationship('producto', 'nombre')
                    ->searchable()
                    ->columnSpan('full'),
                Forms\Components\TextInput::make('cantidad_utilizada')
                    ->label('Cantidad utilizada')
                    ->hint('')
                    ->numeric()
                    ->hintIcon('')
                    ->prefixIcon('')
                    ->required()
                   
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id_produccion')
                    ->label('Producción')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('id_producto')
                    ->label('Producto')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cantidad_utilizada')
                    ->label('Cantidad utilizada')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de creación')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Fecha de actualización')
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
                    ->tooltip('Editar detalle de producción')
                    ->visible(function (Detalleproducciones $record) {
                        return $record->deleted_at === null;
                    })
                    ->icon('heroicon-o-pencil'),

                RestoreAction::make()
                    ->tooltip('Restaurar detalle de producción')
                    ->visible(function (Detalleproducciones $record) {
                        return $record->deleted_at !== null;
                    }),

                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar')
                    ->tooltip('Eliminar detalle de producción')
                    ->visible(function (Detalleproducciones $record) {
                        return $record->deleted_at === null;
                    })
                    ->icon('heroicon-o-trash'),

                Tables\Actions\ViewAction::make()
                    ->label('Ver')
                    ->tooltip('Ver detalle de producción')
                    ->icon('heroicon-o-eye')
                    ->color('info'),
                ForceDeleteAction::make()
                    ->label('Borrado definitivo')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('¿Eliminar detalle de producción?')
                    ->modalDescription('¿Estás seguro de que deseas eliminar este detalle de producción? Esta acción no se puede deshacer.')
                    ->modalSubmitActionLabel('Sí, eliminar')
                    ->modalCancelActionLabel('Cancelar')
                    ->action(function (Detalleproducciones $record) {
                        $record->forceDelete();
                    })
                    ->tooltip('Eliminar definitivamente'),

            ])
            ->bulkActions([

                     // Restauración multiple de datos eliminados logícamente
                Tables\Actions\RestoreBulkAction::make()
                    ->color('success')
                    ->label('Restaurar registros')
                    ->tooltip('Restaurar detalle de producción')
                ,

                // Borrado definitivo multiple de datos eliminados logícamente
                Tables\Actions\ForceDeleteBulkAction::make()
                    ->color('danger')
                    ->label('Borrar registros definitivamente')
                    ->tooltip('Borrar definitivamente detalle de producción')
              
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
            'index' => Pages\ListDetalleproducciones::route('/'),
            'create' => Pages\CreateDetalleproducciones::route('/create'),
            'edit' => Pages\EditDetalleproducciones::route('/{record}/edit'),
        ];
    }
}
