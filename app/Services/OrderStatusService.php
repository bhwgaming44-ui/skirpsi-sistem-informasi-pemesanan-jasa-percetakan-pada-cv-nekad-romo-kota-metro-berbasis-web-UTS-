<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderHistory;

class OrderStatusService
{
    public function advance(Order $order, ?int $userId = null, ?string $note = null): Order
    {
        $next = $order->nextStatus();

        if ($next === null) {
            return $order;
        }

        return $this->update($order, $next, $userId, $note);
    }

    public function update(Order $order, string $status, ?int $userId = null, ?string $note = null): Order
    {
        $order->status_pesanan = $status;

        if ($status === Order::STATUS_BATAL) {
            $order->status_pembayaran = $order->isPaid() ? $order->status_pembayaran : 'Gagal';
        }

        $order->save();

        OrderHistory::query()->create([
            'id_pesanan' => $order->id_pesanan,
            'id_user' => $userId,
            'status_tahapan' => $status,
            'keterangan' => $note ?? $this->defaultNote($status),
        ]);

        return $order;
    }

    public function cancel(Order $order, ?int $userId = null, ?string $note = null): Order
    {
        return $this->update($order, Order::STATUS_BATAL, $userId, $note ?? 'Pesanan dibatalkan.');
    }

    private function defaultNote(string $status): string
    {
        return match ($status) {
            Order::STATUS_ANTREAN => 'Pembayaran terverifikasi. Pesanan masuk antrean produksi workshop.',
            Order::STATUS_CETAK => 'Operator mulai mencetak pesanan di workshop CV Nekad Romo.',
            Order::STATUS_SELESAI_CETAK => 'Proses cetak selesai dan masuk tahap quality control / finishing.',
            Order::STATUS_SIAP => 'Pesanan siap diambil di toko atau diserahkan ke kurir.',
            Order::STATUS_SELESAI => 'Pesanan telah diterima pelanggan. Terima kasih!',
            default => 'Status pesanan diperbarui menjadi '.$status.'.',
        };
    }
}
