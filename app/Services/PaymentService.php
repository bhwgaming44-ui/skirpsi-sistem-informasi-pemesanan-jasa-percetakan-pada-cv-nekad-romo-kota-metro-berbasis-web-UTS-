<?php

namespace App\Services;

use App\Models\GatewayTransaction;
use App\Models\Order;
use App\Models\OrderHistory;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function markPaid(Order $order, array $transactionData = [], ?int $userId = null): Order
    {
        return DB::transaction(function () use ($order, $transactionData, $userId): Order {
            $locked = Order::query()->lockForUpdate()->findOrFail($order->id_pesanan);

            if ($locked->isPaid()) {
                return $locked;
            }

            $locked->status_pembayaran = 'Lunas';
            $locked->status_pesanan = Order::STATUS_ANTREAN;
            $locked->save();

            OrderHistory::query()->create([
                'id_pesanan' => $locked->id_pesanan,
                'id_user' => $userId,
                'status_tahapan' => 'Pembayaran Lunas',
                'keterangan' => $transactionData['keterangan']
                    ?? 'Pembayaran berhasil diverifikasi otomatis oleh payment gateway. Pesanan masuk antrean cetak.',
            ]);

            return $locked;
        });
    }

    public function markFailed(Order $order, string $status = 'Gagal', string $note = 'Pembayaran gagal atau kadaluarsa.'): void
    {
        DB::transaction(function () use ($order, $status, $note): void {
            $locked = Order::query()->lockForUpdate()->findOrFail($order->id_pesanan);

            if ($locked->isPaid()) {
                return;
            }

            $locked->status_pembayaran = $status;
            $locked->save();

            OrderHistory::query()->create([
                'id_pesanan' => $locked->id_pesanan,
                'status_tahapan' => 'Pembayaran '.$status,
                'keterangan' => $note,
            ]);
        });
    }

    public function markReversed(Order $order, string $status = 'Dikembalikan', string $note = 'Dana pembayaran dikembalikan oleh payment gateway.'): void
    {
        DB::transaction(function () use ($order, $status, $note): void {
            $locked = Order::query()->lockForUpdate()->findOrFail($order->id_pesanan);

            if ($locked->status_pembayaran === $status) {
                return;
            }

            $locked->status_pembayaran = $status;
            $locked->save();

            OrderHistory::query()->create([
                'id_pesanan' => $locked->id_pesanan,
                'status_tahapan' => 'Pembayaran '.$status,
                'keterangan' => $note,
            ]);
        });
    }

    public function demoTransaction(Order $order, bool $success): GatewayTransaction
    {
        $transaction = GatewayTransaction::query()->updateOrCreate(
            ['order_id_gateway' => $order->kode_pesanan],
            [
                'id_pesanan' => $order->id_pesanan,
                'transaction_id_gateway' => 'DEMO-'.strtoupper(uniqid()),
                'payment_type' => 'qris_demo',
                'gross_amount' => $order->grand_total,
                'transaction_status' => $success ? 'settlement' : 'deny',
                'fraud_status' => 'accept',
                'payment_time' => $success ? now() : null,
                'settlement_time' => $success ? now() : null,
                'raw_response' => json_encode([
                    'mode' => 'demo',
                    'status' => $success ? 'settlement' : 'deny',
                    'order_id' => $order->kode_pesanan,
                    'gross_amount' => (float) $order->grand_total,
                ], JSON_PRETTY_PRINT),
            ]
        );

        if ($success) {
            $this->markPaid($order, [
                'keterangan' => 'Pembayaran simulasi QRIS berhasil diverifikasi. Pesanan masuk antrean cetak.',
            ]);
        } else {
            $this->markFailed($order, 'Gagal', 'Simulasi pembayaran gagal. Silakan coba lagi.');
        }

        return $transaction;
    }
}
