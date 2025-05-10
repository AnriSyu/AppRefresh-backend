<?php

namespace App\Filament\Resources\ServiceResource\RelationsManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EvaluationsRelationManager extends RelationManager
{
    protected static string $relationship = 'evaluations';
    protected static ?string $recordTitleAttribute = 'id';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Placeholder::make('rating')
                    ->label('Calificación')
                    ->content(fn ($record): string => $record->ratingStars . ' (' . $record->ratingText . ')'),

                Forms\Components\Placeholder::make('comment')
                    ->label('Comentario')
                    ->content(fn ($record): string => $record->comment),

                Forms\Components\Placeholder::make('created_at')
                    ->label('Fecha de evaluación')
                    ->content(fn ($record): string => $record->created_at->format('d/m/Y H:i')),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ratingStars')
                    ->label('Calificación'),

                Tables\Columns\TextColumn::make('comment')
                    ->label('Comentario')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime(),
            ])
            ->filters([
            ])
            ->headerActions([
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
            ]);
    }

    public function canCreate(): bool
    {
        return false;
    }

    public function canEdit(Model $record): bool
    {
        return false;
    }

    public function canDelete(Model $record): bool
    {
        return false;
    }
}
