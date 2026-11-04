<?php
declare(strict_types=1);

/**
 * app/controllers/DashboardController.php
 */
class DashboardController extends Controller
{
    private TaskModel    $tasks;
    private ProjectModel $projects;

    public function __construct()
    {
        $this->tasks    = new TaskModel();
        $this->projects = new ProjectModel();
    }

    public function index(): void
    {
        $userId   = $this->requireAuth();
        $counts   = $this->tasks->countsByStatus($userId);
        $total    = array_sum($counts);
        $active   = $total - ($counts['completed'] ?? 0);
        $projects = $this->projects->allForUser($userId);

        // Recent tasks (limited to 8 for dashboard overview)
        $recentTasks = $this->tasks->allForUser($userId, ['status' => 'todo']);
        $recentTasks = array_merge(
            $recentTasks,
            $this->tasks->allForUser($userId, ['status' => 'inprogress'])
        );
        usort($recentTasks, fn($a, $b) => strcmp($a['deadline'], $b['deadline']));
        $recentTasks = array_slice($recentTasks, 0, 8);

        $this->view('dashboard/index', [
            'pageTitle'    => 'Dashboard',
            'activePage'   => 'dashboard',
            'counts'       => $counts,
            'total'        => $total,
            'active'       => $active,
            'projects'     => $projects,
            'recentTasks'  => $recentTasks,
            'user'         => $_SESSION,
        ]);
    }
}
