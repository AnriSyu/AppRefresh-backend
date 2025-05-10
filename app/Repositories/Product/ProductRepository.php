<?php

namespace App\Repositories\Product;

use App\Models\Product;
use Illuminate\Support\Collection;
use App\Models\InventoryMovement;

class ProductRepository implements ProductRepositoryInterface
{

    public function getAllProducts(): Collection
    {
        return Product::all();
    }

    public function getProductById(int $productId): ?Product
    {
        return Product::find($productId);
    }

    public function getServiceProducts(int $serviceId): Collection
    {
        $movements = InventoryMovement::with('product')
            ->where('service_id', $serviceId)
            ->where('movement_type', 'V')
            ->get();

        $groupedProducts = [];

        foreach ($movements as $movement) {
            $productId = $movement->product_id;

            if (isset($groupedProducts[$productId])) {
                $groupedProducts[$productId]->quantity += $movement->quantity;
            } else {
                $clone = clone $movement;
                $groupedProducts[$productId] = $clone;
            }
        }

        return collect(array_values($groupedProducts));
    }

}
