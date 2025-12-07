<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SucursalesResource\Pages;
use App\Filament\Resources\SucursalesResource\RelationManagers;
use App\Models\Sucursales;
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
use function Livewire\wrap;

class SucursalesResource extends Resource
{
    protected static ?string $model = Sucursales::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = "🚚 Gestión de envíos";

    protected static ?int $navigationSort = 1;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('nombre')
                                    ->label('Nombre de la Sucursal')
                                    ->placeholder('Ej: Sucursal Centro, Sucursal Norte...')
                                    ->prefixIcon('heroicon-o-building-storefront')
                                    ->hint('Nombre descriptivo de la sucursal')
                                    ->hintIcon('heroicon-m-information-circle')
                                    ->hintColor('primary')
                                    ->required()
                                    ->maxLength(100)
                                    ->columnSpan('full'),

                                Forms\Components\Textarea::make('direccion')
                                    ->label('Dirección de la Sucursal')
                                    ->placeholder('Ingrese la dirección completa de la sucursal')
                                    ->hint('Dirección física de la sucursal')
                                    ->hintIcon('heroicon-m-information-circle')
                                    ->hintColor('primary')
                                    ->required()
                                    ->rows(3)
                                    ->maxLength(255)
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
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre de la Sucursal')
                    ->icon('heroicon-o-truck')
                    ->iconColor('primary')
                    ->wrap()
                    ->searchable()
                    ->sortable()
                    ->tooltip('Haz clic para ordenar por nombre'),

                Tables\Columns\TextColumn::make('direccion')
                    ->label('Dirección')
                    ->searchable()
                    ->icon('heroicon-o-map')
                    ->iconColor('primary')
                    ->wrap()
                    ->sortable()
                    ->tooltip('Haz clic para ordenar por dirección'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime()
                    ->sortable()
                    ->wrap()
                    ->description(fn(Sucursales $record) => 'Creado: ' . $record->created_at->diffForHumans())
                    ->tooltip(fn(Sucursales $record) => 'Creado el ' . $record->created_at->format('d/m/Y \a \l\a\s H:i'))
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Fecha de Actualización')
                    ->dateTime()
                    ->sortable()
                    ->wrap()
                    ->description(fn(Sucursales $record) => 'Actualizado: ' . $record->updated_at->diffForHumans())
                    ->tooltip(fn(Sucursales $record) => 'Actualizado el ' . $record->updated_at->format('d/m/Y \a \l\a\s H:i'))
                    ->toggleable(isToggledHiddenByDefault: true)
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Ver')
                    ->tooltip('Ver sucursal')
                    ->icon('heroicon-o-eye')
                    ->color('primary'),

                Tables\Actions\EditAction::make()
                    ->label('Editar')
                    ->tooltip('Editar sucursal')
                    ->color('success')
                    ->visible(function (Sucursales $record) {
                        return $record->deleted_at === null;
                    })
                    ->icon('heroicon-o-pencil'),

                RestoreAction::make()
                    ->tooltip('Restaurar sucursal')
                    ->visible(function (Sucursales $record) {
                        return $record->deleted_at !== null;
                    }),

                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar')
                    ->tooltip('Eliminar sucursal')
                    ->color('danger')
                    ->visible(function (Sucursales $record) {
                        return $record->deleted_at === null;
                    })
                    ->icon('heroicon-o-trash'),

                ForceDeleteAction::make()
                    ->label('Borrado definitivo')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('¿Eliminar sucursal?')
                    ->modalDescription('¿Estás seguro de que deseas eliminar esta sucursal? Esta acción no se puede deshacer.')
                    ->modalSubmitActionLabel('Sí, eliminar')
                    ->modalCancelActionLabel('Cancelar')
                    ->action(function (Sucursales $record) {
                        $record->forceDelete();
                    })
                    ->tooltip('Eliminar definitivamente'),

            ])
            ->bulkActions([
                // Restauración multiple de datos eliminados logícamente
                Tables\Actions\RestoreBulkAction::make()
                    ->color('success')
                    ->label('Restaurar registros')
                    ->tooltip('Restaurar sucursales')
                ,

                // Borrado definitivo multiple de datos eliminados logícamente
                Tables\Actions\ForceDeleteBulkAction::make()
                    ->color('danger')
                    ->label('Borrar registros definitivamente')
                    ->tooltip('Borrar definitivamente sucursales')
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
            'index' => Pages\ListSucursales::route('/'),
            'create' => Pages\CreateSucursales::route('/create'),
            'edit' => Pages\EditSucursales::route('/{record}/edit'),
        ];
    }
}
