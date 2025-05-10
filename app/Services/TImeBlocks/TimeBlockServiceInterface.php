<?php

namespace App\Services\TimeBlocks;

use Illuminate\Http\JsonResponse;

interface TimeBlockServiceInterface
{
    public function getAvailableTimeBlocks(array $filters = []): JsonResponse;
}
