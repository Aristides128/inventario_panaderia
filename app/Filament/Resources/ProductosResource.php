<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductosResource\Pages;
use App\Models\Productos;
use App\Models\Categorias;
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
                                    ->placeholder('Ej: Pan Integral, Pastel de Chocolate...')
                                    ->hint('Ingrese el nombre del producto')
                                    ->hintIcon('heroicon-m-information-circle')
                                    ->prefixIcon('heroicon-o-user')
                                    ->required()
                                    ->maxLength(100),

                                Forms\Components\Select::make('id_categoria')
                                    ->label('Categoría')
                                    ->placeholder('Seleccione una categoría')
                                    ->relationship('categoria', 'nombre')
                                    ->preload()
                                    ->searchable()
                                    ->required()
                                    ->hint('Seleccione la categoría del producto')
                                    ->hintIcon('heroicon-m-information-circle')
                                    ->prefixIcon('heroicon-o-user'),

                                Forms\Components\Select::make('unidad_medida')
                                    ->placeholder('Seleccione una unidad de medida')
                                    ->label('Unidad de Medida')
                                    ->hint('Seleccione la unidad de medida')
                                    ->hintIcon('heroicon-m-information-circle')
                                    ->prefixIcon('heroicon-o-user')
                                    ->options([
                                        'unidad' => 'Unidad',
                                        'kilogramo' => 'Kilogramos (kg)',
                                        'libra' => 'Libras (lb)',
                                        'onza' => 'Onzas (oz)',
                                        'gramo' => 'Gramos (g)',
                                        'litro' => 'Litros (l)',
                                    ])
                                    ->default('unidad')
                                    ->required(),

                                Forms\Components\Textarea::make('descripcion')
                                    ->label('Descripción')
                                    ->required()
                                    ->hint('Descripción detallada del producto')
                                    ->hintIcon('heroicon-m-information-circle')
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
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-building-storefront')
                    ->iconColor('primary')
                    ->description(fn(Productos $record): string => $record->categoria->nombre ?: 'Sin producto')
                    ->wrap()
                ,
                Tables\Columns\TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->searchable(),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stock')
                    ->numeric()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unidad_medida')
                    ->label('Unidad de Medida')
                    ->searchable()
                    ->sortable(),
               Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable()
                    ->icon('heroicon-o-calendar')
                    ->color('gray')
                    ->description(fn(Productos $record) => 'Creado: ' . $record->created_at->diffForHumans())
                    ->tooltip(fn(Productos $record) => 'Creado el ' . $record->created_at->format('d/m/Y \a \l\a\s H:i')),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Fecha de Actualización')
                    ->dateTime()
                    ->sortable()
                    ->icon('heroicon-o-calendar')
                    ->iconColor('gray')
                    ->description(fn(Productos $record) => 'Actualizado: ' . $record->updated_at->diffForHumans())
                    ->tooltip(fn(Productos $record) => 'Actualizado el ' . $record->updated_at->format('d/m/Y \a \l\a\s H:i'))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([

                 Tables\Actions\ViewAction::make()
                    ->label('Ver')
                    ->tooltip('Ver Producto')
                    ->icon('heroicon-o-eye')
                    ->color('primary'),

                Tables\Actions\EditAction::make()
                    ->label('Editar')
                    ->tooltip('Editar Producto')
                    ->color('success')
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
                    ->color('danger')
                    ->visible(function (Productos $record) {
                        return $record->deleted_at === null;
                    })
                    ->icon('heroicon-o-trash'),

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
