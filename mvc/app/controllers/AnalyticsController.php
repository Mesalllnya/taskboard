<?php
declare(strict_types=1);

/** app/controllers/AnalyticsController.php */
class AnalyticsController extends Controller
{
    private TaskModel $tasks;

    public function __construct() { $this->tasks = new TaskModel(); }

    public function index(): void
    {
        $userId  = $this->requireAuth();
        $counts  = $this->tasks->countsByStatus($userId);
        $subjects = $this->tasks->perSubject($userId);
        $priorities = $this->tasks->perPriority($userId);
        $trend   = $this->tasks->deadlineTrend($userId);

        $this->view('analytics/index', [
            'pageTitle'   => 'Analytics',
            'activePage'  => 'analytics',
            'counts'      => $counts,
            'subjects'    => $subjects,
            'priorities'  => $priorities,
            'trend'       => $trend,
            'user'        => $_SESSION,
        ]);
    }

    public function apiData(): void
    {
        $userId = $this->requireAuth();
        $this->json(['success' => true, 'data' => [
            'counts'     => $this->tasks->countsByStatus($userId),
            'subjects'   => $this->tasks->perSubject($userId),
            'priorities' => $this->tasks->perPriority($userId),
            'trend'      => $this->tasks->deadlineTrend($userId),
        ]]);
    }
}
