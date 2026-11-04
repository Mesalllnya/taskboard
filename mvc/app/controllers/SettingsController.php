<?php
declare(strict_types=1);

/** app/controllers/SettingsController.php */
class SettingsController extends Controller
{
    private UserModel $users;

    public function __construct() { $this->users = new UserModel(); }

    public function index(): void
    {
        $userId = $this->requireAuth();
        $user   = $this->users->findById($userId);
        $this->view('settings/index', [
            'pageTitle'  => 'Settings',
            'activePage' => 'settings',
            'userRecord' => $user,
            'user'       => $_SESSION,
        ]);
    }

    public function updateProfile(): void
    {
        $userId  = $this->requireAuth();
        $name    = $this->clean($_POST['name']     ?? '');
        $sem     = $this->clean($_POST['semester'] ?? '');
        $major   = $this->clean($_POST['major']    ?? '');
        if ($name) {
            $this->users->updateProfile($userId, $name, $sem, $major);
            $_SESSION['user_name'] = $name;
        }
        $this->redirect(BASE_URL . '/settings?saved=1');
    }

    public function updatePassword(): void
    {
        $userId  = $this->requireAuth();
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password']     ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $user    = $this->users->findById($userId);

        if (!password_verify($current, $user['password_hash'] ?? '')) {
            $this->redirect(BASE_URL . '/settings?pwd_error=1');
            return;
        }
        if (strlen($new) < 8 || $new !== $confirm) {
            $this->redirect(BASE_URL . '/settings?pwd_error=2');
            return;
        }
        $this->users->updatePassword($userId, $new);
        $this->redirect(BASE_URL . '/settings?pwd_saved=1');
    }
}
