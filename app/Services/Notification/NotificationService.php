<?php

namespace App\Services\Notification;

use App\Repositories\Notification\NotificationRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class NotificationService implements NotificationServiceInterface
{
    protected $notificationRepository;

    public function __construct(NotificationRepositoryInterface $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }


    public function getReadNotifications(int $userId): Collection
    {
        try {
            return $this->notificationRepository->getReadNotificationsByUser($userId);
        } catch (\Exception $e) {
            Log::error('Error obteniendo notificaciones leídas: ' . $e->getMessage());
            return collect();
        }
    }

    public function getUnreadNotifications(int $userId): Collection
    {
        try {
            return $this->notificationRepository->getUnreadNotificationsByUser($userId);
        } catch (\Exception $e) {
            Log::error('Error obteniendo notificaciones no leídas: ' . $e->getMessage());
            return collect();
        }
    }

    public function markAsRead(int $notificationId, int $userId): bool
    {
        try {
            return $this->notificationRepository->markAsRead($notificationId, $userId);
        } catch (\Exception $e) {
            Log::error('Error marcando notificación como leída: ' . $e->getMessage());
            return false;
        }
    }

}
