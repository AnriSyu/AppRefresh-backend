<?php

namespace App\Filament\Resources\TimeBlockResource\Pages;

use App\Filament\Resources\TimeBlockResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTimeBlocks extends ListRecords
{
    protected static string $resource = TimeBlockResource::class;

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
