<?php
declare(strict_types=1);

namespace App\Models;

class User extends BaseModel
{
    protected static string $table = 'users';

    public static function findByEmail(string $email): ?array
    {
        $stmt = self::db()->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function verifyPassword(string $email, string $password): ?array
    {
        $user = self::findByEmail($email);
        if (!$user) {
            return null;
        }
        if (!password_verify($password, $user['password_hash'])) {
            return null;
        }
        if ($user['status'] !== 'active') {
            return null;
        }
        return $user;
    }

    public static function updateLastLogin(int $id): void
    {
        $stmt = self::db()->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?");
        $stmt->execute([$id]);
    }

    public static function create(?int $tenantId, array $data): int
{
    $stmt = self::db()->prepare(
        "INSERT INTO users (tenant_id, nama, email, password_hash, role, status)
         VALUES (?, ?, ?, ?, ?, 'active')"
    );
    $stmt->execute([
        $tenantId,
        $data['nama'],
        $data['email'],
        password_hash($data['password'], PASSWORD_BCRYPT),
        $data['role'] ?? 'admin',
    ]);
    return (int) self::db()->lastInsertId();
}

public static function findByTenantAndRole(int $tenantId, string $role): array
{
    $stmt = self::db()->prepare(
        "SELECT * FROM users WHERE tenant_id = ? AND role = ? ORDER BY id ASC"
    );
    $stmt->execute([$tenantId, $role]);
    return $stmt->fetchAll();
}

public static function updatePassword(int $userId, string $plainPassword): void
{
    $stmt = self::db()->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    $stmt->execute([password_hash($plainPassword, PASSWORD_BCRYPT), $userId]);
}

public static function findByIdAndTenant(int $userId, int $tenantId): ?array
{
    $stmt = self::db()->prepare("SELECT * FROM users WHERE id = ? AND tenant_id = ? LIMIT 1");
    $stmt->execute([$userId, $tenantId]);
    return $stmt->fetch() ?: null;
}

public static function updateProfile(int $userId, array $data): void
{
    $stmt = self::db()->prepare(
        "UPDATE users SET nama = ?, email = ? WHERE id = ?"
    );
    $stmt->execute([
        $data['nama'],
        $data['email'],
        $userId,
    ]);
}
}