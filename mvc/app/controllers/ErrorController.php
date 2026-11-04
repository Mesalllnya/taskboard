<?php
declare(strict_types=1);

/** app/controllers/ErrorController.php */
class ErrorController extends Controller
{
    public function notFound(): void
    {
        http_response_code(404);
        $this->view('errors/404', ['pageTitle' => '404 — Not Found'], 'minimal');
    }

    public function serverError(): void
    {
        http_response_code(500);
        $this->view('errors/500', ['pageTitle' => '500 — Server Error'], 'minimal');
    }
}
