<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'duration',
        'type_service_id',
        'client_id',
        'technical_id',
    ];

    /**
     * Get the type service that owns the service.
     */
    public function typeService(): BelongsTo
    {
        return $this->belongsTo(TypeService::class, 'type_service_id');
    }

    /**
     * Get the client that owns the service.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Get the technical that owns the service.
     */
    public function technical(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technical_id');
    }

    /**
     * Get the service history entries for this service.
     */
    public function history(): HasMany
    {
        return $this->hasMany(ServiceHistory::class, 'service_id');
    }

    /**
     * Get the inventory movements for this service.
     */
    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'service_id');
    }

    /**
     * Get the evaluations for this service.
     */
    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class, 'service_id');
    }

    public function availabilities()
    {
        return $this->hasMany(ServicesAvailability::class, 'services_id');
    }
}
