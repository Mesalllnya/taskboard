<?php
declare(strict_types=1);

/** app/controllers/StubController.php — placeholder pages */
class StubController extends Controller
{
    private function stub(string $title, string $page): void
    {
        $this->requireAuth();
        $this->view('stub', [
            'pageTitle'  => $title,
            'activePage' => $page,
            'user'       => $_SESSION,
        ]);
    }
    public function team():         void { $this->stub('Team',         'team');         }
    public function files():        void { $this->stub('Files',        'files');        }
    public function integrations(): void { $this->stub('Integrations', 'integrations'); }
}
