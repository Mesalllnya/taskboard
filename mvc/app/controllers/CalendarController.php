<?php
declare(strict_types=1);

/** app/controllers/CalendarController.php */
class CalendarController extends Controller
{
    private TaskModel $tasks;

    public function __construct() { $this->tasks = new TaskModel(); }

    public function index(): void
    {
        $userId = $this->requireAuth();
        $ym     = $_GET['month'] ?? date('Y-m');
        $map    = $this->tasks->forCalendar($userId, $ym);
        $this->view('calendar/index', [
            'pageTitle'   => 'Calendar',
            'activePage'  => 'calendar',
            'yearMonth'   => $ym,
            'taskMap'     => $map,
            'user'        => $_SESSION,
        ]);
    }

    public function apiEvents(): void
    {
        $userId = $this->requireAuth();
        $ym     = $_GET['month'] ?? date('Y-m');
        $map    = $this->tasks->forCalendar($userId, $ym);
        // Flatten to array of events for FullCalendar-like consumption
        $events = [];
        foreach ($map as $date => $taskList) {
            foreach ($taskList as $t) {
                $events[] = [
                    'id'    => $t['id'],
                    'title' => $t['title'],
                    'date'  => $date,
                    'status'    => $t['status'],
                    'priority'  => $t['priority'],
                    'subject'   => $t['subject'],
                    'project_color' => $t['project_color'] ?? '#0052CC',
                ];
            }
        }
        $this->json(['success' => true, 'data' => $events]);
    }
}
