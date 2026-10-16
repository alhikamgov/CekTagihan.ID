<?php
declare(strict_types=1);

namespace App\Models;

class NotificationLog extends BaseModel
{
    protected static string $table = 'notification_logs';

    public static function create(
        int $tenantId,
        ?int $payerId,
        string $tipe,
        string $tujuan,
        string $pesan
    ): int {
        $stmt = self::db()->prepare(
            "INSERT INTO notification_logs (tenant_id, payer_id, tipe, tujuan, pesan, status)
             VALUES (?, ?, ?, ?, ?, 'pending')"
        );
        $stmt->execute([$tenantId, $payerId, $tipe, $tujuan, $pesan]);
        return (int) self::db()->lastInsertId();
    }

    public static function markSent(int $id, string $response = ''): void
    {
        $stmt = self::db()->prepare(
            "UPDATE notification_logs SET status = 'sent', response = ? WHERE id = ?"
        );
        $stmt->execute([$response, $id]);
    }

    public static function markFailed(int $id, string $response = ''): void
    {
        $stmt = self::db()->prepare(
            "UPDATE notification_logs SET status = 'failed', response = ? WHERE id = ?"
        );
        $stmt->execute([$response, $id]);
    }

    public static function allByTenant(int $tenantId, int $limit = 50): array
    {
        $stmt = self::db()->prepare(
            "SELECT * FROM notification_logs
             WHERE tenant_id = ?
             ORDER BY created_at DESC
             LIMIT ?"
        );
        $stmt->bindValue(1, $tenantId, \PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}