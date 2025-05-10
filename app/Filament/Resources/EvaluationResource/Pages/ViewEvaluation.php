<?php

namespace App\Filament\Resources\EvaluationResource\Pages;

use App\Filament\Resources\EvaluationResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;

class ViewEvaluation extends ViewRecord
{
    protected static string $resource = EvaluationResource::class;

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
        return [];
    }
}
