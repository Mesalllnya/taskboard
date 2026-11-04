<?php
// mvc/config.php — Application configuration
// Copy this file and never commit credentials to version control.

// ── Database ──────────────────────────────────────────────────
define('DB_HOST',    'localhost');
define('DB_PORT',    '3306');
define('DB_NAME',    'taskboard_mvc');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

// ── Application ───────────────────────────────────────────────
// Set BASE_URL to the sub-path where public/ is served.
// For XAMPP: http://localhost/taskboard/mvc/public
define('BASE_URL', 'http://localhost/taskboard/mvc/public');

// Application name shown in the UI
define('APP_NAME', 'TaskBoard');
define('APP_VERSION', '2.0.0');

// Session lifetime (seconds)
define('SESSION_LIFETIME', 86400);

// CSRF token session key
define('CSRF_TOKEN_KEY', '_csrf_token');
