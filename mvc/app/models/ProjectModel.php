<?php
declare(strict_types=1);

/**
 * app/models/ProjectModel.php
 */
class ProjectModel extends Model
{
    public function allForUser(int $userId): array
    {
        return $this->query(
            'SELECT p.*,
                    (SELECT COUNT(*) FROM tasks t WHERE t.project_id = p.id) AS task_count,
                    (SELECT COUNT(*) FROM tasks t WHERE t.project_id = p.id AND t.status = "completed") AS done_count
             FROM projects p
             WHERE p.user_id = :uid AND p.status = "active"
             ORDER BY p.name ASC',
            [':uid' => $userId]
        );
    }

    public function findById(int $id, int $userId): ?array
    {
        return $this->queryOne(
            'SELECT * FROM projects WHERE id = :id AND user_id = :uid LIMIT 1',
            [':id' => $id, ':uid' => $userId]
        );
    }

    public function create(int $userId, string $name, ?string $description, string $color): int
    {
        $this->execute(
            'INSERT INTO projects (user_id, name, description, color)
             VALUES (:uid, :name, :desc, :color)',
            [':uid' => $userId, ':name' => $name, ':desc' => $description, ':color' => $color]
        );
        return $this->lastInsertId();
    }

    public function update(int $id, int $userId, string $name, ?string $description, string $color): bool
    {
        return $this->execute(
            'UPDATE projects SET name = :name, description = :desc, color = :color
             WHERE id = :id AND user_id = :uid',
            [':name' => $name, ':desc' => $description, ':color' => $color, ':id' => $id, ':uid' => $userId]
        ) > 0;
    }

    public function archive(int $id, int $userId): bool
    {
        return $this->execute(
            "UPDATE projects SET status = 'archived' WHERE id = :id AND user_id = :uid",
            [':id' => $id, ':uid' => $userId]
        ) > 0;
    }
}
