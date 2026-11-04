<?php
declare(strict_types=1);

/**
 * app/models/UserModel.php
 */
class UserModel extends Model
{
    // ── Finders ───────────────────────────────────────────────

    public function findById(int $id): ?array
    {
        return $this->queryOne(
            'SELECT id, name, email, avatar_initials, role, semester, major, created_at
             FROM users WHERE id = :id LIMIT 1',
            [':id' => $id]
        );
    }

    public function findByEmail(string $email): ?array
    {
        return $this->queryOne(
            'SELECT * FROM users WHERE email = :email LIMIT 1',
            [':email' => $email]
        );
    }

    // ── Auth ──────────────────────────────────────────────────

    /**
     * Attempt login. Returns the user row on success, null on failure.
     * @return array<string,mixed>|null
     */
    public function attemptLogin(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);
        if (!$user) return null;
        if (!password_verify($password, $user['password_hash'])) return null;
        return $user;
    }

    /**
     * Register a new user. Returns the new user's id.
     */
    public function register(string $name, string $email, string $password): int
    {
        $initials = $this->buildInitials($name);
        $this->execute(
            'INSERT INTO users (name, email, password_hash, avatar_initials)
             VALUES (:name, :email, :hash, :initials)',
            [
                ':name'     => $name,
                ':email'    => $email,
                ':hash'     => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
                ':initials' => $initials,
            ]
        );
        return $this->lastInsertId();
    }

    // ── Updates ───────────────────────────────────────────────

    public function updateProfile(int $id, string $name, string $semester, string $major): bool
    {
        $initials = $this->buildInitials($name);
        return $this->execute(
            'UPDATE users SET name = :name, semester = :semester, major = :major,
                              avatar_initials = :initials
             WHERE id = :id',
            [
                ':name'     => $name,
                ':semester' => $semester,
                ':major'    => $major,
                ':initials' => $initials,
                ':id'       => $id,
            ]
        ) > 0;
    }

    public function updatePassword(int $id, string $newPassword): bool
    {
        return $this->execute(
            'UPDATE users SET password_hash = :hash WHERE id = :id',
            [':hash' => password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]), ':id' => $id]
        ) > 0;
    }

    // ── Helpers ───────────────────────────────────────────────

    private function buildInitials(string $name): string
    {
        $words = preg_split('/\s+/', trim($name));
        $init  = '';
        foreach (array_slice($words, 0, 2) as $w) {
            $init .= mb_strtoupper(mb_substr($w, 0, 1));
        }
        return $init ?: 'U';
    }
}
