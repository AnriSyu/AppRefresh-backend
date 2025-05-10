<?php

namespace App\Filament\Resources\ServicesAvailabilityResource\Pages;

use App\Filament\Resources\ServicesAvailabilityResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListServicesAvailabilities extends ListRecords
{
    protected static string $resource = ServicesAvailabilityResource::class;

    public function canDelete(): bool
    {
        return auth()->user()->hasRole('Administrador');
    }

    public function canEdit(): bool
    {
        return auth()->user()->hasRole('Administrador');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
