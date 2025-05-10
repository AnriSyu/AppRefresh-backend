<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'stock',
        'price',
    ];

    /**
     * Get the inventory movements for this product.
     */
    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'product_id');
    }

    /**
     * Get the product type in human-readable format.
     */
    public function getTypeNameAttribute(): string
    {
        return [
            'R' => 'Repuesto',
            'H' => 'Herramienta',
            'P' => 'Producto'
        ][$this->type] ?? 'Desconocido';
    }
}
