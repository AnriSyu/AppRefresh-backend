<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\Product\ProductServiceInterface;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductServiceInterface $productService)
    {
        $this->productService = $productService;
    }

    public function getAllProducts(): JsonResponse
    {
        return $this->productService->getAllProducts();
    }

    public function getProductById(int $id): JsonResponse
    {
        return $this->productService->getProductById($id);
    }

    public function getServiceProducts(int $serviceId): JsonResponse
    {
        return $this->productService->getServiceProducts($serviceId);
    }

}
