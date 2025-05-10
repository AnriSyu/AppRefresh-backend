<?php

namespace App\Filament\Resources;
use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use App\Models\User;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Section;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationGroup = 'Inventario';
    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('Administrador');
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información del Producto')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),

                        Textarea::make('description')
                            ->label('Descripción')
                            ->required()
                            ->maxLength(1000),

                        Select::make('type')
                            ->label('Tipo')
                            ->options([
                                'R' => 'Repuesto',
                                'H' => 'Herramienta',
                                'P' => 'Producto',
                            ])
                            ->required(),

                        TextInput::make('stock')
                            ->label('Stock')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->disabled(fn (string $operation): bool => $operation === 'edit'),

                        TextInput::make('price')
                            ->label('Precio')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('$'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->limit(15)
                    ->extraAttributes(function ($state) {
                        if (strlen($state) <= 15) {
                            return [];
                        }

                        return [
                            'title' => $state,
                            'data-tooltip-max-width' => '300px',
                        ];
                    })
                    ->searchable(),

                TextColumn::make('typeName')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Repuesto' => 'success',
                        'Herramienta' => 'warning',
                        'Producto' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('stock')
                    ->label('Stock')
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string =>
                        $state > 10 ? 'success' : ($state > 5 ? 'warning' : 'danger')
                    ),

                TextColumn::make('price')
                    ->label('Precio')
                    ->money('COP')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Última actualización')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'R' => 'Repuesto',
                        'H' => 'Herramienta',
                        'P' => 'Producto',
                    ]),

                Tables\Filters\Filter::make('low_stock')
                    ->label('Stock bajo')
                    ->query(fn (Builder $query): Builder => $query->where('stock', '<', 5)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Action::make('add_stock')
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
                        ->nullable(), // Hacerlo opcional

                    Select::make('service_id')
                        ->label('Servicio')
                        ->options(Service::pluck('name', 'id'))
                        ->searchable()
                        ->nullable(),
                ]),

                Action::make('remove_stock')
                    ->label('Retirar Stock')
                    ->icon('heroicon-o-minus')
                    ->color('danger')
                    ->action(function (Product $record, array $data): void {
                        // Verificar que hay suficiente stock
                        if ($record->stock < $data['quantity']) {
                            Notification::make()
                                ->title('Error')
                                ->body('No hay suficiente stock disponible')
                                ->danger()
                                ->send();

                            return;
                        }

                        $record->inventoryMovements()->create([
                            'movement_type' => $data['movement_type'],
                            'quantity' => $data['quantity'],
                            'reason' => $data['reason'],
                            'technical_id' => $data['technical_id'],
                            'service_id' => $data['service_id'],
                        ]);

                        $record->decrement('stock', $data['quantity']);

                        if ($data['movement_type'] === 'V' && !empty($data['service_id'])) {
                            $service = Service::find($data['service_id']);

                            if ($service) {

                                $productValue = $record->price * $data['quantity'];
                                $service->price = $service->price + $productValue;
                                $service->save();

                                Notification::make()
                                    ->title('Servicio actualizado')
                                    ->success()
                                    ->body("Precio del servicio actualizado: {$service->name} - Nuevo precio: \${$service->price}")
                                    ->send();
                            }
                        }

                        Notification::make()
                            ->title('Stock actualizado')
                            ->success()
                            ->send();
                    })
                    ->form([
                        Select::make('movement_type')
                            ->label('Tipo de movimiento')
                            ->options([
                                'S' => 'Salida',
                                'V' => 'Venta',
                            ])
                            ->required(),

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
                            ->required()
                            ->searchable(),

                        Select::make('service_id')
                            ->label('Servicio')
                            ->options(Service::pluck('name', 'id'))
                            ->searchable()
                            ->nullable(),
                    ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\InventoryMovementsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
