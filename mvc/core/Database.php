<?php
declare(strict_types=1);

/**
 * core/Database.php
 * PDO singleton factory — one connection per request.
 */
class Database
{
    private static ?PDO $instance = null;

    /** Returns the shared PDO connection. */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
            );
            self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        return self::$instance;
    }

    /** Prevent construction / cloning. */
    private function __construct() {}
    private function __clone()     {}
}
