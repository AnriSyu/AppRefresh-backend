<?php

namespace App\Filament\Resources\TimeBlockResource\Pages;

use App\Filament\Resources\TimeBlockResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use App\Models\TimeBlock;
use Filament\Notifications\Notification;

class EditTimeBlock extends EditRecord
{
    protected static string $resource = TimeBlockResource::class;

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
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $exists = TimeBlock::where('day_of_week', $data['day_of_week'])
            ->where('hours', $data['hours'])
            ->where('id', '!=', $this->record->id)
            ->exists();

        if ($exists) {
            Notification::make()
                ->title('Error al actualizar el bloque de tiempo')
                ->body('Ya existe un bloque de tiempo para este día y hora. No se permiten duplicados.')
                ->danger()
                ->send();

            $this->halt();
        }

        return $data;
    }
}
