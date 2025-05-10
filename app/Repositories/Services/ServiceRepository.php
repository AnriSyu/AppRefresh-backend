<?php
namespace App\Repositories\Services;

use App\Models\Service;
use App\Models\ServiceHistory;
use App\Models\ServicesAvailability;
use App\Models\TimeBlock;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class ServiceRepository implements ServiceRepositoryInterface
{
    public function createService(array $data): Service
    {
        $service = Service::create($data);

        Cache::forget('all_type_services');
        Cache::forget('available_time_blocks');

        return $service;
    }

    public function getServicesByUserId(int $userId): Collection
    {
        return Service::where(function($query) use ($userId) {
                $query->where('client_id', $userId)
                    ->orWhere('technical_id', $userId);
            })
            ->where(function($query) {
                $query->whereHas('history', function($q) {
                    $q->whereNotIn('status', ['F', 'C'])
                        ->whereIn('id', function($subquery) {
                            $subquery->selectRaw('MAX(id)')
                                    ->from('services_history')
                                    ->groupBy('service_id');
                        });
                })
                ->orWhere(function($q) {
                    $q->whereHas('history', function($subQ) {
                        $subQ->where('status', 'F')
                            ->whereIn('id', function($subquery) {
                                $subquery->selectRaw('MAX(id)')
                                        ->from('services_history')
                                        ->groupBy('service_id');
                            });
                    })
                    ->whereDoesntHave('evaluations');
                });
            })
            ->with([
                'typeService',
                'client',
                'technical',
                'availabilities',
                'history' => function($query) {
                    $query->orderBy('created_at', 'desc');
                }
            ])
            ->get();
    }
    public function createServiceAvailability(int $serviceId, array $timeBlockIds): void
    {
        ServicesAvailability::create([
            'services_id' => $serviceId,
            'time_block_ids' => $timeBlockIds
        ]);
    }

    public function createServiceHistory(int $serviceId, string $status, string $observations, ?string $endTime = null): void
    {
        ServiceHistory::create([
            'service_id' => $serviceId,
            'status' => $status,
            'observations' => $observations,
            'start_time' => Carbon::now(),
            'end_time' => $endTime,
        ]);
    }

    public function getTechnicianCount(): int
    {
        return User::role('Tecnico')->count();
    }

    public function getServicesWithTimeBlock(int $timeBlockId): Collection
    {
        return ServicesAvailability::whereJsonContains('time_block_ids', $timeBlockId)->get();
    }

    public function getServiceById(int $serviceId): ?Service
    {
        return Service::find($serviceId);
    }

    public function updateServicePrice(int $serviceId, float $newPrice): bool
    {
        $service = $this->getServiceById($serviceId);

        if (!$service) {
            return false;
        }

        $service->price = $newPrice;
        return $service->save();
    }

    public function removeServiceAvailability(int $serviceId, array $timeBlock): bool
    {
        $timeBlockRecord = TimeBlock::where('day_of_week', $timeBlock['day_of_week'])
            ->where('hours', $timeBlock['hour'])
            ->first();

        if (!$timeBlockRecord) {
            return false;
        }

        $timeBlockId = $timeBlockRecord->id;

        $serviceAvailability = ServicesAvailability::where('services_id', $serviceId)->first();

        if (!$serviceAvailability) {
            return false;
        }

        $timeBlockIds = $serviceAvailability->time_block_ids;

        $timeBlockIds = array_values(array_filter($timeBlockIds, function ($id) use ($timeBlockId) {
            return $id != $timeBlockId;
        }));

        $serviceAvailability->time_block_ids = $timeBlockIds;
        $result = $serviceAvailability->save();

        // Limpiar caché
        Cache::forget('all_type_services');
        Cache::forget('available_time_blocks');

        return $result;
    }
}
