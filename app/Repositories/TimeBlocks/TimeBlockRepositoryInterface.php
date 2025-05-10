<?php
namespace App\Repositories\TimeBlocks;

interface TimeBlockRepositoryInterface
{
    public function getAllTimeBlocks(array $filters = []);
    public function getServiceCountByTimeBlock(int $timeBlockId): int;
}
