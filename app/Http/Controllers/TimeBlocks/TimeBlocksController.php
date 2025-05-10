<?php
namespace App\Http\Controllers\TimeBlocks;

use App\Http\Controllers\Controller;
use App\Services\TimeBlocks\TimeBlockServiceInterface;
use Illuminate\Http\Request;

class TimeBlocksController extends Controller
{
    protected $timeBlockService;

    public function __construct(TimeBlockServiceInterface $timeBlockService)
    {
        $this->timeBlockService = $timeBlockService;
    }

    /**
     * Get available time blocks by day of week
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailableTimeBlocks(Request $request)
    {
        $validatedData = $request->validate([
            'day_of_week' => 'required|in:monday,tuesday,wednesday,thursday,friday',
        ]);

        return $this->timeBlockService->getAvailableTimeBlocks($validatedData);
    }
}
