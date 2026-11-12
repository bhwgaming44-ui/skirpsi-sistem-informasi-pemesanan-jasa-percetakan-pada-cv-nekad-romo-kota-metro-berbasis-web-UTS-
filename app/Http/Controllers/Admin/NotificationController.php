<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $count = Order::query()->where('status_pesanan', Order::STATUS_ANTREAN)->count();

        $latest = Order::query()
            ->with('user')
            ->where('status_pesanan', Order::STATUS_ANTREAN)
            ->latest('updated_at')
            ->limit(4)
            ->get()
            ->map(fn (Order $order) => [
                'kode' => $order->kode_pesanan,
                'nama' => $order->user->nama,
                'total' => (int) $order->grand_total,
                'url' => route('admin.orders.show', $order),
            ]);

        return response()->json([
            'count' => $count,
            'latest' => $latest,
        ]);
    }
}
