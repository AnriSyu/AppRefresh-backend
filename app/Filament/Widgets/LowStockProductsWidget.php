<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Notifications\Notification;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;

class LowStockProductsWidget extends BaseWidget
{
    protected static ?string $heading = 'Productos con Stock Bajo';
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 2;
    protected static ?string $pollingInterval = '300s';
    protected static ?int $stockThreshold = 5;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->where('stock', '<=', static::$stockThreshold)
                    ->orderBy('stock', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(20)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 20 ? $state : null;
                    }),

                Tables\Columns\TextColumn::make('typeName')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Repuesto' => 'success',
                        'Herramienta' => 'warning',
                        'Producto' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('stock')
                    ->label('Stock Actual')
                    ->sortable()
                    ->badge()
                    ->alignCenter()
                    ->color(fn (int $state): string =>
                        $state === 0 ? 'danger' : ($state <= 3 ? 'warning' : 'primary')
                    ),

                Tables\Columns\TextColumn::make('price')
                    ->label('Precio')
                    ->money('COP')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última actualización')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('add_stock')
                    ->label('Añadir Stock')
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->action(function (Product $record, array $data): void {
                        $record->inventoryMovements()->create([
                            'movement_type' => 'E',
                            'quantity' => $data['quantity'],
                            'reason' => $data['reason'],
                            'technical_id' => $data['technical_id'] ?? auth()->id(),
                            'service_id' => $data['service_id'],
                        ]);

                        $record->increment('stock', $data['quantity']);

                        Notification::make()
                            ->title('Stock actualizado')
                            ->success()
                            ->send();

                        $this->dispatch('refresh');
                    })
                    ->form([
                        TextInput::make('quantity')
                            ->label('Cantidad')
                            ->required()
                            ->numeric()
                            ->minValue(1),

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
                            ->searchable()
                            ->nullable(),

                        Select::make('service_id')
                            ->label('Servicio')
                            ->options(Service::pluck('name', 'id'))
                            ->searchable()
                            ->nullable(),
                    ]),
            ])
            ->emptyStateHeading('¡Todo el inventario está bien abastecido!')
            ->emptyStateDescription('No hay productos con stock bajo actualmente.')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->defaultSort('stock', 'asc')
            ->paginated([5, 10, 25, 50])
            ->defaultPaginationPageOption(5);
    }

    protected function getHeaderActions(): array
    {
        return [
            Tables\Actions\Action::make('view_all')
                ->label('Ver todos los productos con stock bajo')
                ->url(route('filament.admin.resources.products.index', [
                    'tableFilters[low_stock][value]' => true
                ]))
                ->icon('heroicon-m-arrow-top-right-on-square'),

            Tables\Actions\Action::make('configure')
                ->label('Configurar umbral')
                ->icon('heroicon-m-cog-6-tooth')
                ->action(function (array $data): void {
                    static::$stockThreshold = $data['threshold'];
                    $this->dispatch('refresh');
                })
                ->form([
                    \Filament\Forms\Components\TextInput::make('threshold')
                        ->label('Umbral de stock bajo')
                        ->default(static::$stockThreshold)
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(20)
                        ->required(),
                ]),
        ];
    }
}
