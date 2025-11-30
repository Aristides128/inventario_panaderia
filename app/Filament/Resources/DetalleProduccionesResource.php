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
                                Forms\Components\Select::make('id_produccion')
                                    ->label('Producción')
                                    ->placeholder('Seleccione una producción')
                                    ->prefixIcon('heroicon-o-calendar')
                                    ->hint('Ingrese producción')
                                    ->hintIcon('heroicon-o-calendar')
                                    ->required(),
                                Forms\Components\Select::make('id_producto')
                                    ->label('Productos disponibles')
                                    ->placeholder('Seleccione producto')
                                    ->hint('Ingrese producto')
                                    ->hintIcon('heroicon-o-calendar')
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make('cantidad_utilizada')
                                    ->required()
                                    ->label('Cantidad de producto utilizado')
                                    ->placeholder('Ingrese cantidad')
                                    ->numeric()
                                    ->hint('Ingrese cantidad utilizada')
                                    ->hintIcon('heroicon-o-calendar')
                                    ->default(1),
                            ])
                        ->columns(1)
                        ->columnSpan('lg')
                        ->extraAttributes(['class' => 'shadow-md']),
                ])
            ->columns(1)
            ->extraAttributes(['class' => 'py-6']);
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
