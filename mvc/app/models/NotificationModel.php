<?php
declare(strict_types=1);

/**
 * app/models/NotificationModel.php
 */
class NotificationModel extends Model
{
    public function allForUser(int $userId, bool $unreadOnly = false): array
    {
        $sql = 'SELECT * FROM notifications WHERE user_id = :uid';
        if ($unreadOnly) $sql .= ' AND is_read = 0';
        $sql .= ' ORDER BY created_at DESC LIMIT 50';
        return $this->query($sql, [':uid' => $userId]);
    }

    public function countUnread(int $userId): int
    {
        $row = $this->queryOne(
            'SELECT COUNT(*) AS cnt FROM notifications WHERE user_id = :uid AND is_read = 0',
            [':uid' => $userId]
        );
        return (int) ($row['cnt'] ?? 0);
    }

    public function create(int $userId, ?int $taskId, string $type, string $message): int
    {
        $this->execute(
            'INSERT INTO notifications (user_id, task_id, type, message)
             VALUES (:uid, :tid, :type, :msg)',
            [':uid' => $userId, ':tid' => $taskId, ':type' => $type, ':msg' => $message]
        );
        return $this->lastInsertId();
    }

    public function markRead(int $id, int $userId): bool
    {
        return $this->execute(
            'UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :uid',
            [':id' => $id, ':uid' => $userId]
        ) > 0;
    }

    public function markAllRead(int $userId): int
    {
        return $this->execute(
            'UPDATE notifications SET is_read = 1 WHERE user_id = :uid AND is_read = 0',
            [':uid' => $userId]
        );
    }
}
