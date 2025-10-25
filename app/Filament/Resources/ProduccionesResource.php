<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProduccionesResource\Pages;
use App\Filament\Resources\ProduccionesResource\RelationManagers;
use App\Models\Producciones;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProduccionesResource extends Resource
{
    protected static ?string $model = Producciones::class;
protected static ?string $navigationIcon = 'heroicon-o-archive-box';


    
 protected static ?string $navigationGroup = '⚙️ Gestión de producciones';

    protected static ?string $navigationGroupIcon = 'heroicon-s-bread';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('fecha_produccion')
                    ->required(),
                Forms\Components\Textarea::make('observaciones')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('fecha_produccion')
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
            'index' => Pages\ListProducciones::route('/'),
            'create' => Pages\CreateProducciones::route('/create'),
            'edit' => Pages\EditProducciones::route('/{record}/edit'),
        ];
    }
}
