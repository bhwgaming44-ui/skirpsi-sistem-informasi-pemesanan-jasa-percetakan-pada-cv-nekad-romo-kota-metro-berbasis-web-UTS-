<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * @return array<string, int|float>
     */
    public function metrics(CarbonInterface $from, CarbonInterface $to): array
    {
        $paid = Order::query()
            ->where('status_pembayaran', 'Lunas')
            ->whereBetween('tgl_pesan', [$from, $to]);

        $revenue = (int) (clone $paid)->sum('grand_total');
        $orderCount = (clone $paid)->count();
        $customerCount = (int) (clone $paid)->distinct('id_user')->count('id_user');
        $average = $orderCount > 0 ? (int) round($revenue / $orderCount) : 0;

        $production = Order::query()
            ->whereIn('status_pesanan', [
                Order::STATUS_ANTREAN,
                Order::STATUS_CETAK,
                Order::STATUS_SELESAI_CETAK,
                Order::STATUS_SIAP,
            ])
            ->count();

        $waiting = Order::query()->where('status_pesanan', Order::STATUS_MENUNGGU)->count();
        $completed = Order::query()->where('status_pesanan', Order::STATUS_SELESAI)->count();
        $allPaid = Order::query()->where('status_pembayaran', 'Lunas')->count();
        $completionRate = $allPaid > 0 ? (int) round($completed / $allPaid * 100) : 0;

        return [
            'revenue' => $revenue,
            'orders' => $orderCount,
            'customers' => $customerCount,
            'average' => $average,
            'production' => $production,
            'waiting' => $waiting,
            'completion_rate' => $completionRate,
            'total_customers' => User::query()->where('role', 'pelanggan')->count(),
        ];
    }

    /**
     * @return array{labels: array<int, string>, values: array<int, int>}
     */
    public function revenueSeries(CarbonInterface $from, CarbonInterface $to): array
    {
        $orders = Order::query()
            ->where('status_pembayaran', 'Lunas')
            ->whereBetween('tgl_pesan', [$from, $to])
            ->get(['grand_total', 'tgl_pesan']);

        $grouped = $orders->groupBy(fn (Order $order) => $order->tgl_pesan->format('Y-m-d'))
            ->map(fn (Collection $rows) => (int) $rows->sum('grand_total'));

        $labels = [];
        $values = [];
        $cursor = $from->copy();

        while ($cursor->lessThanOrEqualTo($to)) {
            $key = $cursor->format('Y-m-d');
            $labels[] = $cursor->copy()->locale('id')->translatedFormat('d M');
            $values[] = (int) ($grouped[$key] ?? 0);
            $cursor = $cursor->copy()->addDay();
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * @return array{labels: array<int, string>, values: array<int, int>}
     */
    public function monthlyRevenue(int $months = 6): array
    {
        $start = now()->startOfMonth()->subMonths($months - 1);
        $orders = Order::query()
            ->where('status_pembayaran', 'Lunas')
            ->where('tgl_pesan', '>=', $start)
            ->get(['grand_total', 'tgl_pesan']);

        $labels = [];
        $values = [];

        for ($i = 0; $i < $months; $i++) {
            $month = $start->copy()->addMonths($i);
            $labels[] = $month->locale('id')->translatedFormat('M Y');
            $values[] = (int) $orders
                ->filter(fn (Order $order) => $order->tgl_pesan->format('Y-m') === $month->format('Y-m'))
                ->sum('grand_total');
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * @return array{labels: array<int, string>, values: array<int, int>}
     */
    public function ordersByStatus(CarbonInterface $from, CarbonInterface $to): array
    {
        $rows = Order::query()
            ->whereBetween('tgl_pesan', [$from, $to])
            ->select('status_pesanan', DB::raw('COUNT(*) as total'))
            ->groupBy('status_pesanan')
            ->orderByDesc('total')
            ->get();

        return [
            'labels' => $rows->pluck('status_pesanan')->all(),
            'values' => $rows->pluck('total')->map(fn ($total) => (int) $total)->all(),
        ];
    }

    /**
     * @return Collection<int, object>
     */
    public function topProducts(CarbonInterface $from, CarbonInterface $to, int $limit = 5): Collection
    {
        return DB::table('detail_pesanan as d')
            ->join('pesanan as p', 'p.id_pesanan', '=', 'd.id_pesanan')
            ->where('p.status_pembayaran', 'Lunas')
            ->whereBetween('p.tgl_pesan', [$from, $to])
            ->select('d.nama_produk_snapshot', DB::raw('SUM(d.subtotal) as pendapatan'), DB::raw('SUM(d.jumlah) as qty'))
            ->groupBy('d.nama_produk_snapshot')
            ->orderByDesc('pendapatan')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, object>
     */
    public function revenueByCategory(CarbonInterface $from, CarbonInterface $to): Collection
    {
        return DB::table('detail_pesanan as d')
            ->join('pesanan as p', 'p.id_pesanan', '=', 'd.id_pesanan')
            ->join('bahan as b', 'b.id_bahan', '=', 'd.id_bahan')
            ->join('produk as pr', 'pr.id_produk', '=', 'b.id_produk')
            ->join('kategori_produk as k', 'k.id_kategori', '=', 'pr.id_kategori')
            ->where('p.status_pembayaran', 'Lunas')
            ->whereBetween('p.tgl_pesan', [$from, $to])
            ->select('k.nama_kategori', DB::raw('SUM(d.subtotal) as pendapatan'))
            ->groupBy('k.nama_kategori')
            ->orderByDesc('pendapatan')
            ->get();
    }

    /**
     * @return Collection<int, object>
     */
    public function topCustomers(CarbonInterface $from, CarbonInterface $to, int $limit = 5): Collection
    {
        return DB::table('pesanan as p')
            ->join('users as u', 'u.id_user', '=', 'p.id_user')
            ->where('p.status_pembayaran', 'Lunas')
            ->whereBetween('p.tgl_pesan', [$from, $to])
            ->select('u.nama', 'u.email', DB::raw('COUNT(p.id_pesanan) as total_pesanan'), DB::raw('SUM(p.grand_total) as total_belanja'))
            ->groupBy('u.id_user', 'u.nama', 'u.email')
            ->orderByDesc('total_belanja')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Order>
     */
    public function recentOrders(int $limit = 6): Collection
    {
        return Order::query()
            ->with('user')
            ->orderByDesc('tgl_pesan')
            ->limit($limit)
            ->get();
    }

    public function newOrderCount(): int
    {
        return Order::query()->where('status_pesanan', Order::STATUS_ANTREAN)->count();
    }
}
