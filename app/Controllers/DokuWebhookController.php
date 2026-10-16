<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Payment;
use App\Services\DokuService;

class DokuWebhookController
{
    public function handle(): void
    {
        $rawBody = file_get_contents('php://input');
        $headers = DokuService::headersFromServer($_SERVER);

        $requestId = $headers['request_id'] ?? null;
        $orderId   = null;
        $signatureValid = 0;

        // Log awal (sebelum verifikasi)
        $logId = $this->insertLog([
            'request_id'      => $requestId,
            'order_id'        => null,
            'signature_valid' => 0,
            'result'          => 'received',
            'http_status'     => null,
            'payload'         => $rawBody,
            'note'            => null,
        ]);

        try {
            $doku = new DokuService();
        } catch (\Throwable $e) {
            $this->updateLog($logId, ['result' => 'config_error', 'http_status' => 500, 'note' => $e->getMessage()]);
            http_response_code(500);
            echo json_encode(['message' => 'Config error']);
            return;
        }

        // Verifikasi signature
        if (!$doku->verifyNotification($headers, $rawBody)) {
            $this->updateLog($logId, ['result' => 'invalid_signature', 'http_status' => 401]);
            http_response_code(401);
            echo json_encode(['message' => 'Invalid signature']);
            return;
        }

        $data = json_decode($rawBody, true);
        if (!is_array($data)) {
            $this->updateLog($logId, ['signature_valid' => 1, 'result' => 'invalid_json', 'http_status' => 400]);
            http_response_code(400);
            echo json_encode(['message' => 'Invalid JSON']);
            return;
        }

        $orderId = $data['order']['invoice_number'] ?? null;
        $status  = $data['transaction']['status']      ?? null;
        $method  = $data['channel']['id']              ?? null;
        $paidAmount = $data['order']['amount']         ?? null;

        if (!$orderId) {
            $this->updateLog($logId, ['signature_valid' => 1, 'result' => 'missing_order_id', 'http_status' => 400]);
            http_response_code(400);
            echo json_encode(['message' => 'Missing invoice_number']);
            return;
        }

        $payment = Payment::findByOrderId($orderId);
        if (!$payment) {
            $this->updateLog($logId, ['signature_valid' => 1, 'order_id' => $orderId, 'result' => 'payment_not_found', 'http_status' => 404]);
            http_response_code(404);
            echo json_encode(['message' => 'Payment not found']);
            return;
        }

        if ($payment['status'] === 'settlement') {
            $this->updateLog($logId, ['signature_valid' => 1, 'order_id' => $orderId, 'result' => 'already_settled', 'http_status' => 200]);
            echo json_encode(['message' => 'Already settled']);
            return;
        }

        if ($status === 'SUCCESS') {
            $this->markAsSettled((int) $payment['id'], $orderId, $method, $paidAmount);
            $this->updateLog($logId, ['signature_valid' => 1, 'order_id' => $orderId, 'result' => 'settled', 'http_status' => 200]);
            echo json_encode(['message' => 'OK']);
        } else {
            $this->updateLog($logId, ['signature_valid' => 1, 'order_id' => $orderId, 'result' => 'ignored:' . $status, 'http_status' => 200]);
            echo json_encode(['message' => 'Ignored status: ' . $status]);
        }
    }

    /**
     * Tandai payment sebagai settlement.
     * Model SEWA: tenant_amount = gross_amount (tidak ada potongan platform).
     * Fee gateway (QRIS, VA, dll) nanti dihitung dari DOKU saat settlement report.
     */
    private function markAsSettled(int $paymentId, string $orderId, ?string $method, $paidAmount): void
    {
        $db = Payment::db();
        $db->beginTransaction();
        try {
            // Ambil gross_amount untuk hitung tenant_amount
            $stmt = $db->prepare("SELECT gross_amount FROM payments WHERE id = ? LIMIT 1");
            $stmt->execute([$paymentId]);
            $grossAmount = (float) $stmt->fetchColumn();

            $stmt = $db->prepare(
                "UPDATE payments
                SET status = 'settlement',
                    payment_method = COALESCE(?, payment_method),
                    paid_at = NOW(),
                    platform_fee = 0,
                    tenant_amount = ?,
                    updated_at = NOW()
                WHERE id = ?"
            );
            $stmt->execute([$method, $grossAmount, $paymentId]);

            // Ambil invoice terkait
            $stmt = $db->prepare("SELECT invoice_id FROM payment_invoices WHERE payment_id = ?");
            $stmt->execute([$paymentId]);
            $invoiceIds = $stmt->fetchAll(\PDO::FETCH_COLUMN);

            if (!empty($invoiceIds)) {
                $placeholders = implode(',', array_fill(0, count($invoiceIds), '?'));
                $stmt = $db->prepare(
                    "UPDATE invoices
                    SET paid_amount = jumlah,
                        status = 'paid',
                        paid_at = NOW(),
                        updated_at = NOW()
                    WHERE id IN ($placeholders)"
                );
                $stmt->execute($invoiceIds);
            }

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }


    private function insertLog(array $data): int
    {
        $db = Payment::db();
        $stmt = $db->prepare(
            "INSERT INTO doku_notification_logs
             (request_id, order_id, signature_valid, result, http_status, payload, note, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
        );
        $stmt->execute([
            $data['request_id'],
            $data['order_id'],
            $data['signature_valid'],
            $data['result'],
            $data['http_status'],
            $data['payload'],
            $data['note'],
        ]);
        return (int) $db->lastInsertId();
    }

    private function updateLog(int $logId, array $updates): void
    {
        if (empty($updates)) return;
        $db = Payment::db();
        $fields = [];
        $values = [];
        foreach ($updates as $k => $v) {
            $fields[] = "$k = ?";
            $values[] = $v;
        }
        $values[] = $logId;
        $sql = "UPDATE doku_notification_logs SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($values);
    }
}