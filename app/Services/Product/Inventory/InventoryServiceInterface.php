<?php
namespace App\Services\Product\Inventory;

use Illuminate\Http\JsonResponse;

interface InventoryServiceInterface
{
    public function addProductsToService(array $data): JsonResponse;
}
