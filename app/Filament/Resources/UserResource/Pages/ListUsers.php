<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Widgets\UserRolesStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;


    public function canView(): bool
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
            Actions\CreateAction::make(),

        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            UserRolesStatsWidget::class,
        ];
    }
}
