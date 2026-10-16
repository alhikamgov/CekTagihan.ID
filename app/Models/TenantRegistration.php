<?php
declare(strict_types=1);

namespace App\Models;

class TenantRegistration extends BaseModel
{
    protected static string $table = 'tenant_registrations';

    public static function allByStatus(string $status = '', int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT * FROM tenant_registrations WHERE 1=1";
        $params = [];

        if ($status !== '') {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY 
                    CASE status 
                        WHEN 'pending' THEN 1
                        WHEN 'approved' THEN 2
                        WHEN 'rejected' THEN 3
                    END,
                    created_at DESC
                  LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        $stmt = self::db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function countByStatus(string $status = ''): int
    {
        if ($status !== '') {
            $stmt = self::db()->prepare("SELECT COUNT(*) FROM tenant_registrations WHERE status = ?");
            $stmt->execute([$status]);
        } else {
            $stmt = self::db()->query("SELECT COUNT(*) FROM tenant_registrations");
        }
        return (int) $stmt->fetchColumn();
    }

    public static function countPending(): int
    {
        return self::countByStatus('pending');
    }

    public static function create(array $data): int
    {
        $stmt = self::db()->prepare(
            "INSERT INTO tenant_registrations
                (nama_pic, email, telepon, nama_bisnis, jenis_bisnis, jumlah_unit, alamat, kota, catatan, ip_address)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['nama_pic'],
            $data['email'],
            $data['telepon'],
            $data['nama_bisnis'],
            $data['jenis_bisnis'],
            $data['jumlah_unit'] ?? 0,
            $data['alamat'] ?? null,
            $data['kota'] ?? null,
            $data['catatan'] ?? null,
            $data['ip_address'] ?? null,
        ]);
        return (int) self::db()->lastInsertId();
    }

    public static function findByEmail(string $email): ?array
    {
        $stmt = self::db()->prepare(
            "SELECT * FROM tenant_registrations 
             WHERE email = ? AND status IN ('pending', 'approved')
             ORDER BY created_at DESC LIMIT 1"
        );
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public static function approve(int $id, int $approvedBy, int $tenantId): void
    {
        $stmt = self::db()->prepare(
            "UPDATE tenant_registrations
             SET status = 'approved', approved_at = NOW(), approved_by = ?, tenant_id = ?
             WHERE id = ?"
        );
        $stmt->execute([$approvedBy, $tenantId, $id]);
    }

    public static function reject(int $id, string $reason, int $approvedBy): void
    {
        $stmt = self::db()->prepare(
            "UPDATE tenant_registrations
             SET status = 'rejected', reject_reason = ?, approved_by = ?, approved_at = NOW()
             WHERE id = ?"
        );
        $stmt->execute([$reason, $approvedBy, $id]);
    }
}