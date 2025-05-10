<?php

namespace App\Repositories\Product;

use App\Models\Product;
use Illuminate\Support\Collection;

interface ProductRepositoryInterface
{

    public function getAllProducts(): Collection;

    public function getProductById(int $productId): ?Product;

    public function getServiceProducts(int $serviceId): Collection;
}
