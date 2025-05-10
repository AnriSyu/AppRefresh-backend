<?php
namespace App\Repositories\Product\Inventory;

use App\Models\Product;
use App\Models\InventoryMovement;

interface InventoryRepositoryInterface
{
    public function getProductById(int $productId): ?Product;
    public function updateProductStock(int $productId, int $quantityChange): bool;
    public function createInventoryMovement(array $data): InventoryMovement;
}
