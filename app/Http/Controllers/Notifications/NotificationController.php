<?php

namespace App\Http\Controllers\Notifications;


use App\Http\Controllers\Controller;
use App\Services\Notification\NotificationServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationServiceInterface $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function getReadNotifications(): JsonResponse
    {
        $userId = auth()->id();
        $notifications = $this->notificationService->getReadNotifications($userId);

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    public function getUnreadNotifications(): JsonResponse
    {
        $userId = auth()->id();
        $notifications = $this->notificationService->getUnreadNotifications($userId);

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    public function markAsRead(Request $request, int $notificationId): JsonResponse
    {
        $userId = auth()->id();
        $success = $this->notificationService->markAsRead($notificationId, $userId);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Notificación marcada como leída correctamente'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No se pudo marcar la notificación como leída'
        ], 400);
    }

}
