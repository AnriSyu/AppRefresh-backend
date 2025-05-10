<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventoryMovementResource\Pages;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\User;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;

class InventoryMovementResource extends Resource
{
    protected static ?string $model = InventoryMovement::class;
    protected static ?string $navigationGroup = 'Inventario';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Movimiento de Inventario';
    protected static ?string $pluralModelLabel = 'Movimientos de Inventario';

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('Administrador');
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('product_id')
                    ->label('Producto')
                    ->options(Product::pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->disabled(fn (string $operation): bool => $operation === 'edit')
                    ->afterStateUpdated(fn ($state, $set) =>
                        $set('current_stock', Product::find($state)?->stock ?? 0)
                    ),

                TextInput::make('current_stock')
                    ->label('Stock Actual')
                    ->disabled()
                    ->dehydrated(false)
                    ->visible(fn (string $operation): bool => $operation === 'create'),

                Select::make('movement_type')
                    ->label('Tipo de Movimiento')
                    ->options([
                        'E' => 'Entrada',
                        'S' => 'Salida',
                        'V' => 'Venta',
                    ])
                    ->required()
                    ->disabled(fn (string $operation): bool => $operation === 'edit'),

                TextInput::make('quantity')
                    ->label('Cantidad')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->disabled(fn (string $operation): bool => $operation === 'edit'),

                Textarea::make('reason')
                    ->label('Motivo')
                    ->required(),

                Select::make('technical_id')
                    ->label('Técnico')
                    ->options(function () {
                        return User::whereHas('roles', function ($query) {
                            $query->where('name', 'Técnico');
                        })->pluck('name', 'id');
                    })
                    ->required(fn (callable $get) => $get('movement_type') !== 'E')
                    ->nullable()
                    ->searchable(),

                Select::make('service_id')
                    ->label('Servicio')
                    ->options(Service::pluck('name', 'id'))
                    ->searchable()
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Producto')
                    ->formatStateUsing(function ($state) {
                        if (strlen($state) > 15) {
                            return substr($state, 0, 15) . '...';
                        }
                        return $state;
                    })
                    ->extraAttributes(fn ($state) => [
                        'title' => $state,
                    ])
                    ->searchable()
                    ->sortable(),

                TextColumn::make('movementTypeName')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Entrada' => 'success',
                        'Salida' => 'warning',
                        'Venta' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('quantity')
                    ->label('Cantidad')
                    ->numeric(),

                TextColumn::make('reason')
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

                TextColumn::make('technical.name')
                    ->label('Técnico')
                    ->searchable(),

                TextColumn::make('service.name')
                    ->label('Servicio')
                    ->placeholder('N/A')
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_id')
                    ->label('Producto')
                    ->options(Product::pluck('name', 'id'))
                    ->searchable(),

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
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventoryMovements::route('/'),
            'create' => Pages\CreateInventoryMovement::route('/create'),
            'view' => Pages\ViewInventoryMovement::route('/{record}'),
        ];
    }
}
