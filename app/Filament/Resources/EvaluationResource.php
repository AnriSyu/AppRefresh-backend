<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EvaluationResource\Pages;
use App\Models\Evaluation;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;
use Illuminate\Database\Eloquent\Model;

class EvaluationResource extends Resource
{
    protected static ?string $model = Evaluation::class;
    protected static ?string $navigationGroup = 'Gestión de Servicios';
    protected static ?string $modelLabel = 'Evaluación';
    protected static ?string $pluralModelLabel = 'Evaluaciones';
    protected static ?int $navigationSort = 20;

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('Administrador');
    }



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Detalles de la Evaluación')
                    ->schema([
                        Placeholder::make('service')
                            ->label('Servicio')
                            ->content(fn (Evaluation $record): string => $record->service->name ?? 'N/A'),

                        Placeholder::make('client')
                            ->label('Cliente')
                            ->content(fn (Evaluation $record): string => $record->service->client->name ?? 'N/A'),

                        Placeholder::make('technical')
                            ->label('Técnico')
                            ->content(fn (Evaluation $record): string => $record->service->technical->name ?? 'Sin asignar'),

                        Placeholder::make('rating')
                            ->label('Calificación')
                            ->content(fn (Evaluation $record): string => $record->ratingStars . ' (' . $record->ratingText . ')'),

                        Placeholder::make('comment')
                            ->label('Comentario')
                            ->content(fn (Evaluation $record): string => $record->comment),

                            Placeholder::make('created_at')
                            ->label('Fecha de evaluación')
                            ->content(fn (Evaluation $record): string => $record->created_at ? $record->created_at->format('d/m/Y H:i') : 'Sin fecha'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('service.name')
                    ->label('Servicio')
                    ->searchable()
                    ->sortable()
                    ->limit(25)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 25 ? $state : null;
                    }),

                TextColumn::make('service.client.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('service.technical.name')
                    ->label('Técnico')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Sin asignar'),

                TextColumn::make('ratingStars')
                    ->label('Calificación')
                    ->searchable(false)
                    ->sortable(
                        query: fn (Builder $query, string $direction): Builder =>
                            $query->orderBy('rating', $direction)
                    ),

                TextColumn::make('comment')
                    ->label('Comentario')
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),

                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('rating')
                    ->label('Calificación')
                    ->options([
                        1 => '★ (Muy malo)',
                        2 => '★★ (Malo)',
                        3 => '★★★ (Regular)',
                        4 => '★★★★ (Bueno)',
                        5 => '★★★★★ (Excelente)',
                    ]),

                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Desde'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),

                Tables\Filters\SelectFilter::make('service.technical_id')
                    ->label('Técnico')
                    ->relationship('service.technical', 'name'),

                Tables\Filters\SelectFilter::make('service.client_id')
                    ->label('Cliente')
                    ->relationship('service.client', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
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
            'index' => Pages\ListEvaluations::route('/'),
            'view' => Pages\ViewEvaluation::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }
}
