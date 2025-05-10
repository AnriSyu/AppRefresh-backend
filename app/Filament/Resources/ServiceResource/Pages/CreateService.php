<?php

namespace App\Filament\Resources\ServiceResource\Pages;

use App\Filament\Resources\ServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification as FilamentNotification;
use App\Models\ServiceHistory;


class CreateService extends CreateRecord
{
    protected static string $resource = ServiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Aseguramos que el servicio se cree sin técnico asignado
        $data['technical_id'] = null;

        return $data;
    }

    protected function afterCreate(): void
    {
        ServiceHistory::create([
            'service_id' => $this->record->id,
            'status' => 'P', 
            'observations' => 'Servicio creado, pendiente de asignación a técnico',
            'start_time' => now(),
            'end_time' => null,
        ]);

        // Enviar notificaciones
        ServiceResource::sendServiceNotifications($this->record);
    }

}
