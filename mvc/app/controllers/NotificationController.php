<?php
declare(strict_types=1);

/** app/controllers/NotificationController.php */
class NotificationController extends Controller
{
    private NotificationModel $notifs;

    public function __construct() { $this->notifs = new NotificationModel(); }

    public function apiAll(): void
    {
        $userId = $this->requireAuth();
        $items  = $this->notifs->allForUser($userId);
        $unread = $this->notifs->countUnread($userId);
        $this->json(['success' => true, 'data' => $items, 'unread' => $unread]);
    }

    public function apiMarkRead(string $id): void
    {
        $userId = $this->requireAuth();
        $this->notifs->markRead((int)$id, $userId);
        $this->json(['success' => true]);
    }

    public function apiMarkAllRead(): void
    {
        $userId = $this->requireAuth();
        $this->notifs->markAllRead($userId);
        $this->json(['success' => true]);
    }
}
