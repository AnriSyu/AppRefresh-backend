<?php

namespace App\Filament\Resources\ServicesAvailabilityResource\Pages;

use App\Filament\Resources\ServicesAvailabilityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\TimeBlock;

class EditServicesAvailability extends EditRecord
{
    protected static string $resource = ServicesAvailabilityResource::class;

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

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Intentamos determinar el día de los bloques de tiempo seleccionados (para edición)
        if (!empty($data['time_block_ids'])) {
            $timeBlockIds = is_array($data['time_block_ids'])
                           ? $data['time_block_ids']
                           : json_decode($data['time_block_ids'], true);

            if (!empty($timeBlockIds)) {
                $firstBlockId = $timeBlockIds[0] ?? null;

                if ($firstBlockId) {
                    $timeBlock = TimeBlock::find($firstBlockId);
                    if ($timeBlock) {
                        $data['selected_day'] = $timeBlock->day_of_week;
                    }
                }
            }
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // No guardar el campo 'selected_day'
        unset($data['selected_day']);

        return $data;
    }
}
