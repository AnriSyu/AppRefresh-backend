<?php

namespace App\Services\Notification;
use Illuminate\Support\Collection;

interface NotificationServiceInterface
{

    public function getReadNotifications(int $userId): Collection;
    public function getUnreadNotifications(int $userId): Collection;
    public function markAsRead(int $notificationId, int $userId): bool;
}
