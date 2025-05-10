<?php

namespace App\Filament\Resources\ServiceResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\ServiceHistory;

class HistoryRelationManager extends RelationManager
{
    protected static string $relationship = 'history';
    protected static ?string $recordTitleAttribute = 'status';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('status')
                    ->options([
                        'P' => 'Pendiente',
                        'A' => 'Activo',
                        'C' => 'Cancelado',
                        'E' => 'En progreso',
                        'F' => 'Finalizado',
                    ])
                    ->required(),

                Forms\Components\Textarea::make('observations')
                    ->label('Observaciones')
                    ->required(),

                Forms\Components\DateTimePicker::make('start_time')
                    ->label('Hora de inicio')
                    ->required(),

                Forms\Components\DateTimePicker::make('end_time')
                    ->label('Hora de fin'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('status')
            ->columns([
                Tables\Columns\TextColumn::make('statusName')
                    ->label('Estado')
                    ->badge()
                    ->color(function ($state) {
                        return match ($state) {
                            'P' => 'warning',
                            'A' => 'success',
                            'C' => 'danger',
                            'E' => 'info',
                            'F' => 'success',
                            default => 'gray',
                        };
                    }),

                Tables\Columns\TextColumn::make('observations')
                    ->label('Observaciones')
                    ->limit(50),

                Tables\Columns\TextColumn::make('start_time')
                    ->label('Hora de inicio')
                    ->dateTime(),

                Tables\Columns\TextColumn::make('end_time')
                    ->label('Hora de fin')
                    ->dateTime()
                    ->placeholder('En proceso'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registrado en')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data) {
                        if (in_array($data['status'], ['C', 'E', 'P', 'F']) && empty($data['end_time'])) {
                            $data['end_time'] = now();
                        }
                        return $data;
                    })
                    ->after(function ($record) {
                        $service = $this->getOwnerRecord();

                        if (in_array($record->status, ['C', 'F'])) {
                            $service->availabilities()->delete();

                            $service->update(['technical_id' => null]);
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data, $record) {
                        if (in_array($data['status'], ['C', 'E', 'P', 'F']) && empty($data['end_time'])) {
                            $data['end_time'] = now();
                        }
                        return $data;
                    })
                    ->after(function ($record) {
                        // Get the parent service
                        $service = $this->getOwnerRecord();

                        if (in_array($record->status, ['C', 'F'])) {
                            $service->availabilities()->delete();

                            $service->update(['technical_id' => null]);
                        }
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
