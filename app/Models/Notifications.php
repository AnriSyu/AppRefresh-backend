<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class Notifications extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'type',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'notification_user', 'notification_id', 'user_id')
                    ->withPivot('is_read')
                    ->withTimestamps();
    }

    public function scopeUnreadByUser(Builder $query, $userId): Builder
    {
        return $query->whereHas('users', function ($query) use ($userId) {
            $query->where('users.id', $userId)
                  ->where('notification_user.is_read', false);
        })->orderBy('created_at', 'desc');
    }

    public function scopeReadByUser(Builder $query, $userId): Builder
    {
        return $query->whereHas('users', function ($query) use ($userId) {
            $query->where('users.id', $userId)
                  ->where('notification_user.is_read', true);
        })->orderBy('created_at', 'desc');
    }

    public function isReadByUser(int $userId): bool
    {
        return $this->users()
                    ->where('users.id', $userId)
                    ->wherePivot('is_read', true)
                    ->exists();
    }
}
