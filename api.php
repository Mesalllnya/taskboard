<?php
// =============================================================
// api.php — Student Task Management REST API
// =============================================================
// Endpoints (all via query-string ?action=...):
//
//   GET    ?action=all                 → list all tasks
//   GET    ?action=get&id=X            → get single task
//   POST   ?action=create              → create task (JSON body)
//   PUT    ?action=update&id=X         → update task (JSON body)
//   DELETE ?action=delete&id=X         → delete task
//   PUT    ?action=status&id=X         → update status only
//
// All responses are JSON:  { "success": bool, "data": ..., "message": "..." }
// =============================================================

declare(strict_types=1);

require_once __DIR__ . '/config.php';

// ── CORS headers (adjust origin in production) ───────────────
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Pre-flight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ── Route dispatcher ─────────────────────────────────────────
$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

try {
    $pdo = getDB();

    match (true) {
        // ── GET all tasks ──────────────────────────────────────
        $action === 'all' && $method === 'GET'
            => handleGetAll($pdo),

        // ── GET single task ────────────────────────────────────
        $action === 'get' && $method === 'GET'
            => handleGet($pdo, (int)($_GET['id'] ?? 0)),

        // ── CREATE task ────────────────────────────────────────
        $action === 'create' && $method === 'POST'
            => handleCreate($pdo),

        // ── UPDATE task ────────────────────────────────────────
        $action === 'update' && $method === 'PUT'
            => handleUpdate($pdo, (int)($_GET['id'] ?? 0)),

        // ── UPDATE status only ─────────────────────────────────
        $action === 'status' && $method === 'PUT'
            => handleStatusUpdate($pdo, (int)($_GET['id'] ?? 0)),

        // ── DELETE task ────────────────────────────────────────
        $action === 'delete' && $method === 'DELETE'
            => handleDelete($pdo, (int)($_GET['id'] ?? 0)),

        // ── Unknown route ──────────────────────────────────────
        default => respond(404, false, null, 'Unknown endpoint.')
    };

} catch (PDOException $e) {
    // Never expose raw DB errors to the client
    error_log('[TaskAPI] PDOException: ' . $e->getMessage());
    respond(500, false, null, 'Database error. Please try again later.');
} catch (Throwable $e) {
    error_log('[TaskAPI] Error: ' . $e->getMessage());
    respond(500, false, null, 'Internal server error.');
}


// =============================================================
// HANDLERS
// =============================================================

/**
 * GET ?action=all
 * Returns every task ordered by deadline ASC.
 */
function handleGetAll(PDO $pdo): void
{
    $stmt = $pdo->prepare(
        'SELECT id, title, description, subject, deadline,
                priority, reminder, status, created_at, updated_at
         FROM tasks
         ORDER BY FIELD(status,"overdue","inprogress","todo","completed"),
                  deadline ASC'
    );
    $stmt->execute();
    $tasks = $stmt->fetchAll();

    // Compute a human-readable reminder label if not stored
    foreach ($tasks as &$task) {
        $task['reminder'] = computeReminder($task);
    }
    unset($task);

    respond(200, true, $tasks);
}

/**
 * GET ?action=get&id=X
 */
function handleGet(PDO $pdo, int $id): void
{
    requirePositiveId($id);

    $stmt = $pdo->prepare(
        'SELECT id, title, description, subject, deadline,
                priority, reminder, status, created_at, updated_at
         FROM tasks WHERE id = :id LIMIT 1'
    );
    $stmt->execute([':id' => $id]);
    $task = $stmt->fetch();

    if (!$task) {
        respond(404, false, null, "Task #$id not found.");
    }

    $task['reminder'] = computeReminder($task);
    respond(200, true, $task);
}

/**
 * POST ?action=create
 * Body (JSON): { title, description?, subject?, deadline, priority, status? }
 */
function handleCreate(PDO $pdo): void
{
    $body = parseJsonBody();

    // Validate required fields
    $errors = validateTaskBody($body);
    if ($errors) {
        respond(422, false, null, implode(' ', $errors));
    }

    $stmt = $pdo->prepare(
        'INSERT INTO tasks (title, description, subject, deadline, priority, reminder, status)
         VALUES (:title, :description, :subject, :deadline, :priority, :reminder, :status)'
    );
    $stmt->execute([
        ':title'       => sanitize($body['title']),
        ':description' => sanitize($body['description'] ?? null),
        ':subject'     => sanitize($body['subject']     ?? null),
        ':deadline'    => $body['deadline'],
        ':priority'    => $body['priority']  ?? 'medium',
        ':reminder'    => sanitize($body['reminder']    ?? null),
        ':status'      => $body['status']    ?? 'todo',
    ]);

    $newId = (int)$pdo->lastInsertId();
    handleGet($pdo, $newId); // return full record
}

/**
 * PUT ?action=update&id=X
 * Body (JSON): any subset of task fields.
 */
function handleUpdate(PDO $pdo, int $id): void
{
    requirePositiveId($id);
    $body = parseJsonBody();

    $errors = validateTaskBody($body);
    if ($errors) {
        respond(422, false, null, implode(' ', $errors));
    }

    // Fetch existing to merge (so partial updates work)
    $existing = fetchTaskById($pdo, $id);

    $stmt = $pdo->prepare(
        'UPDATE tasks
         SET title       = :title,
             description = :description,
             subject     = :subject,
             deadline    = :deadline,
             priority    = :priority,
             reminder    = :reminder,
             status      = :status
         WHERE id = :id'
    );
    $stmt->execute([
        ':title'       => sanitize($body['title']       ?? $existing['title']),
        ':description' => sanitize($body['description'] ?? $existing['description']),
        ':subject'     => sanitize($body['subject']     ?? $existing['subject']),
        ':deadline'    => $body['deadline']              ?? $existing['deadline'],
        ':priority'    => $body['priority']              ?? $existing['priority'],
        ':reminder'    => sanitize($body['reminder']    ?? $existing['reminder']),
        ':status'      => $body['status']               ?? $existing['status'],
        ':id'          => $id,
    ]);

    handleGet($pdo, $id); // return updated record
}

/**
 * PUT ?action=status&id=X
 * Body (JSON): { status: "todo"|"inprogress"|"completed"|"overdue" }
 * Lightweight endpoint for drag-and-drop column changes.
 */
function handleStatusUpdate(PDO $pdo, int $id): void
{
    requirePositiveId($id);
    $body = parseJsonBody();

    $allowed = ['todo', 'inprogress', 'completed', 'overdue'];
    $status  = $body['status'] ?? '';

    if (!in_array($status, $allowed, true)) {
        respond(422, false, null, "Invalid status '$status'. Allowed: " . implode(', ', $allowed));
    }

    $stmt = $pdo->prepare('UPDATE tasks SET status = :status WHERE id = :id');
    $stmt->execute([':status' => $status, ':id' => $id]);

    handleGet($pdo, $id);
}

/**
 * DELETE ?action=delete&id=X
 */
function handleDelete(PDO $pdo, int $id): void
{
    requirePositiveId($id);
    fetchTaskById($pdo, $id); // throws 404 if missing

    $stmt = $pdo->prepare('DELETE FROM tasks WHERE id = :id');
    $stmt->execute([':id' => $id]);

    respond(200, true, ['id' => $id], 'Task deleted successfully.');
}


// =============================================================
// HELPERS
// =============================================================

/** Send JSON response and exit. */
function respond(int $httpCode, bool $success, mixed $data, string $message = ''): never
{
    http_response_code($httpCode);
    echo json_encode([
        'success' => $success,
        'data'    => $data,
        'message' => $message,
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/** Decode JSON request body or abort. */
function parseJsonBody(): array
{
    $raw = file_get_contents('php://input');
    if (empty($raw)) {
        respond(400, false, null, 'Request body is empty.');
    }
    $data = json_decode($raw, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        respond(400, false, null, 'Invalid JSON: ' . json_last_error_msg());
    }
    return $data;
}

/** Strip tags and trim; returns null for null input. */
function sanitize(mixed $value): ?string
{
    if ($value === null) return null;
    return trim(strip_tags((string)$value));
}

/** Abort with 400 if $id is not a positive integer. */
function requirePositiveId(int $id): void
{
    if ($id <= 0) {
        respond(400, false, null, 'A valid positive integer id is required.');
    }
}

/** Fetch task by id or 404. */
function fetchTaskById(PDO $pdo, int $id): array
{
    $stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $task = $stmt->fetch();
    if (!$task) {
        respond(404, false, null, "Task #$id not found.");
    }
    return $task;
}

/**
 * Validate the minimal required fields for create/update.
 * Returns an array of error strings (empty = valid).
 */
function validateTaskBody(array $body): array
{
    $errors = [];
    $allowedPriority = ['high', 'medium', 'low'];
    $allowedStatus   = ['todo', 'inprogress', 'completed', 'overdue'];

    if (isset($body['title']) && trim($body['title']) === '') {
        $errors[] = 'Title cannot be empty.';
    }
    if (isset($body['deadline']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $body['deadline'])) {
        $errors[] = 'Deadline must be in YYYY-MM-DD format.';
    }
    if (isset($body['priority']) && !in_array($body['priority'], $allowedPriority, true)) {
        $errors[] = 'Invalid priority value.';
    }
    if (isset($body['status']) && !in_array($body['status'], $allowedStatus, true)) {
        $errors[] = 'Invalid status value.';
    }

    return $errors;
}

/**
 * Compute a human-readable reminder label from the deadline.
 * Used to fill in auto-reminder for tasks that don't have one stored.
 */
function computeReminder(array $task): ?string
{
    // If a reminder is explicitly stored, prefer it
    if (!empty($task['reminder'])) {
        return $task['reminder'];
    }

    $status = $task['status'];
    if (in_array($status, ['completed'], true)) {
        return null;
    }

    try {
        $now      = new DateTimeImmutable('today');
        $deadline = new DateTimeImmutable($task['deadline']);
        $diff     = (int)$now->diff($deadline)->days;
        $isPast   = $deadline < $now;
    } catch (Exception) {
        return null;
    }

    if ($isPast || $status === 'overdue') {
        return $diff . ' hari terlambat';
    }
    if ($diff === 0) {
        return 'Hari ini!';
    }
    return $diff . ' hari lagi';
}
