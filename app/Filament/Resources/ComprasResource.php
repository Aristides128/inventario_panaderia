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

class ComprasResource extends Resource
{
    protected static ?string $model = Compras::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = "Gestión de productos";

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id_producto')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('id_proveedor')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('id_sucursal')
                    ->required()
                    ->numeric(),
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
                Forms\Components\TextInput::make('estado_compra')
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
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('id_proveedor')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('id_sucursal')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cantidad_paquetes')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cantidad_producto')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('precio_total')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('precio_unitario')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado_compra'),
                Tables\Columns\TextColumn::make('observaciones')
                    ->searchable(),
                Tables\Columns\TextColumn::make('fecha_vencimiento')
                    ->date()
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
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
