<?php

namespace App\Repositories\Notification;
use Illuminate\Support\Collection;
use App\Models\Notifications;
use App\Models\User;


class NotificationRepository implements NotificationRepositoryInterface
{
    public function getReadNotificationsByUser(int $userId): Collection
    {
        return Notifications::readByUser($userId)->get();
    }

    public function getUnreadNotificationsByUser(int $userId): Collection
    {
        return Notifications::unreadByUser($userId)->get();
    }

    public function markAsRead(int $notificationId, int $userId): bool
    {
        $user = User::find($userId);

        if (!$user) {
            return false;
        }

        return $user->markNotificationAsRead($notificationId);
    }
}
