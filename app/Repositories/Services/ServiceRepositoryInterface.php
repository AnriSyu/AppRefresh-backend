<?php

namespace App\Repositories\Services;

use App\Models\Service;
use Illuminate\Support\Collection;


interface ServiceRepositoryInterface
{
    public function createService(array $data): Service;
    public function getServicesByUserId(int $userId): Collection;
    public function createServiceAvailability(int $serviceId, array $timeBlockIds): void;
    public function createServiceHistory(int $serviceId, string $status, string $observations): void;
    public function getTechnicianCount(): int;
    public function getServicesWithTimeBlock(int $timeBlockId): Collection;
    public function getServiceById(int $serviceId): ?Service;
    public function updateServicePrice(int $serviceId, float $newPrice): bool;
    public function removeServiceAvailability(int $serviceId, array $timeBlock): bool;
}
