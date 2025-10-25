<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DetalleproduccionesResource\Pages;
use App\Filament\Resources\DetalleproduccionesResource\RelationManagers;
use App\Models\Detalleproducciones;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DetalleproduccionesResource extends Resource
{
    protected static ?string $model = Detalleproducciones::class;

    protected static ?string $navigationIcon = 'heroicon-o-scale';

    protected static ?string $navigationGroup = '⚙️ Gestión de producciones';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id_produccion')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('id_producto')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('cantidad_utilizada')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id_produccion')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('id_producto')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cantidad_utilizada')
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
            'index' => Pages\ListDetalleproducciones::route('/'),
            'create' => Pages\CreateDetalleproducciones::route('/create'),
            'edit' => Pages\EditDetalleproducciones::route('/{record}/edit'),
        ];
    }
}
