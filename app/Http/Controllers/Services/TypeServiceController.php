<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;
use App\Services\Services\TypeServices\TypeServiceServiceInterface;
use App\Helpers\Traits\ResponseTrait;
use Illuminate\Http\Request;

class TypeServiceController extends Controller
{
    use ResponseTrait;

    protected $typeServiceService;

    public function __construct(TypeServiceServiceInterface $typeServiceService)
    {
        $this->typeServiceService = $typeServiceService;
    }

    public function index()
    {
        return $this->typeServiceService->getAllTypeServices();
    }
}
