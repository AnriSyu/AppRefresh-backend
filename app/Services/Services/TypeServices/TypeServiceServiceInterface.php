<?php
namespace App\Services\Services\TypeServices;

use Illuminate\Http\JsonResponse;

interface TypeServiceServiceInterface
{
    public function getAllTypeServices(): JsonResponse;
}
