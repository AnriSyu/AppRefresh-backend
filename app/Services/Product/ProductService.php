<?php

namespace App\Services\Product;

use App\Repositories\Product\ProductRepositoryInterface;
use App\Repositories\Services\ServiceRepositoryInterface;
use App\Helpers\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ProductService implements ProductServiceInterface
{
    use ResponseTrait;

    protected $productRepository;
    protected $serviceRepository;

    public function __construct(ProductRepositoryInterface $productRepository, ServiceRepositoryInterface $serviceRepository)
    {
        $this->productRepository = $productRepository;
        $this->serviceRepository = $serviceRepository;
    }

    public function getAllProducts(): JsonResponse
    {
        try {
            $products = $this->productRepository->getAllProducts();

            $formattedProducts = $products->map(function ($product) {
                return $this->formatProductData($product);
            });

            return $this->successJsonResponse([
                'products' => $formattedProducts,
                'total' => $formattedProducts->count()
            ]);

        } catch (\Exception $e) {
            return $this->errorJsonResponse($e->getMessage(), 500);
        }
    }

    public function getProductById(int $productId): JsonResponse
    {
        try {
            $product = $this->productRepository->getProductById($productId);

            if (!$product) {
                return $this->errorJsonResponse("Producto con ID $productId no encontrado", 404);
            }

            $formattedProduct = $this->formatProductData($product);

            return $this->successJsonResponse(['product' => $formattedProduct]);

        } catch (\Exception $e) {
            return $this->errorJsonResponse($e->getMessage(), 500);
        }
    }

    public function getServiceProducts(int $serviceId): JsonResponse
    {
        try {

            $service = $this->serviceRepository->getServiceById($serviceId);
            if (!$service) {
                return $this->errorJsonResponse("Servicio con ID $serviceId no encontrado", 404);
            }

            $movements = $this->productRepository->getServiceProducts($serviceId);

            $formattedProducts = $movements->map(function ($movement) {
                return $this->formatServiceProductData($movement);
            })->toArray();

            $responseData = $this->formatServiceProductsData($service, $formattedProducts);

            return $this->successJsonResponse($responseData);


        } catch (\Exception $e) {
            return $this->errorJsonResponse($e->getMessage(), 500);
        }
    }
}
