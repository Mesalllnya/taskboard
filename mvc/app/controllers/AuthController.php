<?php
declare(strict_types=1);
require_once APP_PATH . '/controllers/ErrorController.php';

/**
 * app/controllers/AuthController.php
 */
class AuthController extends Controller
{
    private UserModel $users;

    public function __construct()
    {
        $this->users = new UserModel();
    }

    public function loginForm(): void
    {
        if (!empty($_SESSION['user_id'])) {
            $this->redirect(BASE_URL . '/dashboard');
        }
        $this->view('auth/login', ['pageTitle' => 'Login'], 'auth');
    }

    public function loginAction(): void
    {
        $email    = $this->clean($_POST['email']    ?? '');
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) {
            $this->view('auth/login', ['pageTitle' => 'Login', 'error' => 'Email dan password wajib diisi.'], 'auth');
            return;
        }

        $user = $this->users->attemptLogin($email, $password);
        if (!$user) {
            $this->view('auth/login', ['pageTitle' => 'Login', 'error' => 'Email atau password salah.'], 'auth');
            return;
        }

        session_regenerate_id(true);
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_init'] = $user['avatar_initials'];

        $this->redirect(BASE_URL . '/dashboard');
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        $this->redirect(BASE_URL . '/auth/login');
    }

    public function registerForm(): void
    {
        if (!empty($_SESSION['user_id'])) {
            $this->redirect(BASE_URL . '/dashboard');
        }
        $this->view('auth/register', ['pageTitle' => 'Daftar'], 'auth');
    }

    public function registerAction(): void
    {
        $name     = $this->clean($_POST['name']     ?? '');
        $email    = $this->clean($_POST['email']    ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm']  ?? '';

        $errors = [];
        if (!$name)                       $errors[] = 'Nama wajib diisi.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email tidak valid.';
        if (strlen($password) < 8)        $errors[] = 'Password minimal 8 karakter.';
        if ($password !== $confirm)        $errors[] = 'Konfirmasi password tidak cocok.';
        if ($this->users->findByEmail($email)) $errors[] = 'Email sudah terdaftar.';

        if ($errors) {
            $this->view('auth/register', [
                'pageTitle' => 'Daftar',
                'errors'    => $errors,
                'old'       => compact('name', 'email'),
            ], 'auth');
            return;
        }

        $newId = $this->users->register($name, $email, $password);
        session_regenerate_id(true);
        $user = $this->users->findById($newId);
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_init'] = $user['avatar_initials'];

        $this->redirect(BASE_URL . '/dashboard');
    }
}
