<?php

namespace App\Http\Controllers\Pimpinan;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\DashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request, DashboardService $dashboard): View
    {
        $days = in_array($request->integer('periode'), [7, 30, 90, 365], true) ? $request->integer('periode') : 30;
        $from = now()->subDays($days - 1)->startOfDay();
        $to = now()->endOfDay();

        $metrics = $dashboard->metrics($from, $to);
        $previous = $dashboard->metrics($from->copy()->subDays($days), $from->copy()->subSecond());

        return view('pimpinan.dashboard', [
            'metrics' => $metrics,
            'growth' => [
                'revenue' => $this->growth($metrics['revenue'], $previous['revenue']),
                'orders' => $this->growth($metrics['orders'], $previous['orders']),
                'customers' => $this->growth($metrics['customers'], $previous['customers']),
                'average' => $this->growth($metrics['average'], $previous['average']),
            ],
            'days' => $days,
            'revenueSeries' => $dashboard->revenueSeries($from, $to),
            'monthlyRevenue' => $dashboard->monthlyRevenue(12),
            'statusSeries' => $dashboard->ordersByStatus($from, $to),
            'topProducts' => $dashboard->topProducts($from, $to, 8),
            'topCustomers' => $dashboard->topCustomers($from, $to, 5),
            'categoryRevenue' => $dashboard->revenueByCategory($from, $to),
            'recentOrders' => Order::query()
                ->with('user')
                ->where('status_pembayaran', 'Lunas')
                ->latest('tgl_pesan')
                ->limit(8)
                ->get(),
            'from' => $from,
            'to' => $to,
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
