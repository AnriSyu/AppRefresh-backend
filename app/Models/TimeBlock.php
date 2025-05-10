<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeBlock extends Model
{
    protected $fillable = ['day_of_week', 'hours'];
    protected $casts = [
        'hours' => 'string',
    ];
}
