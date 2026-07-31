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

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = "📦 Gestión de productos";

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Grid::make(['default' => 1, 'sm' => 2])
                            ->schema([
                                Forms\Components\TextInput::make('nombre')
                                    ->label('Nombre del Proveedor')
                                    ->required()
                                    ->autocomplete('off')
                                    ->placeholder('Ej: Distribuidora Pan S.A.')
                                    ->maxLength(100)
                                    ->prefixIcon('heroicon-o-building-storefront'),

                                Forms\Components\TextInput::make('telefono')
                                    ->label('Teléfono')
                                    ->tel()
                                    ->required()
                                    ->autocomplete('off')
                                    ->maxLength(20)
                                    ->prefixIcon('heroicon-o-phone')
                                    ->placeholder('+52 123 456 7890'),

                                Forms\Components\TextInput::make('email')
                                    ->label('Correo Electrónico')
                                    ->email()
                                    ->autocomplete('off')
                                    ->maxLength(100)
                                    ->default(null)
                                    ->prefixIcon('heroicon-o-envelope')
                                    ->placeholder('contacto@proveedor.com'),

                                Forms\Components\Textarea::make('direccion')
                                    ->label('Dirección')
                                    ->maxLength(255)
                                    ->default(null)
                                    ->autocomplete('off')
                                    ->placeholder('Calle, Número, Colonia, C.P., Ciudad, Estado')
                                    ->helperText('Ingrese la dirección completa para envíos')
                                    ->columnSpan('full')
                                    ->rows(2),
                            ])
                            ->columns(['default' => 1, 'sm' => 2])
                            ->extraAttributes(['class' => 'gap-4']),
                    ])
                    ->columnSpan('lg')
                    ->extraAttributes(['class' => 'shadow-md'])
            ])
            ->columns(1)
            ->extraAttributes(['class' => 'py-6']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Proveedor')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-building-storefront')
                    ->iconColor('primary')
                    ->description(fn(Proveedores $record): string => $record->email ?: 'Sin correo electrónico')
                    ->wrap()
                    ->tooltip('Haz clic para ordenar por nombre'),

                Tables\Columns\TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->icon('heroicon-o-phone')
                    ->iconColor('primary')
                    ->searchable()
                    ->sortable()
                    ->visibleFrom('sm'),

                Tables\Columns\TextColumn::make('direccion')
                    ->label('Dirección')
                    ->icon('heroicon-o-map-pin')
                    ->iconColor('primary')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->wrap(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Registro')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-calendar')
                    ->color('gray')
                    ->description(fn(Proveedores $record) => 'Actualizado: ' . $record->updated_at->diffForHumans())
            ])
            ->filters([
                TrashedFilter::make()
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->tooltip('Ver proveedor')
                        ->icon('heroicon-o-eye')
                        ->color('primary'),
                    Tables\Actions\EditAction::make()
                        ->tooltip('Editar proveedor')
                        ->icon('heroicon-o-pencil')
                        ->color('success'),
                    Tables\Actions\DeleteAction::make()
                        ->tooltip('Eliminar proveedor')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->icon('heroicon-o-trash'),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->icon('heroicon-o-trash')
                        ->color('danger'),
                    Tables\Actions\RestoreBulkAction::make()
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('warning'),
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
            'index' => Pages\ListProveedores::route('/'),
            'create' => Pages\CreateProveedores::route('/create'),
            'edit' => Pages\EditProveedores::route('/{record}/edit'),
        ];
    }
}
