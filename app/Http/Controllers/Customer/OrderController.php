<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderStatusService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $query = Order::query()
            ->where('id_user', auth()->id())
            ->withCount('detail')
            ->latest('tgl_pesan');

        if ($request->filled('status') && in_array($request->string('status')->toString(), Order::TAHAPAN, true)) {
            $query->where('status_pesanan', $request->string('status')->toString());
        }

        if ($request->filled('q')) {
            $search = $request->string('q')->trim()->toString();
            $query->where('kode_pesanan', 'like', '%'.$search.'%');
        }

        return view('customer.orders.index', [
            'orders' => $query->paginate(8)->withQueryString(),
            'statuses' => Order::TAHAPAN,
        ]);
    }

    public function show(Order $order): View
    {
        $this->authorizeOrder($order);

        $order->load(['detail.finishing', 'riwayat.user', 'transaksiTerakhir']);

        return view('customer.orders.show', [
            'order' => $order,
            'statuses' => Order::TAHAPAN,
        ]);
    }

    public function invoice(Order $order): View
    {
        $this->authorizeOrder($order);

        $order->load(['detail.finishing', 'user', 'transaksiTerakhir']);

        return view('invoices.print', [
            'order' => $order,
            'backUrl' => route('customer.orders.show', $order),
        ]);
    }

    public function cancel(Order $order, OrderStatusService $statuses): RedirectResponse
    {
        $this->authorizeOrder($order);

        if (! $order->canBeCancelledByCustomer()) {
            return back()->with('error', 'Pesanan sudah diproses dan tidak dapat dibatalkan sendiri. Hubungi admin untuk bantuan.');
        }

        $statuses->cancel($order, auth()->id(), 'Pesanan dibatalkan oleh pelanggan sebelum pembayaran.');

        return redirect()
            ->route('customer.orders.show', $order)
            ->with('success', 'Pesanan berhasil dibatalkan.');
    }

    private function authorizeOrder(Order $order): void
    {
        abort_unless($order->id_user === auth()->id(), 403, 'Pesanan ini bukan milik akun Anda.');
    }
}
