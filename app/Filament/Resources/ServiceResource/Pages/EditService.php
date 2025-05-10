<?php

namespace App\Filament\Resources\ServiceResource\Pages;

use App\Filament\Resources\ServiceResource;
use App\Models\ServiceHistory;
use App\Models\Service;
use App\Models\ServicesAvailability;
use App\Models\TimeBlock;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification as FilamentNotification;

class EditService extends EditRecord
{
    protected static string $resource = ServiceResource::class;

    public function canEdit(): bool
    {
        return auth()->user()->hasRole('Administrador');
    }

    public function canDelete(): bool
    {
        return auth()->user()->hasRole('Administrador');
    }

    protected ?string $originalTechnicalId = null;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->originalTechnicalId = $data['technical_id'] ?? null;

        return $data;
    }

    protected function afterSave(): void
    {
        $record = $this->record;

        $oldTechnicianId = $this->originalTechnicalId;
        $newTechnicianId = $record->technical_id;

        if ($oldTechnicianId != $newTechnicianId && $newTechnicianId) {
            if ($this->hasTechnicianScheduleConflict($record, $newTechnicianId)) {
                $record->update(['technical_id' => $oldTechnicianId]);

                FilamentNotification::make()
                    ->danger()
                    ->title('Asignación fallida')
                    ->body('El técnico ya tiene un servicio asignado en un horario que se solapa con este servicio.')
                    ->send();

                return;
            }

            $status = $newTechnicianId ? 'A' : 'P';

            ServiceHistory::create([
                'service_id' => $record->id,
                'status' => $status,
                'observations' => $newTechnicianId
                    ? 'Técnico reasignado al servicio'
                    : 'Servicio sin técnico asignado',
                'start_time' => now(),
                'end_time' => null,
            ]);

            ServiceResource::sendTechnicianReassignmentNotification($record, $oldTechnicianId);

            FilamentNotification::make()
                ->success()
                ->title('Técnico actualizado')
                ->body('Se ha actualizado el técnico del servicio y se han enviado las notificaciones correspondientes.')
                ->send();
        }
    }

    private function hasTechnicianScheduleConflict($service, $technicalId)
    {
        $serviceAvailability = ServicesAvailability::where('services_id', $service->id)->first();

        if (!$serviceAvailability || empty($serviceAvailability->time_block_ids)) {
            return false;
        }

        $serviceTimeBlocks = TimeBlock::whereIn('id', $serviceAvailability->time_block_ids)
            ->get()
            ->map(function ($timeBlock) {
                return [
                    'day_of_week' => $timeBlock->day_of_week,
                    'hours' => $timeBlock->hours, // Ahora es un string
                ];
            });

        if ($serviceTimeBlocks->isEmpty()) {
            return false;
        }

        $technicianServices = Service::where('technical_id', $technicalId)
            ->where('id', '!=', $service->id)
            ->get();

        foreach ($technicianServices as $techService) {
            $techAvailability = ServicesAvailability::where('services_id', $techService->id)->first();

            if (!$techAvailability || empty($techAvailability->time_block_ids)) {
                continue;
            }

            // Obtenemos los bloques de tiempo del servicio del técnico
            $techTimeBlocks = TimeBlock::whereIn('id', $techAvailability->time_block_ids)
                ->get()
                ->map(function ($timeBlock) {
                    return [
                        'day_of_week' => $timeBlock->day_of_week,
                        'hours' => $timeBlock->hours, // Ahora es un string
                    ];
                });

            foreach ($serviceTimeBlocks as $serviceBlock) {
                foreach ($techTimeBlocks as $techBlock) {
                    if ($serviceBlock['day_of_week'] === $techBlock['day_of_week']) {
                        list($serviceStart, $serviceEnd) = explode('-', $serviceBlock['hours']);
                        list($techStart, $techEnd) = explode('-', $techBlock['hours']);

                        $serviceStartMin = $this->timeToMinutes($serviceStart);
                        $serviceEndMin = $this->timeToMinutes($serviceEnd);
                        $techStartMin = $this->timeToMinutes($techStart);
                        $techEndMin = $this->timeToMinutes($techEnd);

                        if (
                            ($serviceStartMin >= $techStartMin && $serviceStartMin < $techEndMin) ||
                            ($serviceEndMin > $techStartMin && $serviceEndMin <= $techEndMin) ||
                            ($serviceStartMin <= $techStartMin && $serviceEndMin >= $techEndMin)
                        ) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    private function timeToMinutes($time)
    {
        $parts = explode(':', $time);
        return (int)$parts[0] * 60 + (isset($parts[1]) ? (int)$parts[1] : 0);
    }
}
