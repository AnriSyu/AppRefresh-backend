<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Services\Product\Inventory\InventoryServiceInterface;
use App\Helpers\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryMovementController extends Controller
{
    //
    use ResponseTrait;

    protected $inventoryService;

    public function __construct(InventoryServiceInterface $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Add products to a service as a sale
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addProductsToService(Request $request)
    {
        $validatedData = $request->validate([
            'service_id' => 'required|exists:services,id',
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        return $this->inventoryService->addProductsToService($validatedData);
    }
}
