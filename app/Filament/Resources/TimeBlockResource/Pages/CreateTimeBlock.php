<?php

namespace App\Filament\Resources\TimeBlockResource\Pages;

use App\Filament\Resources\TimeBlockResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Model;
use App\Models\TimeBlock;
use Filament\Notifications\Notification;

class CreateTimeBlock extends CreateRecord
{
    protected static string $resource = TimeBlockResource::class;


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
        $exists = TimeBlock::where('day_of_week', $data['day_of_week'])
            ->where('hours', $data['hours'])
            ->exists();

        if ($exists) {
            // Mostrar notificación y cancelar la creación
            Notification::make()
                ->title('Error al crear el bloque de tiempo')
                ->body('Ya existe un bloque de tiempo para este día y hora. No se permiten duplicados.')
                ->danger()
                ->send();

            $this->halt();
        }

        return $data;
    }
}
