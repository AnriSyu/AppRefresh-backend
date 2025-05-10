<?php

namespace App\Filament\Resources\InventoryMovementResource\Pages;

use App\Filament\Resources\InventoryMovementResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Product;
use App\Models\Service;
use Filament\Notifications\Notification;

class CreateInventoryMovement extends CreateRecord
{
    protected static string $resource = InventoryMovementResource::class;

    public function canCreate(): bool
    {
        return auth()->user()->hasRole('Administrador');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $product = Product::find($data['product_id']);

        if (($data['movement_type'] === 'S' || $data['movement_type'] === 'V') && $product && $product->stock < $data['quantity']) {
            Notification::make()
                ->title('Error')
                ->body('No hay suficiente stock disponible')
                ->danger()
                ->send();

            $this->halt();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;
        $product = $record->product;

        if ($record->movement_type === 'E') {
            $product->increment('stock', $record->quantity);
        } else {
            $product->decrement('stock', $record->quantity);
        }

        if ($record->movement_type === 'V' && $record->service_id) {
            $service = Service::find($record->service_id);

            if ($service) {
                $productValue = $product->price * $record->quantity;

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
            ->title('Movimiento registrado')
            ->success()
            ->body("Stock actualizado: {$product->name} - Nuevo stock: {$product->stock}")
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
