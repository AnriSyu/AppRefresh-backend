<?php
namespace App\Services\Product\Inventory;

use App\Repositories\Product\Inventory\InventoryRepositoryInterface;
use App\Repositories\Services\ServiceRepositoryInterface;
use App\Helpers\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class InventoryService implements InventoryServiceInterface
{
    use ResponseTrait;

    protected $inventoryRepository;
    protected $serviceRepository;

    public function __construct(
        InventoryRepositoryInterface $inventoryRepository,
        ServiceRepositoryInterface $serviceRepository
    ) {
        $this->inventoryRepository = $inventoryRepository;
        $this->serviceRepository = $serviceRepository;
    }

    public function addProductsToService(array $data): JsonResponse
    {
        try {
            $serviceId = $data['service_id'];
            $products = $data['products'];
            $userId = Auth::id();

            $service = $this->serviceRepository->getServiceById($serviceId);

            if (!$service || $service->client_id !== $userId) {
                return $this->errorJsonResponse('No tienes permiso para modificar este servicio', 403);
            }

            if (!$service->technical_id) {
                return $this->errorJsonResponse('No se puede agregar productos a un servicio sin técnico asignado', 422);
            }

            if ($service->price == NULL) {
                return $this->errorJsonResponse('No se puede agregar porque el servicio no tiene un precio establecido', 422);
            }

            DB::beginTransaction();

            $totalProductsPrice = 0;
            $addedProducts = [];

            foreach ($products as $productData) {
                $productId = $productData['product_id'];
                $quantity = $productData['quantity'];

                $product = $this->inventoryRepository->getProductById($productId);

                if (!$product) {
                    DB::rollBack();
                    return $this->errorJsonResponse("Producto con ID $productId no encontrado", 404);
                }

                if ($product->stock < $quantity) {
                    DB::rollBack();
                    return $this->errorJsonResponse("Stock insuficiente para el producto {$product->name}. Stock actual: {$product->stock}", 422);
                }

                $movement = $this->inventoryRepository->createInventoryMovement([
                    'product_id' => $productId,
                    'movement_type' => 'V',
                    'quantity' => $quantity,
                    'reason' => 'Venta a ' . Auth::user()->name,
                    'technical_id' => $service->technical_id,
                    'service_id' => $serviceId
                ]);

                $this->inventoryRepository->updateProductStock($productId, -$quantity);

                $totalProductsPrice += $product->price * $quantity;

                $addedProducts[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'quantity' => $quantity,
                    'price' => $product->price,
                    'subtotal' => $product->price * $quantity
                ];
            }

            $newPrice = $service->price + $totalProductsPrice;
            $this->serviceRepository->updateServicePrice($serviceId, $newPrice);

            DB::commit();

            return $this->successJsonResponse([
                'message' => 'Productos añadidos correctamente al servicio',
                'products_added' => $addedProducts,
                'total_products_price' => $totalProductsPrice,
                'service_original_price' => $service->price,
                'service_new_price' => $newPrice
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorJsonResponse($e->getMessage(), 500);
        }
    }
}
