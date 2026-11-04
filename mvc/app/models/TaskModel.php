<?php
declare(strict_types=1);

/**
 * app/models/TaskModel.php
 *
 * All queries are scoped to a user_id to prevent data leakage.
 */
class TaskModel extends Model
{
    // ── Read ──────────────────────────────────────────────────

    /**
     * All tasks for a user, with optional filters.
     *
     * @param  array{project?:int,status?:string,priority?:string,search?:string} $filters
     * @return array<int,array<string,mixed>>
     */
    public function allForUser(int $userId, array $filters = []): array
    {
        $where  = ['t.user_id = :uid'];
        $params = [':uid' => $userId];

        if (!empty($filters['project']) && $filters['project'] !== 'all') {
            $where[]             = 't.project_id = :proj';
            $params[':proj']     = (int) $filters['project'];
        }
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $where[]             = 't.status = :status';
            $params[':status']   = $filters['status'];
        }
        if (!empty($filters['priority']) && $filters['priority'] !== 'all') {
            $where[]             = 't.priority = :priority';
            $params[':priority'] = $filters['priority'];
        }
        if (!empty($filters['search'])) {
            $where[]             = '(t.title LIKE :search OR t.subject LIKE :search)';
            $params[':search']   = '%' . $filters['search'] . '%';
        }

        $whereClause = implode(' AND ', $where);

        return $this->query(
            "SELECT t.*, p.name AS project_name, p.color AS project_color
             FROM tasks t
             LEFT JOIN projects p ON t.project_id = p.id
             WHERE {$whereClause}
             ORDER BY FIELD(t.status,'overdue','inprogress','todo','completed'),
                      t.deadline ASC",
            $params
        );
    }

    /**
     * Summary counts grouped by status for a user.
     * Returns: ['todo' => N, 'inprogress' => N, 'completed' => N, 'overdue' => N]
     * @return array<string,int>
     */
    public function countsByStatus(int $userId): array
    {
        $rows = $this->query(
            "SELECT status, COUNT(*) AS cnt FROM tasks
             WHERE user_id = :uid GROUP BY status",
            [':uid' => $userId]
        );
        $counts = ['todo' => 0, 'inprogress' => 0, 'completed' => 0, 'overdue' => 0];
        foreach ($rows as $row) {
            $counts[$row['status']] = (int) $row['cnt'];
        }
        return $counts;
    }

    /**
     * Tasks grouped by deadline date for calendar view.
     * @return array<string,array<int,array<string,mixed>>>
     */
    public function forCalendar(int $userId, string $yearMonth): array
    {
        // yearMonth = 'YYYY-MM'
        $rows = $this->query(
            "SELECT t.*, p.color AS project_color
             FROM tasks t
             LEFT JOIN projects p ON t.project_id = p.id
             WHERE t.user_id = :uid
               AND DATE_FORMAT(t.deadline, '%Y-%m') = :ym
             ORDER BY t.deadline ASC",
            [':uid' => $userId, ':ym' => $yearMonth]
        );
        $map = [];
        foreach ($rows as $row) {
            $map[$row['deadline']][] = $row;
        }
        return $map;
    }

    /** Analytics: tasks per subject. */
    public function perSubject(int $userId): array
    {
        return $this->query(
            "SELECT COALESCE(subject, '(No Subject)') AS subject,
                    COUNT(*) AS total,
                    SUM(status = 'completed') AS done
             FROM tasks WHERE user_id = :uid
             GROUP BY subject ORDER BY total DESC",
            [':uid' => $userId]
        );
    }

    /** Analytics: tasks per priority. */
    public function perPriority(int $userId): array
    {
        return $this->query(
            "SELECT priority, COUNT(*) AS total,
                    SUM(status = 'completed') AS done
             FROM tasks WHERE user_id = :uid
             GROUP BY priority",
            [':uid' => $userId]
        );
    }

    /** Analytics: deadline trend — count of tasks due per day (last 30 days). */
    public function deadlineTrend(int $userId): array
    {
        return $this->query(
            "SELECT deadline AS date, COUNT(*) AS total,
                    SUM(status = 'completed') AS done
             FROM tasks
             WHERE user_id = :uid
               AND deadline BETWEEN DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                                AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
             GROUP BY deadline ORDER BY deadline ASC",
            [':uid' => $userId]
        );
    }

    // ── Single ────────────────────────────────────────────────

    public function findById(int $id, int $userId): ?array
    {
        return $this->queryOne(
            'SELECT t.*, p.name AS project_name, p.color AS project_color
             FROM tasks t
             LEFT JOIN projects p ON t.project_id = p.id
             WHERE t.id = :id AND t.user_id = :uid LIMIT 1',
            [':id' => $id, ':uid' => $userId]
        );
    }

    // ── Write ─────────────────────────────────────────────────

    /**
     * Insert a new task. Returns the new id.
     * @param  array<string,mixed> $data
     */
    public function create(int $userId, array $data): int
    {
        $this->execute(
            'INSERT INTO tasks
                (user_id, project_id, title, description, subject,
                 deadline, priority, status, reminder)
             VALUES
                (:uid, :proj, :title, :desc, :subj,
                 :deadline, :priority, :status, :reminder)',
            [
                ':uid'      => $userId,
                ':proj'     => $data['project_id']  ?? null,
                ':title'    => $data['title'],
                ':desc'     => $data['description'] ?? null,
                ':subj'     => $data['subject']     ?? null,
                ':deadline' => $data['deadline'],
                ':priority' => $data['priority']    ?? 'medium',
                ':status'   => $data['status']      ?? 'todo',
                ':reminder' => $data['reminder']    ?? null,
            ]
        );
        return $this->lastInsertId();
    }

    /**
     * Update an existing task (all fields).
     * @param  array<string,mixed> $data
     */
    public function update(int $id, int $userId, array $data): bool
    {
        return $this->execute(
            'UPDATE tasks
             SET project_id  = :proj,
                 title       = :title,
                 description = :desc,
                 subject     = :subj,
                 deadline    = :deadline,
                 priority    = :priority,
                 status      = :status,
                 reminder    = :reminder
             WHERE id = :id AND user_id = :uid',
            [
                ':proj'     => $data['project_id']  ?? null,
                ':title'    => $data['title'],
                ':desc'     => $data['description'] ?? null,
                ':subj'     => $data['subject']     ?? null,
                ':deadline' => $data['deadline'],
                ':priority' => $data['priority']    ?? 'medium',
                ':status'   => $data['status']      ?? 'todo',
                ':reminder' => $data['reminder']    ?? null,
                ':id'       => $id,
                ':uid'      => $userId,
            ]
        ) > 0;
    }

    /** Update only the status column. */
    public function updateStatus(int $id, int $userId, string $status): bool
    {
        return $this->execute(
            'UPDATE tasks SET status = :status WHERE id = :id AND user_id = :uid',
            [':status' => $status, ':id' => $id, ':uid' => $userId]
        ) > 0;
    }

    public function delete(int $id, int $userId): bool
    {
        return $this->execute(
            'DELETE FROM tasks WHERE id = :id AND user_id = :uid',
            [':id' => $id, ':uid' => $userId]
        ) > 0;
    }

    // ── Validation ────────────────────────────────────────────

    /**
     * Validate task payload. Returns array of error strings.
     * @param  array<string,mixed> $data
     * @return string[]
     */
    public static function validate(array $data): array
    {
        $errors = [];
        if (empty(trim($data['title'] ?? ''))) {
            $errors[] = 'Title is required.';
        }
        if (empty($data['deadline']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['deadline'])) {
            $errors[] = 'Deadline must be a valid date (YYYY-MM-DD).';
        }
        $allowedPriority = ['high', 'medium', 'low'];
        if (isset($data['priority']) && !in_array($data['priority'], $allowedPriority, true)) {
            $errors[] = 'Invalid priority.';
        }
        $allowedStatus = ['todo', 'inprogress', 'completed', 'overdue'];
        if (isset($data['status']) && !in_array($data['status'], $allowedStatus, true)) {
            $errors[] = 'Invalid status.';
        }
        return $errors;
    }

    /**
     * Compute a human-readable reminder label from the deadline.
     * @param  array<string,mixed> $task
     */
    public static function computeReminder(array $task): ?string
    {
        if (!empty($task['reminder'])) return $task['reminder'];
        if ($task['status'] === 'completed') return null;
        try {
            $now      = new DateTimeImmutable('today');
            $deadline = new DateTimeImmutable($task['deadline']);
            $diff     = (int) $now->diff($deadline)->days;
            $isPast   = $deadline < $now;
        } catch (Exception) {
            return null;
        }
        if ($isPast || $task['status'] === 'overdue') return $diff . ' hari terlambat';
        if ($diff === 0) return 'Hari ini!';
        return $diff . ' hari lagi';
    }
}
