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
use Filament\Infolists;
use Filament\Infolists\Infolist;

use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Filters\TrashedFilter;

class ProduccionesResource extends Resource
{
    protected static ?string $model = Producciones::class;
    protected static ?string $navigationIcon = 'heroicon-o-fire';

    protected static ?string $navigationGroup = '⚙️ Gestión de producciones';

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationGroupIcon = 'heroicon-s-bread';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Grid::make(['default' => 1, 'sm' => 2])
                            ->schema([
                                Forms\Components\DatePicker::make('fecha_produccion')
                                    ->label('Fecha de Producción')
                                    ->placeholder('Seleccione la fecha de producción')
                                    ->prefixIcon('heroicon-o-calendar')
                                    ->closeOnDateSelection()
                                    ->default(now())
                                    ->native(false)
                                    ->required()
                                    ->columnSpan('full'),

                                Forms\Components\Textarea::make('observaciones')
                                    ->label('Observaciones de Producción')
                                    ->placeholder('Ingrese observaciones sobre la producción')
                                    ->rows(3)
                                    ->maxLength(255)
                                    ->columnSpan('full'),
                            ]),
                    ])
                    ->columnSpan('lg'),
            ])
            ->columns(1);
    }
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Información de la Producción')
                    ->schema([
                        Infolists\Components\TextEntry::make('id_produccion')
                            ->label('ID de Producción')
                            ->icon('heroicon-o-hashtag'),
                        Infolists\Components\TextEntry::make('fecha_produccion')
                            ->label('Fecha de Producción')
                            ->date('d/m/Y')
                            ->icon('heroicon-o-calendar'),
                        Infolists\Components\TextEntry::make('observaciones')
                            ->label('Observaciones')
                            ->columnSpanFull(),
                    ])->columns(['default' => 1, 'sm' => 2]),

                Infolists\Components\Section::make('Detalles de Productos y Entrega')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('detalles')
                            ->schema([
                                Infolists\Components\TextEntry::make('Producto.nombre')
                                    ->label('Producto')
                                    ->icon('heroicon-o-shopping-bag'),
                                Infolists\Components\TextEntry::make('cantidad_utilizada')
                                    ->label('Cantidad')
                                    ->icon('heroicon-o-clipboard-document-list'),
                                Infolists\Components\TextEntry::make('Empleado.nombre')
                                    ->label('Recibido por')
                                    ->icon('heroicon-o-user')
                                    ->default('Sin asignar'),
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Fecha/Hora Registro')
                                    ->dateTime('d/m/Y H:i')
                                    ->icon('heroicon-o-clock'),
                            ])
                            ->columns(['default' => 1, 'sm' => 2, 'md' => 4])
                            ->columnSpanFull(),
                    ]),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('fecha_produccion')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('observaciones')
                    ->label('Observaciones')
                    ->limit(50)
                    ->wrap()
                    ->toggleable(),
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
                TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Ver')
                        ->tooltip('Ver producción')
                        ->icon('heroicon-o-eye')
                        ->color('info'),

                    Tables\Actions\EditAction::make()
                        ->label('Editar')
                        ->tooltip('Editar producción')
                        ->visible(fn (Producciones $record) => $record->deleted_at === null)
                        ->icon('heroicon-o-pencil'),

                    RestoreAction::make()
                        ->tooltip('Restaurar producción')
                        ->visible(fn (Producciones $record) => $record->deleted_at !== null),

                    Tables\Actions\DeleteAction::make()
                        ->label('Eliminar')
                        ->tooltip('Eliminar producción')
                        ->visible(fn (Producciones $record) => $record->deleted_at === null)
                        ->icon('heroicon-o-trash'),

                    ForceDeleteAction::make()
                        ->label('Borrado definitivo')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('¿Eliminar producción?')
                        ->modalDescription('¿Estás seguro de que deseas eliminar esta producción? Esta acción no se puede deshacer.')
                        ->modalSubmitActionLabel('Sí, eliminar')
                        ->modalCancelActionLabel('Cancelar')
                        ->action(fn (Producciones $record) => $record->forceDelete())
                        ->tooltip('Eliminar definitivamente'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\RestoreBulkAction::make()
                        ->color('success')
                        ->label('Restaurar registros')
                        ->tooltip('Restaurar producción'),

                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->color('danger')
                        ->label('Borrar registros definitivamente')
                        ->tooltip('Borrar definitivamente producción'),
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
            'index' => Pages\ListProducciones::route('/'),
            'create' => Pages\CreateProducciones::route('/create'),
            'view' => Pages\ViewProducciones::route('/{record}'),
            'edit' => Pages\EditProducciones::route('/{record}/edit'),
        ];
    }
}
