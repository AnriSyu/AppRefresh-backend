<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceHistory extends Model
{
    use HasFactory;

    protected $table = 'services_history';

    protected $fillable = [
        'service_id',
        'status',
        'observations',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    /**
     * Get the service that owns the service history.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    /**
     * Get the status in human-readable format.
     */
    public function getStatusNameAttribute(): string
    {
        return [
            'P' => 'Pendiente',
            'C' => 'Cancelado',
            'A' => 'Activo',
            'E' => 'En progreso',
            'F' => 'Finalizado',
        ][$this->status] ?? 'Desconocido';
    }
}
