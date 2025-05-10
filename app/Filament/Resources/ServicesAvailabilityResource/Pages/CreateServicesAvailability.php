<?php

namespace App\Filament\Resources\ServicesAvailabilityResource\Pages;

use App\Filament\Resources\ServicesAvailabilityResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateServicesAvailability extends CreateRecord
{
    protected static string $resource = ServicesAvailabilityResource::class;


    public function canCreate(): bool
    {
        return auth()->user()->hasRole('Administrador');
    }


    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // No necesitamos guardar el campo 'selected_day' en la base de datos
        unset($data['selected_day']);

        return $data;
    }
}
