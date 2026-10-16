<?php
declare(strict_types=1);

namespace App\Helpers;

use App\Models\BaseModel;

class AuditLog
{
    public static function write(
        string $aksi,
        ?string $tabel = null,
        ?int $recordId = null,
        ?array $detail = null
    ): void {
        $stmt = BaseModel::db()->prepare(
            "INSERT INTO audit_logs (tenant_id, user_id, aksi, tabel, record_id, detail, ip_address)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $_SESSION['tenant_id'] ?? null,
            $_SESSION['user_id']   ?? null,
            $aksi,
            $tabel,
            $recordId,
            $detail ? json_encode($detail, JSON_UNESCAPED_UNICODE) : null,
            $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }
}