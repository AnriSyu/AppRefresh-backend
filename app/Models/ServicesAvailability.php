<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class ServicesAvailability extends Model
{
    protected $fillable = ['services_id', 'time_block_ids'];

    protected $casts = [
        'time_block_ids' => 'array',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'services_id');
    }

    public function getTimeBlocks()
    {
        return TimeBlock::whereIn('id', $this->time_block_ids)->get();
    }
}
