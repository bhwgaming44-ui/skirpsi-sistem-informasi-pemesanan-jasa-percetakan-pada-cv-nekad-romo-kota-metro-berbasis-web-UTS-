<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\DashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardService $dashboard): View
    {
        $days = in_array($request->integer('periode'), [7, 30, 90], true) ? $request->integer('periode') : 30;
        $from = now()->subDays($days - 1)->startOfDay();
        $to = now()->endOfDay();

        $metrics = $dashboard->metrics($from, $to);
        $previous = $dashboard->metrics($from->copy()->subDays($days), $from->copy()->subSecond());

        $growth = [
            'revenue' => $this->growth($metrics['revenue'], $previous['revenue']),
            'orders' => $this->growth($metrics['orders'], $previous['orders']),
            'customers' => $this->growth($metrics['customers'], $previous['customers']),
            'average' => $this->growth($metrics['average'], $previous['average']),
        ];

        $queue = Order::query()
            ->with('user')
            ->whereIn('status_pesanan', [
                Order::STATUS_ANTREAN,
                Order::STATUS_CETAK,
                Order::STATUS_SELESAI_CETAK,
                Order::STATUS_SIAP,
            ])
            ->orderBy('tgl_pesan')
            ->limit(6)
            ->get();

        return view('admin.dashboard', [
            'metrics' => $metrics,
            'growth' => $growth,
            'days' => $days,
            'revenueSeries' => $dashboard->revenueSeries($from, $to),
            'monthlyRevenue' => $dashboard->monthlyRevenue(6),
            'statusSeries' => $dashboard->ordersByStatus($from, $to),
            'topProducts' => $dashboard->topProducts($from, $to),
            'categoryRevenue' => $dashboard->revenueByCategory($from, $to),
            'recentOrders' => $dashboard->recentOrders(6),
            'queue' => $queue,
            'newOrderCount' => $dashboard->newOrderCount(),
        ]);
    }

    private function growth(int|float $current, int|float $previous): int
    {
        if ($previous <= 0) {
            return $current > 0 ? 100 : 0;
        }

        return (int) round((($current - $previous) / $previous) * 100);
    }
}
