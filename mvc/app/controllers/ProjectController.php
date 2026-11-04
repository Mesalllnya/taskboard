<?php
declare(strict_types=1);

/** app/controllers/ProjectController.php */
class ProjectController extends Controller
{
    private ProjectModel $projects;
    private TaskModel    $tasks;

    public function __construct()
    {
        $this->projects = new ProjectModel();
        $this->tasks    = new TaskModel();
    }

    public function index(): void
    {
        $userId   = $this->requireAuth();
        $projects = $this->projects->allForUser($userId);
        $this->view('projects/index', [
            'pageTitle'  => 'Projects',
            'activePage' => 'projects',
            'projects'   => $projects,
            'user'       => $_SESSION,
        ]);
    }

    public function show(string $id): void
    {
        $userId  = $this->requireAuth();
        $project = $this->projects->findById((int)$id, $userId);
        if (!$project) { $this->redirect(BASE_URL . '/projects'); }
        $tasks = $this->tasks->allForUser($userId, ['project' => (int)$id]);
        $this->view('projects/show', [
            'pageTitle'  => $project['name'],
            'activePage' => 'projects',
            'project'    => $project,
            'tasks'      => $tasks,
            'user'       => $_SESSION,
        ]);
    }

    public function store(): void
    {
        $userId = $this->requireAuth();
        $name   = $this->clean($_POST['name'] ?? '');
        $desc   = $this->clean($_POST['description'] ?? null);
        $color  = $this->clean($_POST['color'] ?? '#0052CC');
        if ($name) {
            $this->projects->create($userId, $name, $desc, $color);
        }
        $this->redirect(BASE_URL . '/projects');
    }

    public function update(string $id): void
    {
        $userId = $this->requireAuth();
        $name   = $this->clean($_POST['name'] ?? '');
        $desc   = $this->clean($_POST['description'] ?? null);
        $color  = $this->clean($_POST['color'] ?? '#0052CC');
        if ($name) {
            $this->projects->update((int)$id, $userId, $name, $desc, $color);
        }
        $this->redirect(BASE_URL . '/projects');
    }

    public function destroy(string $id): void
    {
        $userId = $this->requireAuth();
        $this->projects->archive((int)$id, $userId);
        $this->redirect(BASE_URL . '/projects');
    }

    public function apiAll(): void
    {
        $userId   = $this->requireAuth();
        $projects = $this->projects->allForUser($userId);
        $this->json(['success' => true, 'data' => $projects]);
    }
}
