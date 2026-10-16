<?php
declare(strict_types=1);

namespace App\Models;

class Tenant extends BaseModel
{
    protected static string $table = 'tenants';

    public static function allWithStats(string $search = '', string $status = '', int $page = 1, int $perPage = 10): array
{
    $offset = ($page - 1) * $perPage;

    $sql = "SELECT t.*,
                (SELECT COUNT(*) FROM payers p WHERE p.tenant_id = t.id) AS total_payers,
                (SELECT COUNT(*) FROM invoices i WHERE i.tenant_id = t.id) AS total_invoices
            FROM tenants t
            WHERE 1=1";
    $params = [];

    if ($search !== '') {
        $sql .= " AND (t.nama LIKE ? OR t.slug LIKE ?)";
        $like = '%' . $search . '%';
        $params[] = $like;
        $params[] = $like;
    }

    if ($status !== '') {
        $sql .= " AND t.status = ?";
        $params[] = $status;
    }

    $sql .= " ORDER BY t.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $perPage;
    $params[] = $offset;

    $stmt = self::db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

public static function countFiltered(string $search = '', string $status = ''): int
{
    $sql = "SELECT COUNT(*) FROM tenants t WHERE 1=1";
    $params = [];

    if ($search !== '') {
        $sql .= " AND (t.nama LIKE ? OR t.slug LIKE ?)";
        $like = '%' . $search . '%';
        $params[] = $like;
        $params[] = $like;
    }

    if ($status !== '') {
        $sql .= " AND t.status = ?";
        $params[] = $status;
    }

    $stmt = self::db()->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

    public static function findBySlug(string $slug): ?array
    {
        $stmt = self::db()->prepare("SELECT * FROM tenants WHERE slug = ? LIMIT 1");
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function slugExists(string $slug, ?int $exceptId = null): bool
    {
        if ($exceptId) {
            $stmt = self::db()->prepare("SELECT id FROM tenants WHERE slug = ? AND id != ? LIMIT 1");
            $stmt->execute([$slug, $exceptId]);
        } else {
            $stmt = self::db()->prepare("SELECT id FROM tenants WHERE slug = ? LIMIT 1");
            $stmt->execute([$slug]);
        }
        return (bool) $stmt->fetch();
    }

    public static function create(array $data): int
    {
        $stmt = self::db()->prepare(
            "INSERT INTO tenants (nama, slug, alamat, telepon, email, status)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['nama'],
            $data['slug'],
            $data['alamat']  ?? null,
            $data['telepon'] ?? null,
            $data['email']   ?? null,
            $data['status']  ?? 'active',
        ]);
        return (int) self::db()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $stmt = self::db()->prepare(
            "UPDATE tenants
             SET nama = ?, slug = ?, alamat = ?, telepon = ?, email = ?
             WHERE id = ?"
        );
        $stmt->execute([
            $data['nama'],
            $data['slug'],
            $data['alamat']  ?? null,
            $data['telepon'] ?? null,
            $data['email']   ?? null,
            $id,
        ]);
    }

    public static function toggleStatus(int $id): void
    {
        $stmt = self::db()->prepare(
            "UPDATE tenants
             SET status = IF(status = 'active', 'suspended', 'active')
             WHERE id = ?"
        );
        $stmt->execute([$id]);
    }

    public static function generateSlug(string $nama): string
    {
        $slug = strtolower(trim($nama));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug;
    }

    public static function allActive(): array
    {
        $stmt = self::db()->query(
            "SELECT id, nama, slug FROM tenants WHERE status = 'active' ORDER BY nama ASC"
        );
        return $stmt->fetchAll();
    }

    /**
     * Update profil tenant (oleh admin tenant sendiri).
     */
    public static function updateProfile(int $id, array $data): void
    {
        $stmt = self::db()->prepare(
            "UPDATE tenants
            SET nama = ?, alamat = ?, telepon = ?, email = ?, whatsapp = ?
            WHERE id = ?"
        );
        $stmt->execute([
            $data['nama'],
            $data['alamat']   ?? null,
            $data['telepon']  ?? null,
            $data['email']    ?? null,
            $data['whatsapp'] ?? null,
            $id,
        ]);
    }

    /**
     * Cek apakah tenant memiliki langganan aktif.
     */
    public static function isSubscriptionActive(int $tenantId): bool
    {
        $stmt = self::db()->prepare(
            "SELECT id FROM tenant_subscriptions
            WHERE tenant_id = ?
            AND status = 'active'
            AND end_date >= CURDATE()
            LIMIT 1"
        );
        $stmt->execute([$tenantId]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Ambil detail langganan aktif tenant.
     */
    public static function getActiveSubscription(int $tenantId): ?array
    {
        $stmt = self::db()->prepare(
            "SELECT * FROM tenant_subscriptions
            WHERE tenant_id = ?
            AND status = 'active'
            AND end_date >= CURDATE()
            ORDER BY end_date DESC
            LIMIT 1"
        );
        $stmt->execute([$tenantId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Ambil langganan terakhir (termasuk yang sudah expired).
     */
    public static function getLatestSubscription(int $tenantId): ?array
    {
        $stmt = self::db()->prepare(
            "SELECT * FROM tenant_subscriptions
            WHERE tenant_id = ?
            ORDER BY end_date DESC
            LIMIT 1"
        );
        $stmt->execute([$tenantId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Hitung sisa hari langganan aktif.
     * Return null kalau tidak ada langganan aktif.
     */
    public static function getSubscriptionDaysLeft(int $tenantId): ?int
    {
        $sub = self::getActiveSubscription($tenantId);
        if (!$sub) {
            return null;
        }

        $endDate = new \DateTimeImmutable($sub['end_date']);
        $today   = new \DateTimeImmutable('today');
        $diff    = $today->diff($endDate);

        return (int) $diff->format('%r%a');
    }

    /**
     * Status langganan untuk display:
     *   'active'   — masih aktif, > grace days
     *   'grace'    — expired tapi masih dalam masa tenggang
     *   'expired'  — sudah lewat grace, akses dibatasi
     *   'none'     — belum pernah berlangganan
     */
    public static function getSubscriptionStatus(int $tenantId): array
    {
        $sub = self::getLatestSubscription($tenantId);

        if (!$sub) {
            return ['status' => 'none', 'days_left' => null, 'subscription' => null];
        }

        $endDate = new \DateTimeImmutable($sub['end_date']);
        $today   = new \DateTimeImmutable('today');
        $diff    = $today->diff($endDate);
        $daysLeft = (int) $diff->format('%r%a');

        $graceDays = (int) (\App\Models\PlatformSetting::get('subscription_grace_days') ?? 7);

        if ($daysLeft >= 0) {
            $status = 'active';
        } elseif ($daysLeft >= -$graceDays) {
            $status = 'grace';
        } else {
            $status = 'expired';
        }

        return [
            'status'       => $status,
            'days_left'    => $daysLeft,
            'subscription' => $sub,
        ];
    }

    /**
     * Perpanjang langganan tenant.
     */
    public static function extendSubscription(
        int $tenantId,
        string $planName,
        float $price,
        int $months,
        int $createdBy
    ): int {
        $db = self::db();

        // Ambil end_date terakhir (kalau masih aktif, extend dari situ)
        $latest = self::getLatestSubscription($tenantId);

        if ($latest && $latest['status'] === 'active' && new \DateTimeImmutable($latest['end_date']) >= new \DateTimeImmutable('today')) {
            $startDate = new \DateTimeImmutable($latest['end_date']);
        } else {
            $startDate = new \DateTimeImmutable('today');
        }

        $endDate = $startDate->modify("+{$months} months");

        $stmt = $db->prepare(
            "INSERT INTO tenant_subscriptions
            (tenant_id, plan_name, price, period_months, start_date, end_date, status)
            VALUES (?, ?, ?, ?, ?, ?, 'active')"
        );
        $stmt->execute([
            $tenantId,
            $planName,
            $price,
            $months,
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d'),
        ]);

        return (int) $db->lastInsertId();
    }
}