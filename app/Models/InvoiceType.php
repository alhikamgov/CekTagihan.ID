<?php
declare(strict_types=1);

namespace App\Models;

class InvoiceType extends BaseModel
{
    protected static string $table = 'invoice_types';

    public static function allByTenant(int $tenantId): array
    {
        $stmt = self::db()->prepare(
            "SELECT * FROM invoice_types 
             WHERE tenant_id = ? 
             ORDER BY urutan ASC, nama ASC"
        );
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    public static function activeByTenant(int $tenantId): array
    {
        $stmt = self::db()->prepare(
            "SELECT * FROM invoice_types 
             WHERE tenant_id = ? AND status = 'active'
             ORDER BY urutan ASC, nama ASC"
        );
        $stmt->execute([$tenantId]);
        return $stmt->fetchAll();
    }

    public static function findByIdAndTenant(int $id, int $tenantId): ?array
    {
        $stmt = self::db()->prepare(
            "SELECT * FROM invoice_types WHERE id = ? AND tenant_id = ? LIMIT 1"
        );
        $stmt->execute([$id, $tenantId]);
        return $stmt->fetch() ?: null;
    }

    public static function namaExists(int $tenantId, string $nama, ?int $exceptId = null): bool
    {
        if ($exceptId) {
            $stmt = self::db()->prepare(
                "SELECT id FROM invoice_types 
                 WHERE tenant_id = ? AND nama = ? AND id != ? LIMIT 1"
            );
            $stmt->execute([$tenantId, $nama, $exceptId]);
        } else {
            $stmt = self::db()->prepare(
                "SELECT id FROM invoice_types WHERE tenant_id = ? AND nama = ? LIMIT 1"
            );
            $stmt->execute([$tenantId, $nama]);
        }
        return (bool) $stmt->fetch();
    }

    public static function create(int $tenantId, array $data): int
    {
        $stmt = self::db()->prepare(
            "INSERT INTO invoice_types (tenant_id, nama, urutan, status)
             VALUES (?, ?, ?, 'active')"
        );
        $stmt->execute([
            $tenantId,
            $data['nama'],
            $data['urutan'] ?? 0,
        ]);
        return (int) self::db()->lastInsertId();
    }

    public static function update(int $id, int $tenantId, array $data): void
    {
        $stmt = self::db()->prepare(
            "UPDATE invoice_types SET nama = ?, urutan = ? WHERE id = ? AND tenant_id = ?"
        );
        $stmt->execute([
            $data['nama'],
            $data['urutan'] ?? 0,
            $id,
            $tenantId,
        ]);
    }

    public static function toggleStatus(int $id, int $tenantId): void
    {
        $stmt = self::db()->prepare(
            "UPDATE invoice_types 
             SET status = IF(status = 'active', 'inactive', 'active')
             WHERE id = ? AND tenant_id = ?"
        );
        $stmt->execute([$id, $tenantId]);
    }

    public static function delete(int $id, int $tenantId): void
    {
        $stmt = self::db()->prepare(
            "DELETE FROM invoice_types WHERE id = ? AND tenant_id = ?"
        );
        $stmt->execute([$id, $tenantId]);
    }
}