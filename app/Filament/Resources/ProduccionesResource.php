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

use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Filters\TrashedFilter;

class ProduccionesResource extends Resource
{
    protected static ?string $model = Producciones::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = '⚙️ Gestión de producciones';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationGroupIcon = 'heroicon-s-bread';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\DatePicker::make('fecha_produccion')
                            ->label('Fecha de producción')
                            ->prefixIcon('heroicon-o-calendar')
                            ->hint('Ingrese fecha de producción')
                            ->hintIcon('heroicon-o-calendar')
                            ->required(),
                        Forms\Components\Textarea::make('observaciones')
                            ->label('observaciones de producción')
                            ->hint('Ingrese observación de producción')
                            ->hintIcon('heroicon-o-calendar')
                            ->placeholder('Ingrese Observación de producción')
                            ->columnSpanFull(),
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
                TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Editar')
                    ->tooltip('Editar producción')
                    ->visible(function (Producciones $record) {
                        return $record->deleted_at === null;
                    })
                    ->icon('heroicon-o-pencil'),

                RestoreAction::make()
                    ->tooltip('Restaurar producción')
                    ->visible(function (Producciones $record) {
                        return $record->deleted_at !== null;
                    }),

                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar')
                    ->tooltip('Eliminar producción')
                    ->visible(function (Producciones $record) {
                        return $record->deleted_at === null;
                    })
                    ->icon('heroicon-o-trash'),

                Tables\Actions\ViewAction::make()
                    ->label('Ver')
                    ->tooltip('Ver producción')
                    ->icon('heroicon-o-eye')
                    ->color('info'),
                ForceDeleteAction::make()
                    ->label('Borrado definitivo')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('¿Eliminar producción?')
                    ->modalDescription('¿Estás seguro de que deseas eliminar esta producción? Esta acción no se puede deshacer.')
                    ->modalSubmitActionLabel('Sí, eliminar')
                    ->modalCancelActionLabel('Cancelar')
                    ->action(function (Producciones $record) {
                        $record->forceDelete();
                    })
                    ->tooltip('Eliminar definitivamente'),
            ])
            ->bulkActions([

                // Restauración multiple de datos eliminados logícamente
                Tables\Actions\RestoreBulkAction::make()
                    ->color('success')
                    ->label('Restaurar registros')
                    ->tooltip('Restaurar producción')
                ,

                // Borrado definitivo multiple de datos eliminados logícamente
                Tables\Actions\ForceDeleteBulkAction::make()
                    ->color('danger')
                    ->label('Borrar registros definitivamente')
                    ->tooltip('Borrar definitivamente producción')

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
            'edit' => Pages\EditProducciones::route('/{record}/edit'),
        ];
    }
}
