<?php

namespace App\Services\Product;

use Illuminate\Http\JsonResponse;

interface ProductServiceInterface
{

    public function getAllProducts(): JsonResponse;

    public function getProductById(int $productId): JsonResponse;

    public function getServiceProducts(int $serviceId): JsonResponse;
}
