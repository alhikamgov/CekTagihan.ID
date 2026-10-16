<?php
declare(strict_types=1);

namespace App\Models;

class TenantBankAccount extends BaseModel
{
    protected static string $table = 'tenant_bank_accounts';

    public static function findByTenant(int $tenantId): ?array
    {
        $stmt = self::db()->prepare(
            "SELECT * FROM tenant_bank_accounts WHERE tenant_id = ? LIMIT 1"
        );
        $stmt->execute([$tenantId]);
        return $stmt->fetch() ?: null;
    }

    public static function upsert(int $tenantId, array $data): int
    {
        $existing = self::findByTenant($tenantId);

        if ($existing) {
            $stmt = self::db()->prepare(
                "UPDATE tenant_bank_accounts
                 SET bank_nama = ?, bank_kode = ?, rekening_nomor = ?, rekening_pemilik = ?
                 WHERE tenant_id = ?"
            );
            $stmt->execute([
                $data['bank_nama'],
                $data['bank_kode'],
                $data['rekening_nomor'],
                $data['rekening_pemilik'],
                $tenantId,
            ]);
            return (int) $existing['id'];
        }

        $stmt = self::db()->prepare(
            "INSERT INTO tenant_bank_accounts
                (tenant_id, bank_nama, bank_kode, rekening_nomor, rekening_pemilik, status)
             VALUES (?, ?, ?, ?, ?, 'pending')"
        );
        $stmt->execute([
            $tenantId,
            $data['bank_nama'],
            $data['bank_kode'],
            $data['rekening_nomor'],
            $data['rekening_pemilik'],
        ]);
        return (int) self::db()->lastInsertId();
    }

    public static function updateXenditId(int $tenantId, string $subAccountId): void
    {
        $stmt = self::db()->prepare(
            "UPDATE tenant_bank_accounts
             SET xendit_sub_account_id = ?, status = 'verified'
             WHERE tenant_id = ?"
        );
        $stmt->execute([$subAccountId, $tenantId]);
    }

    public static function updateStatus(int $tenantId, string $status): void
    {
        $stmt = self::db()->prepare(
            "UPDATE tenant_bank_accounts SET status = ? WHERE tenant_id = ?"
        );
        $stmt->execute([$status, $tenantId]);
    }
}