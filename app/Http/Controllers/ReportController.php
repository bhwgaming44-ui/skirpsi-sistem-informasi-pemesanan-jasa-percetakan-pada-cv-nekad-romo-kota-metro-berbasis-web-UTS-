<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\DashboardService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request, DashboardService $dashboard): View
    {
        [$from, $to] = $this->range($request);

        $orders = Order::query()
            ->with('user')
            ->where('status_pembayaran', 'Lunas')
            ->whereBetween('tgl_pesan', [$from->startOfDay(), $to->endOfDay()])
            ->latest('tgl_pesan')
            ->paginate(15)
            ->withQueryString();

        return view('admin.reports.index', [
            'from' => $from,
            'to' => $to,
            'metrics' => $dashboard->metrics($from->startOfDay(), $to->endOfDay()),
            'revenueSeries' => $dashboard->revenueSeries($from, $to),
            'topProducts' => $dashboard->topProducts($from->startOfDay(), $to->endOfDay(), 8),
            'categoryRevenue' => $dashboard->revenueByCategory($from->startOfDay(), $to->endOfDay()),
            'topCustomers' => $dashboard->topCustomers($from->startOfDay(), $to->endOfDay(), 8),
            'orders' => $orders,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        [$from, $to] = $this->range($request);

        $orders = Order::query()
            ->with('user')
            ->where('status_pembayaran', 'Lunas')
            ->whereBetween('tgl_pesan', [$from->startOfDay(), $to->endOfDay()])
            ->orderBy('tgl_pesan')
            ->limit(5000)
            ->get();

        $filename = 'laporan-penjualan-'.$from->format('Ymd').'-'.$to->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($orders): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                'Kode Pesanan', 'Tanggal', 'Pelanggan', 'Metode', 'Total Produk',
                'Ongkir', 'Grand Total', 'Status Pembayaran', 'Status Pesanan',
            ]);

            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->kode_pesanan,
                    $order->tgl_pesan->format('Y-m-d H:i'),
                    $order->user->nama,
                    $order->metode_pengambilan,
                    (int) $order->total_produk,
                    (int) $order->ongkir,
                    (int) $order->grand_total,
                    $order->status_pembayaran,
                    $order->status_pesanan,
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function print(Request $request, DashboardService $dashboard): View
    {
        [$from, $to] = $this->range($request);

        $orders = Order::query()
            ->with('user')
            ->where('status_pembayaran', 'Lunas')
            ->whereBetween('tgl_pesan', [$from->startOfDay(), $to->endOfDay()])
            ->orderBy('tgl_pesan')
            ->limit(500)
            ->get();

        return view('admin.reports.print', [
            'from' => $from,
            'to' => $to,
            'metrics' => $dashboard->metrics($from->startOfDay(), $to->endOfDay()),
            'topProducts' => $dashboard->topProducts($from->startOfDay(), $to->endOfDay(), 8),
            'orders' => $orders,
        ]);
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private function range(Request $request): array
    {
        $from = CarbonImmutable::parse($request->date('dari') ?? now()->startOfMonth())->startOfDay();
        $to = CarbonImmutable::parse($request->date('sampai') ?? now())->startOfDay();

        if ($to->lessThan($from)) {
            [$from, $to] = [$to, $from];
        }

        return [$from, $to];
    }
}
