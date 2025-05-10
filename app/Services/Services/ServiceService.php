<?php

namespace App\Services\Services;

use App\Models\User;
use App\Models\Service;
use App\Models\ServiceHistory;
use App\Models\ServicesAvailability;
use App\Models\TimeBlock;
use App\Repositories\Services\ServiceRepositoryInterface;
use App\Helpers\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ServiceService implements ServiceServiceInterface
{
    use ResponseTrait;

    protected $serviceRepository;

    public function __construct(ServiceRepositoryInterface $serviceRepository)
    {
        $this->serviceRepository = $serviceRepository;
    }

    public function createService(array $data): JsonResponse
    {
        try {
            $timeBlockId = $data['time_block_id'];
            $availabilityCheck = $this->checkAvailability($timeBlockId);

            if (!$availabilityCheck['available']) {
                return $this->errorJsonResponse($availabilityCheck['reason'], 422);
            }

            DB::beginTransaction();

            $service = $this->serviceRepository->createService($data);

            $this->serviceRepository->createServiceAvailability($service->id, [$timeBlockId]);

            $this->serviceRepository->createServiceHistory(
                $service->id,
                'P',
                'Servicio creado y en estado pendiente'
            );

            DB::commit();

            $timeBlockData = TimeBlock::find($timeBlockId);
            $timeBlock = null;
            if ($timeBlockData) {
                $timeBlock = [
                    'day_of_week' => $timeBlockData->day_of_week,
                    'hour' => $timeBlockData->hours
                ];
            }

            $lastHistory = null;
            if ($service->history && $service->history->count() > 0) {
                $lastHistory = $service->history->sortByDesc('created_at')->first();
            }

            $formattedService = $this->formatServiceBasicData($service, $timeBlock, $lastHistory);
            return $this->successJsonResponse([
                'message' => 'Servicio creado correctamente',
                'service' => $formattedService
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorJsonResponse($e->getMessage(), 500);
        }
    }

    public function checkAvailability(int $timeBlockId): array
    {
        $totalTechnicians = $this->serviceRepository->getTechnicianCount();

        if ($totalTechnicians === 0) {
            return [
                'available' => false,
                'reason' => 'No hay técnicos registrados en el sistema'
            ];
        }

        $servicesWithThisTimeBlock = $this->serviceRepository->getServicesWithTimeBlock($timeBlockId);

        if ($servicesWithThisTimeBlock->count() >= $totalTechnicians) {
            return [
                'available' => false,
                'reason' => 'No hay técnicos disponibles para este bloque de tiempo. Todos los técnicos ya tienen servicios asignados en este horario.'
            ];
        }

        return [
            'available' => true,
            'reason' => 'Hay técnicos disponibles para este bloque de tiempo'
        ];
    }

    public function getUserServices(): JsonResponse
    {
        try {
            $userId = Auth::id();

            if (!$userId) {
                return $this->errorJsonResponse('Usuario no autenticado', 401);
            }

            $services = $this->serviceRepository->getServicesByUserId($userId);

            $formattedServices = $services->map(function ($service) {
                $timeBlockIds = [];
                if ($service->availabilities && $service->availabilities->count() > 0) {
                    $availability = $service->availabilities->first();
                    if ($availability) {
                        $timeBlockIds = $availability->time_block_ids;
                    }
                }

                $timeBlock = null;
                if (!empty($timeBlockIds)) {
                    $timeBlockData = TimeBlock::whereIn('id', $timeBlockIds)->first();
                    if ($timeBlockData) {
                        $timeBlock = [
                            'day_of_week' => $timeBlockData->day_of_week,
                            'hour' => $timeBlockData->hours
                        ];
                    }
                }

                $lastHistory = null;
                if ($service->history && $service->history->count() > 0) {
                    $lastHistory = $service->history->sortByDesc('created_at')->first();
                }

                return $this->formatServiceBasicData($service, $timeBlock, $lastHistory);

            });

            return $this->successJsonResponse([
                'services' => $formattedServices
            ]);

        } catch (\Exception $e) {
            return $this->errorJsonResponse($e->getMessage(), 500);
        }
    }

    public function getServiceById(int $serviceId): JsonResponse
    {
        try {
            $service = $this->serviceRepository->getServiceById($serviceId);

            if (!$service) {
                return $this->errorJsonResponse('Servicio no encontrado', 404);
            }

            $timeBlockIds = [];
            if ($service->availabilities && $service->availabilities->count() > 0) {
                $availability = $service->availabilities->first();
                if ($availability) {
                    $timeBlockIds = $availability->time_block_ids;
                }
            }

            $timeBlock = null;
            if (!empty($timeBlockIds)) {
                $timeBlockData = TimeBlock::whereIn('id', $timeBlockIds)->first();
                if ($timeBlockData) {
                    $timeBlock = [
                        'day_of_week' => $timeBlockData->day_of_week,
                        'hour' => $timeBlockData->hours
                    ];
                }
            }

            $lastHistory = null;
            if ($service->history && $service->history->count() > 0) {
                $lastHistory = $service->history->sortByDesc('created_at')->first();
            }

            $formattedService = $this->formatServiceBasicData($service, $timeBlock, $lastHistory);

            return $this->successJsonResponse([
                'service' => $formattedService
            ]);

        } catch (\Exception $e) {
            return $this->errorJsonResponse($e->getMessage(), 500);
        }
    }


    public function updateServiceHistory(int $serviceId, string $status, ?array $timeBlock = null): JsonResponse
    {
        try {
            $service = $this->serviceRepository->getServiceById($serviceId);
            $endTime = null;
            $observations = null;
            if (!$service) {
                return $this->errorJsonResponse('Servicio no encontrado', 404);
            }
            DB::beginTransaction();

            if ($status === 'E') {
                $observations = 'Servicio en proceso a las ' . Carbon::now()->format('Y-m-d H:i:s');
            }else if($status === 'F') {
                $observations = 'Servicio completado a las ' . Carbon::now()->format('Y-m-d H:i:s');
                $endTime = Carbon::now();
                if ($timeBlock) {
                    $removed = $this->serviceRepository->removeServiceAvailability($serviceId, $timeBlock);

                    if (!$removed) {
                        throw new \Exception('No se pudo eliminar la disponibilidad del bloque de tiempo');
                    }
                }
            }

            $this->serviceRepository->createServiceHistory($serviceId, $status, $observations, $endTime);


            DB::commit();

            return $this->successJsonResponse([
                'message' => 'Historial del servicio actualizado correctamente',
                'status' => $status,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorJsonResponse($e->getMessage(), 500);
        }
    }


}
