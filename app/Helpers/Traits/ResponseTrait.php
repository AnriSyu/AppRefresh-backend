<?php
namespace App\Helpers\Traits;
use Illuminate\Http\JsonResponse;

trait ResponseTrait
{
    protected function successJsonResponse($data, int $status = 200): JsonResponse
    {
        return response()->json($data, $status);
    }

    protected function errorJsonResponse(string $message, int $status): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'status' => 'error'
        ], $status);
    }

    protected function formatServiceBasicData($service, $timeBlock, $lastHistory): array
    {
        return [
            'service_id' => $service->id,
            'service_description' => $service->description,
            'type_service' => $service->typeService ? $service->typeService->name : null,
            'time_block' => $timeBlock,
            'technical_name' => $service->technical ? $service->technical->name : null,
            'client_name' => $service->client ? $service->client->name : null,
            'last_history' => $lastHistory ? [
                'status_name' => $lastHistory->getStatusNameAttribute(),
                'status' => $lastHistory->status,
            ] : null,
        ];
    }

    protected function formatTimeBlockData($timeBlock, $availableSlots, $totalTechnicians): array
    {
        return [
            'id' => $timeBlock->id,
            'day_of_week' => $timeBlock->day_of_week,
            'hours' => $timeBlock->hours,
            'available_slots' => $availableSlots,
            'total_technicians' => $totalTechnicians
        ];
    }

    protected function formatProductData($product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'type_name' => $product->getTypeNameAttribute(),
            'price' => $product->price,
            'stock' => $product->stock,
            'status' => $product->stock > 0 ? 'Disponible' : 'Agotado',
        ];
    }

    protected function formatServiceProductData($movement): array
    {
        $product = $movement->product;

        return [
            'product_id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'type_name' => $product->getTypeNameAttribute(),
            'quantity' => $movement->quantity,
            'unit_price' => $product->price,
            'total_price' => $movement->quantity * $product->price,
        ];
    }

    protected function formatServiceProductsData($service, $formattedProducts): array
    {
        $totalProductsPrice = collect($formattedProducts)->sum('total_price');

        return [
            'service_id' => $service->id,
            'service_name' => $service->name,
            'service_description' => $service->description,
            'service_price' => $service->price,
            'products' => $formattedProducts,
            'total_products' => count($formattedProducts),
            'total_products_price' => $totalProductsPrice,
        ];
    }

}
