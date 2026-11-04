<?php
declare(strict_types=1);

/**
 * app/controllers/TaskController.php
 *
 * Handles both the HTML Kanban page and the JSON API
 * consumed by the frontend via Fetch.
 */
class TaskController extends Controller
{
    private TaskModel    $tasks;
    private ProjectModel $projects;

    public function __construct()
    {
        $this->tasks    = new TaskModel();
        $this->projects = new ProjectModel();
    }

    // ── HTML page ─────────────────────────────────────────────

    /** GET /tasks — Kanban board page */
    public function index(): void
    {
        $userId   = $this->requireAuth();
        $projects = $this->projects->allForUser($userId);
        $counts   = $this->tasks->countsByStatus($userId);

        $this->view('tasks/index', [
            'pageTitle'  => 'My Tasks',
            'activePage' => 'tasks',
            'projects'   => $projects,
            'counts'     => $counts,
            'user'       => $_SESSION,
        ]);
    }

    // ── JSON API ──────────────────────────────────────────────

    /** GET /api/tasks */
    public function apiAll(): void
    {
        $userId  = $this->requireAuth();
        $filters = [
            'project'  => $_GET['project']  ?? 'all',
            'status'   => $_GET['status']   ?? 'all',
            'priority' => $_GET['priority'] ?? 'all',
            'search'   => $_GET['search']   ?? '',
        ];
        $tasks = $this->tasks->allForUser($userId, $filters);
        foreach ($tasks as &$t) {
            $t['reminder'] = TaskModel::computeReminder($t);
        }
        unset($t);
        $this->json(['success' => true, 'data' => $tasks]);
    }

    /** GET /api/tasks/{id} */
    public function apiGet(string $id): void
    {
        $userId = $this->requireAuth();
        $task   = $this->tasks->findById((int)$id, $userId);
        if (!$task) {
            $this->json(['success' => false, 'message' => 'Task not found.'], 404);
        }
        $task['reminder'] = TaskModel::computeReminder($task);
        $this->json(['success' => true, 'data' => $task]);
    }

    /** POST /api/tasks */
    public function apiCreate(): void
    {
        $userId = $this->requireAuth();
        $body   = $this->jsonBody() ?? [];
        $errors = TaskModel::validate($body);
        if ($errors) {
            $this->json(['success' => false, 'message' => implode(' ', $errors)], 422);
        }

        $newId = $this->tasks->create($userId, $this->sanitizeTaskBody($body));
        $task  = $this->tasks->findById($newId, $userId);
        $task['reminder'] = TaskModel::computeReminder($task);
        $this->json(['success' => true, 'data' => $task], 201);
    }

    /** PUT /api/tasks/{id} */
    public function apiUpdate(string $id): void
    {
        $userId = $this->requireAuth();
        $body   = $this->jsonBody() ?? [];
        $errors = TaskModel::validate($body);
        if ($errors) {
            $this->json(['success' => false, 'message' => implode(' ', $errors)], 422);
        }

        $ok = $this->tasks->update((int)$id, $userId, $this->sanitizeTaskBody($body));
        if (!$ok) {
            $this->json(['success' => false, 'message' => 'Task not found or no changes made.'], 404);
        }
        $task = $this->tasks->findById((int)$id, $userId);
        $task['reminder'] = TaskModel::computeReminder($task);
        $this->json(['success' => true, 'data' => $task]);
    }

    /** PUT /api/tasks/{id}/status */
    public function apiStatus(string $id): void
    {
        $userId  = $this->requireAuth();
        $body    = $this->jsonBody() ?? [];
        $allowed = ['todo', 'inprogress', 'completed', 'overdue'];
        $status  = $body['status'] ?? '';

        if (!in_array($status, $allowed, true)) {
            $this->json(['success' => false, 'message' => "Invalid status '{$status}'."], 422);
        }

        $ok = $this->tasks->updateStatus((int)$id, $userId, $status);
        if (!$ok) {
            $this->json(['success' => false, 'message' => 'Task not found.'], 404);
        }
        $task = $this->tasks->findById((int)$id, $userId);
        $task['reminder'] = TaskModel::computeReminder($task);
        $this->json(['success' => true, 'data' => $task]);
    }

    /** DELETE /api/tasks/{id} */
    public function apiDelete(string $id): void
    {
        $userId = $this->requireAuth();
        $ok     = $this->tasks->delete((int)$id, $userId);
        if (!$ok) {
            $this->json(['success' => false, 'message' => 'Task not found.'], 404);
        }
        $this->json(['success' => true, 'data' => ['id' => (int)$id]]);
    }

    // ── Private helpers ───────────────────────────────────────

    /** @param array<string,mixed> $body */
    private function sanitizeTaskBody(array $body): array
    {
        return [
            'project_id'  => isset($body['project_id']) ? (int)$body['project_id'] : null,
            'title'       => $this->clean($body['title']),
            'description' => $this->clean($body['description'] ?? null),
            'subject'     => $this->clean($body['subject']     ?? null),
            'deadline'    => $body['deadline'],
            'priority'    => $body['priority']  ?? 'medium',
            'status'      => $body['status']    ?? 'todo',
            'reminder'    => $this->clean($body['reminder']    ?? null),
        ];
    }
}
