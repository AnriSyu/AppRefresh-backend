<?php
namespace App\Services\TimeBlocks;

use App\Repositories\TimeBlocks\TimeBlockRepositoryInterface;
use App\Helpers\Traits\ResponseTrait;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class TimeBlockService implements TimeBlockServiceInterface
{
    use ResponseTrait;

    protected $timeBlockRepository;

    public function __construct(TimeBlockRepositoryInterface $timeBlockRepository)
    {
        $this->timeBlockRepository = $timeBlockRepository;
    }

    public function getAvailableTimeBlocks(array $filters = []): JsonResponse
    {
        try {
            $cacheKey = 'available_time_blocks';
            if (isset($filters['day_of_week'])) {
                $cacheKey .= '_' . $filters['day_of_week'];
            }
            Cache::forget($cacheKey);

            $availableTimeBlocks = Cache::remember($cacheKey, 1800, function () use ($filters) {
                $technicians = User::role('Tecnico')->count();

                if ($technicians === 0) {
                    return [];
                }

                $timeBlocks = $this->timeBlockRepository->getAllTimeBlocks($filters);

                $availableBlocks = [];

                foreach ($timeBlocks as $timeBlock) {
                    $servicesCount = $this->timeBlockRepository->getServiceCountByTimeBlock($timeBlock->id);

                    if ($servicesCount < $technicians) {
                        $availableSlots = $technicians - $servicesCount;

                        $availableBlocks[] = $this->formatTimeBlockData($timeBlock, $availableSlots, $technicians);
                    }
                }

                return $availableBlocks;
            });

            return $this->successJsonResponse([
                'available_time_blocks' => $availableTimeBlocks
            ]);

        } catch (\Exception $e) {
            return $this->errorJsonResponse($e->getMessage(), 500);
        }
    }
}
