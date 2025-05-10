<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;



class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function notifications(): BelongsToMany
    {
        return $this->belongsToMany(Notifications::class, 'notification_user', 'user_id', 'notification_id')
                    ->withPivot('is_read')
                    ->withTimestamps();
    }

    /**
     * Get only unread notifications for the user
     */
    public function unreadNotifications()
    {
        return $this->notifications()->wherePivot('is_read', false);
    }

    /**
     * Get only read notifications for the user
     */
    public function readNotifications()
    {
        return $this->notifications()->wherePivot('is_read', true);
    }

    /**
     * Get count of unread notifications
     */
    public function unreadNotificationsCount(): int
    {
        return $this->unreadNotifications()->count();
    }

    /**
     * Mark a notification as read
     */
    public function markNotificationAsRead(int $notificationId): bool
    {
        $notification = $this->notifications()->where('notifications.id', $notificationId)->first();

        if (!$notification) {
            return false;
        }

        return $this->notifications()->updateExistingPivot($notificationId, ['is_read' => true]);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole('Administrador');
    }

}
