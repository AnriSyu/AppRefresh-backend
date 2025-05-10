<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;
use App\Services\Services\ServiceServiceInterface;
use App\Helpers\Traits\ResponseTrait;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    use ResponseTrait;

    protected $serviceService;

    public function __construct(ServiceServiceInterface $serviceService)
    {
        $this->serviceService = $serviceService;
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'type_service_id' => 'required|exists:types_services,id',
            'time_block_id' => 'required|exists:time_blocks,id',
        ]);

        $validatedData['client_id'] = auth()->id();

        return $this->serviceService->createService($validatedData);
    }

    public function getUserServices()
    {
        return $this->serviceService->getUserServices();
    }

    public function ServiceById($id)
    {
        return $this->serviceService->getServiceById($id);
    }

    public function updateServiceHistory(Request $request)
    {
        $validatedData = $request->validate([
            'service_id' => 'required|exists:services,id',
            'status' => 'required|string|max:1|in:E,F',
            'time_block.day_of_week' => 'nullable|string',
            'time_block.hour' => 'nullable|string',
        ]);

        $validatedData['technical_id'] = auth()->id();
        $serviceId = $validatedData['service_id'];
        $status = $validatedData['status'];

        $timeBlock = null;

        if (isset($validatedData['time_block']['day_of_week']) && isset($validatedData['time_block']['hour'])) {
            $timeBlock = $validatedData['time_block'];
        }

        return $this->serviceService->updateServiceHistory($serviceId, $status, $timeBlock);
    }

}
