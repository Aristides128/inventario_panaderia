<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductosResource\Pages;
use App\Models\Productos;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Select;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ForceDeleteAction;


class ProductosResource extends Resource
{
    protected static ?string $model = Productos::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = "📦 Gestión de productos";

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('nombre')
                                    ->label('Nombre del Producto')
                                    ->required()
                                    ->placeholder('Ej: Pan Integral, Pastel de Chocolate...')
                                    ->maxLength(100),

                                Forms\Components\Select::make('id_categoria')
                                    ->label('Categoría')
                                    ->relationship('categoria', 'nombre')
                                    ->preload()
                                    ->searchable()
                                    ->required(),

                                Forms\Components\TextInput::make('cantidad_p')
                                    ->label('Cantidad por Paquete')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->step(1)
                                    ->hint('Número de piezas por paquete'),

                                Forms\Components\Select::make('unidad_medida')
                                    ->label('Unidad de Medida')
                                    ->options([
                                        'unidad' => 'Unidad',
                                        'kg' => 'Kilogramos (kg)',
                                        'g' => 'Gramos (g)',
                                        'pz' => 'Piezas (pz)',
                                        'l' => 'Litros (l)',
                                        'ml' => 'Mililitros (ml)'
                                    ])
                                    ->default('unidad')
                                    ->required(),

                                Forms\Components\Textarea::make('descripcion')
                                    ->label('Descripción')
                                    ->required()
                                    ->placeholder('Ingrese una descripción detallada del producto')
                                    ->maxLength(255)
                                    ->columnSpan('full')
                                    ->rows(3),
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
                    ->label('Nombre del Producto')
                    ->searchable(),
                Tables\Columns\TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->searchable(),
                Tables\Columns\TextColumn::make('id_categoria')
                    ->label('Categoría')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stock')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unidad_medida')
                    ->label('Unidad de Medida'),
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
                    ->tooltip('Editar Producto')
                    ->visible(function (Productos $record) {
                        return $record->deleted_at === null;
                    })
                    ->icon('heroicon-o-pencil'),

                RestoreAction::make()
                    ->tooltip('Restaurar Producto')
                    ->visible(function (Productos $record) {
                        return $record->deleted_at !== null;
                    }),

                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar')
                    ->tooltip('Eliminar Producto')
                    ->visible(function (Productos $record) {
                        return $record->deleted_at === null;
                    })
                    ->icon('heroicon-o-trash'),

                Tables\Actions\ViewAction::make()
                    ->label('Ver')
                    ->tooltip('Ver Producto')
                    ->icon('heroicon-o-eye')
                    ->color('info'),
                ForceDeleteAction::make()
                    ->label('Borrado definitivo')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('¿Eliminar Producto?')
                    ->modalDescription('¿Estás seguro de que deseas eliminar este Producto? Esta acción no se puede deshacer.')
                    ->modalSubmitActionLabel('Sí, eliminar')
                    ->modalCancelActionLabel('Cancelar')
                    ->action(function (Productos $record) {
                        $record->forceDelete();
                    })
                    ->tooltip('Eliminar definitivamente'),

            ])
            ->bulkActions([

                // Restauración multiple de datos eliminados logícamente
                Tables\Actions\RestoreBulkAction::make()
                    ->color('success')
                    ->label('Restaurar registros')
                    ->tooltip('Restaurar Productos')
                ,

                // Borrado definitivo multiple de datos eliminados logícamente
                Tables\Actions\ForceDeleteBulkAction::make()
                    ->color('danger')
                    ->label('Borrar registros definitivamente')
                    ->tooltip('Borrar definitivamente Productos')
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
            'index' => Pages\ListProductos::route('/'),
            'create' => Pages\CreateProductos::route('/create'),
            'edit' => Pages\EditProductos::route('/{record}/edit'),
        ];
    }
}
