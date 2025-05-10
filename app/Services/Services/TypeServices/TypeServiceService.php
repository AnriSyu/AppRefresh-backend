<?php

namespace App\Services\Services\TypeServices;

use App\Repositories\Services\TypeServices\TypeServiceRepositoryInterface;
use App\Helpers\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;

class TypeServiceService implements TypeServiceServiceInterface
{
    use ResponseTrait;

    protected $typeServiceRepository;

    public function __construct(TypeServiceRepositoryInterface $typeServiceRepository)
    {
        $this->typeServiceRepository = $typeServiceRepository;
    }

    public function getAllTypeServices(): JsonResponse
    {
        try {
            $typeServices = $this->typeServiceRepository->getAllTypeServices();
            return $this->successJsonResponse($typeServices);
        } catch (\Exception $e) {
            return $this->errorJsonResponse($e->getMessage(), 500);
        }
    }
}
