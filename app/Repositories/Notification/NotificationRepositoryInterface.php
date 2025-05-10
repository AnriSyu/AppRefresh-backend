<?php

namespace App\Repositories\Notification;
use Illuminate\Support\Collection;

interface NotificationRepositoryInterface
{

    public function getReadNotificationsByUser(int $userId): Collection;
    public function getUnreadNotificationsByUser(int $userId): Collection;
    public function markAsRead(int $notificationId, int $userId): bool;

}
