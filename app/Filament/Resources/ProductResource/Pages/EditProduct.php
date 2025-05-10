<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use App\Models\Product;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    public function canEdit(): bool
    {
        return auth()->user()->hasRole('Administrador');
    }

    public function canDelete(): bool
    {
        return auth()->user()->hasRole('Administrador');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ViewAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $originalStock = $this->record->stock;
        $data['stock'] = $originalStock;

        return $data;
    }

    protected function afterSave(): void
    {
        Notification::make()
            ->title('Producto actualizado')
            ->success()
            ->body('Los cambios han sido guardados. Recuerda que el stock solo puede modificarse mediante movimientos de inventario.')
            ->send();
    }
}
