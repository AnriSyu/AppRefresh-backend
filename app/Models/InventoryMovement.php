<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'movement_type',
        'quantity',
        'reason',
        'technical_id',
        'service_id',
    ];

    /**
     * Get the product that owns the inventory movement.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Get the technical that owns the inventory movement.
     */
    public function technical(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technical_id');
    }

    /**
     * Get the service that owns the inventory movement.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    /**
     * Get the movement type in human-readable format.
     */
    public function getMovementTypeNameAttribute(): string
    {
        return [
            'E' => 'Entrada',
            'S' => 'Salida',
            'V' => 'Venta'
        ][$this->movement_type] ?? 'Desconocido';
    }
}
