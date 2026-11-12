<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\CartService;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly CartService $cart) {}

    public function __invoke(): View
    {
        $user = auth()->user();

        $orders = Order::query()->where('id_user', $user->id_user);
        $totalOrders = (clone $orders)->count();
        $activeOrders = (clone $orders)
            ->whereNotIn('status_pesanan', [Order::STATUS_SELESAI, Order::STATUS_BATAL])
            ->count();
        $totalSpent = (int) (clone $orders)->where('status_pembayaran', 'Lunas')->sum('grand_total');
        $waitingPayment = (clone $orders)->where('status_pesanan', Order::STATUS_MENUNGGU)->count();

        $activeOrder = (clone $orders)
            ->with(['detail.finishing', 'riwayat', 'transaksiTerakhir'])
            ->whereNotIn('status_pesanan', [Order::STATUS_SELESAI, Order::STATUS_BATAL])
            ->latest('tgl_pesan')
            ->first();

        $recentOrders = (clone $orders)
            ->latest('tgl_pesan')
            ->limit(5)
            ->get();

        $paidOrders = (clone $orders)
            ->where('status_pembayaran', 'Lunas')
            ->where('tgl_pesan', '>=', now()->startOfMonth()->subMonths(5))
            ->get(['grand_total', 'tgl_pesan']);

        $spendLabels = [];
        $spendValues = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->startOfMonth()->subMonths($i);
            $spendLabels[] = $month->locale('id')->translatedFormat('M');
            $spendValues[] = (int) $paidOrders
                ->filter(fn (Order $order) => $order->tgl_pesan->format('Y-m') === $month->format('Y-m'))
                ->sum('grand_total');
        }

        $statusCounts = Order::query()
            ->where('id_user', $user->id_user)
            ->selectRaw('status_pesanan, COUNT(*) as total')
            ->groupBy('status_pesanan')
            ->pluck('total', 'status_pesanan');

        return view('customer.dashboard', [
            'totalOrders' => $totalOrders,
            'activeOrders' => $activeOrders,
            'totalSpent' => $totalSpent,
            'waitingPayment' => $waitingPayment,
            'activeOrder' => $activeOrder,
            'recentOrders' => $recentOrders,
            'spendLabels' => $spendLabels,
            'spendValues' => $spendValues,
            'statusCounts' => $statusCounts,
            'cartCount' => $this->cart->count(),
            'cartSubtotal' => $this->cart->subtotal(),
            'hasCheckoutDraft' => $this->cart->checkoutDraft() !== [],
        ]);
    }
}
