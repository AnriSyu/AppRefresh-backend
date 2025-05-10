<?php
namespace App\Repositories\Product\Inventory;

use App\Models\Product;
use App\Models\InventoryMovement;

class InventoryRepository implements InventoryRepositoryInterface
{
    public function getProductById(int $productId): ?Product
    {
        return Product::find($productId);
    }

    public function updateProductStock(int $productId, int $quantityChange): bool
    {
        $product = $this->getProductById($productId);

        if (!$product) {
            return false;
        }

        $product->stock += $quantityChange;
        return $product->save();
    }

    public function createInventoryMovement(array $data): InventoryMovement
    {
        return InventoryMovement::create($data);
    }
}
