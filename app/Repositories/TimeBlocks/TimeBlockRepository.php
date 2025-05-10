<?php

namespace App\Repositories\TimeBlocks;

use App\Models\TimeBlock;
use App\Models\ServicesAvailability;
use Illuminate\Support\Collection;

class TimeBlockRepository implements TimeBlockRepositoryInterface
{
    public function getAllTimeBlocks(array $filters = []): Collection
    {
        $query = TimeBlock::query();

        if (isset($filters['day_of_week'])) {
            $query->where('day_of_week', $filters['day_of_week']);
        }

        return $query->get();
    }

    public function getServiceCountByTimeBlock(int $timeBlockId): int
    {
        return ServicesAvailability::whereJsonContains('time_block_ids', $timeBlockId)->count();
    }
}
