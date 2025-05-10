<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\User;
use App\Models\Service;

class InventoryMovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'inventoryMovements';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('movement_type')
                    ->label('Tipo de Movimiento')
                    ->options([
                        'E' => 'Entrada',
                        'S' => 'Salida',
                        'V' => 'Venta',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('quantity')
                    ->label('Cantidad')
                    ->required()
                    ->numeric()
                    ->minValue(1),

                Forms\Components\Textarea::make('reason')
                    ->label('Motivo')
                    ->required(),

                Forms\Components\Select::make('technical_id')
                    ->label('Técnico')
                    ->options(function () {
                        return User::whereHas('roles', function ($query) {
                            $query->where('name', 'Técnico');
                        })->pluck('name', 'id');
                    })
                    ->required()
                    ->searchable(),

                Forms\Components\Select::make('service_id')
                    ->label('Servicio')
                    ->options(Service::pluck('name', 'id'))
                    ->searchable()
                    ->nullable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('movementTypeName')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Entrada' => 'success',
                        'Salida' => 'warning',
                        'Venta' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Cantidad')
                    ->numeric(),

                Tables\Columns\TextColumn::make('reason')
                    ->label('Motivo')
                    ->limit(30)
                    ->extraAttributes(function ($state) {
                        if (strlen($state) <= 30) {
                            return [];
                        }

                        return [
                            'title' => $state,
                        ];
                    }),

                Tables\Columns\TextColumn::make('technical.name')
                    ->label('Técnico')
                    ->searchable(),

                Tables\Columns\TextColumn::make('service.name')
                    ->label('Servicio')
                    ->placeholder('N/A')
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('movement_type')
                    ->label('Tipo de Movimiento')
                    ->options([
                        'E' => 'Entrada',
                        'S' => 'Salida',
                        'V' => 'Venta',
                    ]),

                Tables\Filters\SelectFilter::make('technical_id')
                    ->label('Técnico')
                    ->options(function () {
                        return User::whereHas('roles', function ($query) {
                            $query->where('name', 'Técnico');
                        })->pluck('name', 'id');
                    }),

                Tables\Filters\SelectFilter::make('service_id')
                    ->label('Servicio')
                    ->options(Service::pluck('name', 'id')),
            ])
            ->headerActions([
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
            ]);
    }
}
