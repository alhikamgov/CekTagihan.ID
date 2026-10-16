<?php
declare(strict_types=1);

namespace App\Models;

class Payer extends BaseModel
{
    protected static string $table = 'payers';

    public static function allByTenant(int $tenantId, string $search = '', string $status = '', int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT * FROM payers WHERE tenant_id = ?";
        $params = [$tenantId];

        if ($search !== '') {
            $sql .= " AND (nomor_induk LIKE ? OR nama LIKE ? OR whatsapp LIKE ?)";
            $like = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        if ($status !== '') {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        // Urutan: terbaru (id besar) di atas
        $sql .= " ORDER BY id DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function countByTenantFiltered(int $tenantId, string $search = '', string $status = ''): int
    {
        $sql = "SELECT COUNT(*) FROM payers WHERE tenant_id = ?";
        $params = [$tenantId];

        if ($search !== '') {
            $sql .= " AND (nomor_induk LIKE ? OR nama LIKE ? OR whatsapp LIKE ?)";
            $like = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        if ($status !== '') {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public static function findByIdAndTenant(int $id, int $tenantId): ?array
    {
        $stmt = self::db()->prepare(
            "SELECT * FROM payers WHERE id = ? AND tenant_id = ? LIMIT 1"
        );
        $stmt->execute([$id, $tenantId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function nomorIndukExists(int $tenantId, string $nomorInduk, ?int $exceptId = null): bool
    {
        if ($exceptId) {
            $stmt = self::db()->prepare(
                "SELECT id FROM payers WHERE tenant_id = ? AND nomor_induk = ? AND id != ? LIMIT 1"
            );
            $stmt->execute([$tenantId, $nomorInduk, $exceptId]);
        } else {
            $stmt = self::db()->prepare(
                "SELECT id FROM payers WHERE tenant_id = ? AND nomor_induk = ? LIMIT 1"
            );
            $stmt->execute([$tenantId, $nomorInduk]);
        }
        return (bool) $stmt->fetch();
    }

    public static function create(int $tenantId, array $data): int
    {
        $stmt = self::db()->prepare(
            "INSERT INTO payers (tenant_id, nomor_induk, nama, whatsapp, status)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $tenantId,
            $data['nomor_induk'],
            $data['nama'],
            $data['whatsapp'] ?? null,
            $data['status']   ?? 'active',
        ]);
        return (int) self::db()->lastInsertId();
    }

    public static function update(int $id, int $tenantId, array $data): void
    {
        $stmt = self::db()->prepare(
            "UPDATE payers
             SET nomor_induk = ?, nama = ?, whatsapp = ?
             WHERE id = ? AND tenant_id = ?"
        );
        $stmt->execute([
            $data['nomor_induk'],
            $data['nama'],
            $data['whatsapp'] ?? null,
            $id,
            $tenantId,
        ]);
    }

    public static function toggleStatus(int $id, int $tenantId): void
    {
        $stmt = self::db()->prepare(
            "UPDATE payers
             SET status = IF(status = 'active', 'inactive', 'active')
             WHERE id = ? AND tenant_id = ?"
        );
        $stmt->execute([$id, $tenantId]);
    }

    public static function countByTenant(int $tenantId): int
    {
        $stmt = self::db()->prepare("SELECT COUNT(*) FROM payers WHERE tenant_id = ?");
        $stmt->execute([$tenantId]);
        return (int) $stmt->fetchColumn();
    }
}