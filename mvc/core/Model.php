<?php
declare(strict_types=1);

/**
 * core/Model.php
 * Abstract base model — wraps PDO and provides helper methods.
 */
abstract class Model
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Execute a prepared statement and return all rows.
     * @param  array<string,mixed> $params
     * @return array<int,array<string,mixed>>
     */
    protected function query(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Execute a prepared statement and return the first row or null.
     * @param  array<string,mixed> $params
     * @return array<string,mixed>|null
     */
    protected function queryOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Execute an INSERT/UPDATE/DELETE and return affected row count.
     * @param  array<string,mixed> $params
     */
    protected function execute(string $sql, array $params = []): int
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /** Return the last auto-increment id. */
    protected function lastInsertId(): int
    {
        return (int) $this->db->lastInsertId();
    }
}
