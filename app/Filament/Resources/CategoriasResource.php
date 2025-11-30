<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoriasResource\Pages;
use App\Models\Categorias;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Filters\TrashedFilter;

class CategoriasResource extends Resource
{
    protected static ?string $model = Categorias::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog';

    protected static ?string $navigationGroup = "📦 Gestión de productos";

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('nombre')
                            ->label('Nombre de la Categoría')
                            ->required()
                            ->prefixIcon('heroicon-o-tag')
                            ->placeholder('Ej: Cremas, Bebidas, Panes...')
                            ->maxLength(100)
                            ->hint('Nombre descriptivo de la categoría')
                            ->hintIcon('heroicon-o-information-circle')
                            ->hintColor('primary')
                            ->columnSpan('full'),

                        Forms\Components\Textarea::make('descripcion')
                            ->label('Descripción')
                            ->required()
                            ->placeholder('Ingrese una descripción detallada de la categoría...')
                            ->maxLength(255)
                            ->default(null)

                            ->hint('Máximo 255 caracteres')
                            ->hintIcon('heroicon-o-information-circle')
                            ->hintColor('primary')
                            ->helperText('Esta descripción ayudará a identificar mejor la categoría')
                            ->columnSpan('full')
                            ->rows(3),
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
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-tag')
                    ->iconColor('primary')
                    ->description(fn(Categorias $record): string => $record->descripcion ?: 'Sin descripción')
                    ->wrap()
                    ->tooltip('Haz clic para ordenar por nombre'),
                Tables\Columns\TextColumn::make('productos_count')
                    ->label('Cantidad de Productos')
                    ->counts('productos')
                    ->icon('heroicon-o-cube')
                    ->iconPosition('after')
                    ->alignCenter()
                    ->color('gray')
                    ->sortable(),
                Tables\Columns\TextColumn::make('descripcion')
                    ->icon('heroicon-o-document-text')
                    ->iconColor('primary')
                    ->label('Descripción')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable()
                    ->icon('heroicon-o-calendar')
                    ->color('gray')
                    ->description(fn(Categorias $record) => 'Creado: ' . $record->created_at->diffForHumans())
                    ->tooltip(fn(Categorias $record) => 'Creado el ' . $record->created_at->format('d/m/Y \a \l\a\s H:i')),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Fecha de Actualización')
                    ->dateTime()
                    ->sortable()
                    ->icon('heroicon-o-calendar')
                    ->iconColor('gray')
                    ->description(fn(Categorias $record) => 'Actualizado: ' . $record->updated_at->diffForHumans())
                    ->tooltip(fn(Categorias $record) => 'Actualizado el ' . $record->updated_at->format('d/m/Y \a \l\a\s H:i'))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
                TrashedFilter::make(),

            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Ver')
                    ->tooltip('Ver categoría')
                    ->icon('heroicon-o-eye')
                    ->color('primary'),

                Tables\Actions\EditAction::make()
                    ->label('Editar')
                    ->color('success')
                    ->tooltip('Editar categoría')
                    ->visible(function (Categorias $record) {
                        return $record->deleted_at === null;
                    })
                    ->icon('heroicon-o-pencil'),

                RestoreAction::make()
                    ->tooltip('Restaurar categoría')
                    ->label('Restaurar')
                    ->color('success')
                    ->visible(function (Categorias $record) {
                        return $record->deleted_at !== null;
                    }),

                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar')
                    ->color('danger')
                    ->tooltip('Eliminar categoría')
                    ->visible(function (Categorias $record) {
                        return $record->deleted_at === null;
                    })
                    ->icon('heroicon-o-trash'),

                ForceDeleteAction::make()
                    ->label('Eliminar permanentemente')
                    ->tooltip('Eliminar registro')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('¿Eliminar categoría permanentemente?')
                    ->modalDescription('Esta acción no se puede deshacer. Todos los productos asociados podrían verse afectados.')
                    ->modalSubmitActionLabel('Sí, eliminar')
                    ->modalCancelActionLabel('Cancelar')
                    ->action(fn(Categorias $record) => $record->forceDelete()),

            ])
            ->bulkActions([

                // Restauración multiple de datos eliminados logícamente
                Tables\Actions\RestoreBulkAction::make()
                    ->color('success')
                    ->label('Restaurar registros')
                    ->tooltip('Restaurar categoría'),

                // Borrado definitivo multiple de datos eliminados logícamente
                Tables\Actions\ForceDeleteBulkAction::make()
                    ->color('danger')
                    ->label('Borrar registros definitivamente')
                    ->tooltip('Borrar definitivamente categoría')

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
            'index' => Pages\ListCategorias::route('/'),
            'create' => Pages\CreateCategorias::route('/create'),
            'edit' => Pages\EditCategorias::route('/{record}/edit'),
        ];
    }
}
