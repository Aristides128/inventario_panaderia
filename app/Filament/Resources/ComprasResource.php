<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ComprasResource\Pages;
use App\Filament\Resources\ComprasResource\RelationManagers;
use App\Models\Compras;
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

class ComprasResource extends Resource
{
    protected static ?string $model = Compras::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = "📦 Gestión de productos";

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('id_producto')
                    ->searchable()
                    ->relationship('producto', 'nombre')
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('id_proveedor')
                    ->searchable()
                    ->relationship('proveedor', 'nombre')
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('id_sucursal')
                    ->searchable()
                    ->relationship('sucursal', 'nombre')
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('cantidad_paquetes')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('cantidad_producto')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('precio_total')
                    ->required()
                    ->numeric()
                    ->default(0.00),
                Forms\Components\TextInput::make('precio_unitario')
                    ->numeric()
                    ->default(0.00),
                Forms\Components\Select::make('estado_compra')
                    ->options([
                        'Pendiente'=> 'Pendiente',
                        'Recibido'=> 'Recibido',
                        'Cancelado'=> 'Cancelado',
                    ])
                    ->default('Pendiente')
                    ->required(),
                Forms\Components\TextInput::make('observaciones')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\DatePicker::make('fecha_vencimiento'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id_producto')
                    ->label('Producto')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('id_proveedor')
                    ->label('Proveedor')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cantidad_producto')
                    ->label('Cantidad de Producto')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('precio_total')
                    ->label('Precio Total')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado_compra')
                    ->label('Estado de la Compra')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('fecha_vencimiento')
                    ->date()
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
                    ->tooltip('Restaurar compras')
                ,

                // Borrado definitivo multiple de datos eliminados logícamente
                Tables\Actions\ForceDeleteBulkAction::make()
                    ->color('danger')
                    ->label('Borrar registros definitivamente')
                    ->tooltip('Borrar definitivamente compras')
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
            'index' => Pages\ListCompras::route('/'),
            'create' => Pages\CreateCompras::route('/create'),
            'edit' => Pages\EditCompras::route('/{record}/edit'),
        ];
    }
}
