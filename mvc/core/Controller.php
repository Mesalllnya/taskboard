<?php
declare(strict_types=1);

/**
 * core/Controller.php
 * Abstract base controller — view rendering helpers.
 */
abstract class Controller
{
    /**
     * Render a view file, injecting $data into scope.
     *
     * @param string               $view   e.g. 'tasks/index'
     * @param array<string,mixed>  $data   variables available inside the view
     * @param string               $layout Layout template name (default 'main')
     */
    protected function view(string $view, array $data = [], string $layout = 'main'): void
    {
        // Extract data so view can use $varName instead of $data['varName']
        extract($data, EXTR_SKIP);

        $viewPath = APP_PATH . '/views/' . $view . '.php';
        if (!file_exists($viewPath)) {
            http_response_code(500);
            echo "View not found: {$view}";
            return;
        }

        // Capture view content
        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        // Render inside layout (layout receives $content)
        $layoutPath = APP_PATH . '/views/layouts/' . $layout . '.php';
        if (file_exists($layoutPath)) {
            require $layoutPath;
        } else {
            echo $content;
        }
    }

    /**
     * Send JSON response (for API / AJAX endpoints).
     * @param mixed $data
     */
    protected function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Redirect to a URL.
     */
    protected function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }

    /**
     * Return the currently authenticated user id from session,
     * or redirect to /auth/login.
     */
    protected function requireAuth(): int
    {
        if (empty($_SESSION['user_id'])) {
            $this->redirect(BASE_URL . '/auth/login');
        }
        return (int) $_SESSION['user_id'];
    }

    /** Sanitise a string value (strip tags + trim). */
    protected function clean(?string $value): ?string
    {
        if ($value === null) return null;
        return trim(strip_tags($value));
    }

    /**
     * Read and decode the raw JSON request body.
     * Returns null on failure.
     * @return array<string,mixed>|null
     */
    protected function jsonBody(): ?array
    {
        $raw = file_get_contents('php://input');
        if (empty($raw)) return null;
        $data = json_decode($raw, true);
        return (json_last_error() === JSON_ERROR_NONE) ? $data : null;
    }
}
