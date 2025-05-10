<?php
namespace App\Services\Services;

use Illuminate\Http\JsonResponse;

interface ServiceServiceInterface
{
    public function createService(array $data): JsonResponse;
    public function checkAvailability(int $timeBlockId): array;
    public function getUserServices(): JsonResponse;
    public function updateServiceHistory(int $serviceId, string $status): JsonResponse;
}
