<?php
// =============================================================
// config.php — Database connection (PDO)
// =============================================================
// Centralises credentials so api.php never hard-codes them.
// In production, move these values into environment variables
// or a .env file that is excluded from version control.
// =============================================================

define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'student_tasks');
define('DB_USER', 'root');          // ← change for production
define('DB_PASS', '');              // ← change for production
define('DB_CHARSET', 'utf8mb4');

/**
 * getDB()
 * Returns a shared PDO instance (lazy-singleton pattern).
 * Throws a PDOException on connection failure — caught in api.php.
 */
function getDB(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,   // use real prepared statements
        ];

        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }

    return $pdo;
}
