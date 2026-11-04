<?php
declare(strict_types=1);

/**
 * public/index.php — Front Controller
 *
 * All HTTP requests are routed here via .htaccess.
 * Bootstraps config, autoloads core classes, registers routes,
 * then dispatches to the appropriate Controller@action.
 */

// ── 0. Bootstrap ──────────────────────────────────────────────

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH',  ROOT_PATH . '/app');
define('CORE_PATH', ROOT_PATH . '/core');

// Application config (credentials, base URL)
require_once ROOT_PATH . '/config.php';

// Autoload core classes
foreach (['Database', 'Model', 'Controller', 'Router'] as $class) {
    require_once CORE_PATH . '/' . $class . '.php';
}

// Autoload models (needed by any controller)
foreach (glob(APP_PATH . '/models/*.php') as $modelFile) {
    require_once $modelFile;
}

// Error controller (for 404 / 500)
require_once APP_PATH . '/controllers/ErrorController.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── 1. Routes ──────────────────────────────────────────────────

$router = new Router();

// Auth
$router->get( '/auth/login',    'AuthController@loginForm');
$router->post('/auth/login',    'AuthController@loginAction');
$router->get( '/auth/logout',   'AuthController@logout');
$router->get( '/auth/register', 'AuthController@registerForm');
$router->post('/auth/register', 'AuthController@registerAction');

// Dashboard (root)
$router->get('/',          'DashboardController@index');
$router->get('/dashboard', 'DashboardController@index');

// Tasks — HTML pages
$router->get('/tasks',     'TaskController@index');

// Tasks — JSON API (consumed by frontend JS)
$router->get(   '/api/tasks',           'TaskController@apiAll');
$router->get(   '/api/tasks/{id}',      'TaskController@apiGet');
$router->post(  '/api/tasks',           'TaskController@apiCreate');
$router->put(   '/api/tasks/{id}',      'TaskController@apiUpdate');
$router->delete('/api/tasks/{id}',      'TaskController@apiDelete');
$router->put(   '/api/tasks/{id}/status','TaskController@apiStatus');

// Projects
$router->get( '/projects',           'ProjectController@index');
$router->get( '/projects/{id}',      'ProjectController@show');
$router->post('/projects',           'ProjectController@store');
$router->put( '/projects/{id}',      'ProjectController@update');
$router->delete('/projects/{id}',    'ProjectController@destroy');
$router->get( '/api/projects',       'ProjectController@apiAll');

// Calendar
$router->get('/calendar', 'CalendarController@index');
$router->get('/api/calendar', 'CalendarController@apiEvents');

// Analytics
$router->get('/analytics', 'AnalyticsController@index');
$router->get('/api/analytics', 'AnalyticsController@apiData');

// Notifications
$router->get('/api/notifications',           'NotificationController@apiAll');
$router->put('/api/notifications/{id}/read', 'NotificationController@apiMarkRead');
$router->put('/api/notifications/read-all',  'NotificationController@apiMarkAllRead');

// Settings
$router->get( '/settings',        'SettingsController@index');
$router->post('/settings/profile','SettingsController@updateProfile');
$router->post('/settings/password','SettingsController@updatePassword');

// Team / Files / Integrations (stub pages)
$router->get('/team',         'StubController@team');
$router->get('/files',        'StubController@files');
$router->get('/integrations', 'StubController@integrations');

// ── 2. Dispatch ────────────────────────────────────────────────

$uri    = $_SERVER['REQUEST_URI'] ?? '/';
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

$router->dispatch($uri, $method);
