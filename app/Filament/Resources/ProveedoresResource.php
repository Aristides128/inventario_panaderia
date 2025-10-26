<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProveedoresResource\Pages;
use App\Filament\Resources\ProveedoresResource\RelationManagers;
use App\Models\Proveedores;
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

class ProveedoresResource extends Resource
{
    protected static ?string $model = Proveedores::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = "📦 Gestión de productos";

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('nombre')
                                    ->label('Nombre del Proveedor')
                                    ->required()
                                    ->autocomplete('off')
                                    ->placeholder('Ej: Distribuidora Pan S.A.')
                                    ->maxLength(100),

                                Forms\Components\TextInput::make('telefono')
                                    ->label('Teléfono')
                                    ->tel()
                                    ->required()
                                    ->autocomplete('off')
                                    ->maxLength(20)
                                    ->placeholder('+52 123 456 7890'),

                                Forms\Components\TextInput::make('email')
                                    ->label('Correo Electrónico')
                                    ->email()
                                    ->maxLength(100)
                                    ->default(null)
                                    ->placeholder('contacto@proveedor.com'),

                                Forms\Components\Textarea::make('direccion')
                                    ->label('Dirección')
                                    ->maxLength(255)
                                    ->default(null)
                                    ->placeholder('Calle, Número, Colonia, C.P.')
                                    ->columnSpan('full'),
                            ]),
                    ])
                    ->columnSpan('lg')
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre del Proveedor')
                    ->searchable(),
                Tables\Columns\TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Correo Electrónico')
                    ->searchable(),
                Tables\Columns\TextColumn::make('direccion')
                    ->label('Dirección')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Fecha de Actualización')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
                TrashedFilter::make(),

            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Editar')
                    ->tooltip('Editar proveedor')
                    ->visible(function (Proveedores $record) {
                        return $record->deleted_at === null;
                    })
                    ->icon('heroicon-o-pencil'),
                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar')
                    ->tooltip('Eliminar proveedor')
                    ->visible(function (Proveedores $record) {
                        return $record->deleted_at === null;
                    })
                    ->icon('heroicon-o-trash'),
                Tables\Actions\ViewAction::make()
                    ->label('Ver')
                    ->tooltip('Ver proveedor')
                    ->icon('heroicon-o-eye')
                    ->color('info'),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->bulkActions([

                // Restauración multiple de datos eliminados logícamente
                Tables\Actions\RestoreBulkAction::make()
                    ->color('success')
                    ->label('Restaurar registros')
                    ->tooltip('Restaurar proveedor')
                ,

                // Borrado definitivo multiple de datos eliminados logícamente
                Tables\Actions\ForceDeleteBulkAction::make()
                    ->color('danger')
                    ->label('Borrar registros definitivamente')
                    ->tooltip('Borrar definitivamente proveedor')


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
            'index' => Pages\ListProveedores::route('/'),
            'create' => Pages\CreateProveedores::route('/create'),
            'edit' => Pages\EditProveedores::route('/{record}/edit'),
        ];
    }
}
