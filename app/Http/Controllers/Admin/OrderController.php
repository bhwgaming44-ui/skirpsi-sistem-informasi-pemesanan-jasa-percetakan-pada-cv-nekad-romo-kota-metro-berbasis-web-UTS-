<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderStatusService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $query = Order::query()
            ->with(['user', 'transaksiTerakhir'])
            ->withCount('detail')
            ->latest('tgl_pesan');

        if ($request->filled('q')) {
            $search = $request->string('q')->trim()->toString();
            $query->where(function ($builder) use ($search): void {
                $builder->where('kode_pesanan', 'like', '%'.$search.'%')
                    ->orWhereHas('user', fn ($user) => $user->where('nama', 'like', '%'.$search.'%'));
            });
        }

        if ($request->filled('status')) {
            $query->where('status_pesanan', $request->string('status')->toString());
        }

        if ($request->filled('pembayaran')) {
            $query->where('status_pembayaran', $request->string('pembayaran')->toString());
        }

        if ($request->filled('dari')) {
            $query->whereDate('tgl_pesan', '>=', $request->date('dari'));
        }

        if ($request->filled('sampai')) {
            $query->whereDate('tgl_pesan', '<=', $request->date('sampai'));
        }

        return view('admin.orders.index', [
            'orders' => $query->paginate(12)->withQueryString(),
            'statuses' => [...Order::TAHAPAN, Order::STATUS_BATAL],
        ]);
    }

    public function show(Order $order): View
    {
        $order->load(['user', 'detail.finishing', 'transaksi', 'riwayat.user']);

        return view('admin.orders.show', [
            'order' => $order,
            'statuses' => [...Order::TAHAPAN, Order::STATUS_BATAL],
        ]);
    }

    public function updateStatus(Request $request, Order $order, OrderStatusService $statuses): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([...Order::TAHAPAN, Order::STATUS_BATAL])],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validated['status'] !== Order::STATUS_BATAL && ! $order->isPaid()) {
            return back()->with('error', 'Pesanan belum lunas. Status produksi hanya dapat diperbarui setelah pembayaran terverifikasi.');
        }

        if ($order->status_pesanan === Order::STATUS_BATAL) {
            return back()->with('error', 'Pesanan yang sudah dibatalkan tidak dapat diubah statusnya.');
        }

        $statuses->update($order, $validated['status'], auth()->id(), $validated['keterangan'] ?? null);

        return back()->with('success', 'Status pesanan diperbarui menjadi '.$validated['status'].'.');
    }

    public function advance(Order $order, OrderStatusService $statuses): RedirectResponse
    {
        if (! $order->isPaid()) {
            return back()->with('error', 'Pesanan belum lunas, status produksi belum bisa dilanjutkan.');
        }

        $next = $order->nextStatus();

        if ($next === null) {
            return back()->with('error', 'Pesanan sudah berada di tahap akhir.');
        }

        $statuses->advance($order, auth()->id());

        return back()->with('success', 'Pesanan dilanjutkan ke tahap: '.$next.'.');
    }

    public function board(): View
    {
        $columns = [
            Order::STATUS_ANTREAN => [],
            Order::STATUS_CETAK => [],
            Order::STATUS_SELESAI_CETAK => [],
            Order::STATUS_SIAP => [],
        ];

        $orders = Order::query()
            ->with(['user'])
            ->withCount('detail')
            ->whereIn('status_pesanan', array_keys($columns))
            ->orderBy('tgl_pesan')
            ->get();

        foreach ($orders as $order) {
            $columns[$order->status_pesanan][] = $order;
        }

        return view('admin.production.board', [
            'columns' => $columns,
        ]);
    }

    public function invoice(Order $order): View
    {
        $order->load(['detail.finishing', 'user', 'transaksiTerakhir']);

        return view('invoices.print', [
            'order' => $order,
            'backUrl' => route('admin.orders.show', $order),
        ]);
    }
}
