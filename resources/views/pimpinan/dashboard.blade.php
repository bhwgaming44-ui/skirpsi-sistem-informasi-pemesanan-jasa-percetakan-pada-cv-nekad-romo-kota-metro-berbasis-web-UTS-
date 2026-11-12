@extends('layouts.dashboard')

@section('title', 'Dashboard Pimpinan')

@section('content')
    @php
        $periodeLabel = match ($days) {
            7 => '7 hari terakhir',
            90 => '90 hari terakhir',
            365 => '1 tahun terakhir',
            default => '30 hari terakhir',
        };
    @endphp

    <div class="space-y-6">
        {{-- Executive header --}}
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-ink-950 via-ink-900 to-brand-900 p-6 text-white shadow-[0_30px_70px_-35px_rgba(30,27,75,0.85)] sm:p-8">
            <div class="pointer-events-none absolute -right-12 -top-12 h-56 w-56 rounded-full bg-brand-500/25 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-16 left-1/3 h-56 w-56 rounded-full bg-accent-500/15 blur-3xl"></div>
            <div class="bg-grid pointer-events-none absolute inset-0 opacity-30"></div>

            <div class="relative flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <span class="badge bg-white/10 text-white ring-1 ring-white/15">
                        <x-icon name="chart" class="h-3.5 w-3.5 text-accent-400" /> Laporan eksekutif
                    </span>
                    <h2 class="mt-3 font-display text-2xl font-extrabold sm:text-3xl">Ringkasan Kinerja Usaha</h2>
                    <p class="mt-1.5 max-w-xl text-sm leading-relaxed text-indigo-100/70">
                        Data transaksi lunas {{ $periodeLabel }}
                        ({{ $from->translatedFormat('d M Y') }} — {{ $to->translatedFormat('d M Y') }}).
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <form method="GET" action="{{ route('pimpinan.dashboard') }}" class="flex items-center gap-1.5 rounded-xl bg-white/10 p-1 backdrop-blur">
                        @foreach ([7 => '7 Hari', 30 => '30 Hari', 90 => '90 Hari', 365 => '1 Tahun'] as $value => $label)
                            <button type="submit" name="periode" value="{{ $value }}"
                                    class="rounded-lg px-3 py-1.5 text-[11px] font-bold transition {{ $days === $value ? 'bg-white text-brand-700 shadow' : 'text-indigo-100/70 hover:bg-white/10 hover:text-white' }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </form>
                    <a href="{{ route('reports.index', ['dari' => $from->format('Y-m-d'), 'sampai' => $to->format('Y-m-d')]) }}" class="btn !bg-white !text-brand-700 shadow-lg hover:-translate-y-0.5">
                        <x-icon name="document" class="h-4.5 w-4.5" /> Laporan Detail
                    </a>
                </div>
            </div>
        </div>

        {{-- KPI --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat-card label="Pendapatan" :value="$metrics['revenue']" icon="wallet" accent="emerald" format="currency" :trend="$growth['revenue']" />
            <x-stat-card label="Jumlah transaksi" :value="$metrics['orders']" icon="receipt" accent="brand" :trend="$growth['orders']" />
            <x-stat-card label="Pelanggan bertransaksi" :value="$metrics['customers']" icon="users" accent="sky" :trend="$growth['customers']" />
            <x-stat-card label="Nilai order rata-rata" :value="$metrics['average']" icon="chart" accent="violet" format="currency" :trend="$growth['average']" />
        </div>

        {{-- Operational snapshot --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="card flex items-center gap-3 p-4">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                    <x-icon name="clock" class="h-5 w-5" />
                </span>
                <div class="leading-tight">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Menunggu pembayaran</p>
                    <p class="font-display text-lg font-extrabold text-slate-900">{{ $metrics['waiting'] }} pesanan</p>
                </div>
            </div>
            <div class="card flex items-center gap-3 p-4">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-50 text-sky-600">
                    <x-icon name="printer" class="h-5 w-5" />
                </span>
                <div class="leading-tight">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Dalam produksi</p>
                    <p class="font-display text-lg font-extrabold text-slate-900">{{ $metrics['production'] }} pesanan</p>
                </div>
            </div>
            <div class="card flex items-center gap-3 p-4">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                    <x-icon name="check-circle" class="h-5 w-5" />
                </span>
                <div class="leading-tight">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tingkat penyelesaian</p>
                    <p class="font-display text-lg font-extrabold text-slate-900">{{ $metrics['completion_rate'] }}%</p>
                </div>
            </div>
            <div class="card flex items-center gap-3 p-4">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                    <x-icon name="users" class="h-5 w-5" />
                </span>
                <div class="leading-tight">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total akun pelanggan</p>
                    <p class="font-display text-lg font-extrabold text-slate-900">{{ $metrics['total_customers'] }} akun</p>
                </div>
            </div>
        </div>

        {{-- Revenue & status --}}
        <div class="grid gap-5 xl:grid-cols-[1.6fr_1fr]">
            <div class="card p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="font-display text-base font-bold text-slate-900">Tren pendapatan harian</h3>
                        <p class="text-xs text-slate-400">{{ $periodeLabel }}</p>
                    </div>
                    <span class="badge bg-emerald-50 text-emerald-700">Rp {{ number_format($metrics['revenue'], 0, ',', '.') }}</span>
                </div>
                <div class="mt-3"
                     data-chart="{{ json_encode([
                        'type' => 'area',
                        'labels' => $revenueSeries['labels'],
                        'values' => $revenueSeries['values'],
                        'height' => 310,
                        'name' => 'Pendapatan',
                     ]) }}"></div>
            </div>

            <div class="card p-5">
                <div>
                    <h3 class="font-display text-base font-bold text-slate-900">Status pesanan</h3>
                    <p class="text-xs text-slate-400">Distribusi pesanan {{ $periodeLabel }}.</p>
                </div>
                <div class="mt-2"
                     data-chart="{{ json_encode([
                        'type' => 'donut',
                        'labels' => $statusSeries['labels'],
                        'values' => $statusSeries['values'],
                        'height' => 310,
                        'currency' => false,
                        'totalLabel' => 'Pesanan',
                     ]) }}"></div>
            </div>
        </div>

        {{-- Monthly & category --}}
        <div class="grid gap-5 xl:grid-cols-[1.6fr_1fr]">
            <div class="card p-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="font-display text-base font-bold text-slate-900">Pendapatan 12 bulan</h3>
                        <p class="text-xs text-slate-400">Tren jangka panjang untuk pengambilan keputusan.</p>
                    </div>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                        <x-icon name="trending-up" class="h-4.5 w-4.5" />
                    </span>
                </div>
                <div class="mt-2"
                     data-chart="{{ json_encode([
                        'type' => 'bar',
                        'labels' => $monthlyRevenue['labels'],
                        'values' => $monthlyRevenue['values'],
                        'height' => 300,
                        'name' => 'Pendapatan',
                     ]) }}"></div>
            </div>

            <div class="card p-5">
                <div>
                    <h3 class="font-display text-base font-bold text-slate-900">Kontribusi kategori</h3>
                    <p class="text-xs text-slate-400">Pendapatan per kategori layanan.</p>
                </div>
                @if ($categoryRevenue->isEmpty())
                    <x-empty-state icon="chart" title="Belum ada data" class="mt-4 !py-10" message="Belum ada transaksi lunas pada periode ini." />
                @else
                    <div class="mt-2"
                         data-chart="{{ json_encode([
                            'type' => 'donut',
                            'labels' => $categoryRevenue->pluck('nama_kategori')->all(),
                            'values' => $categoryRevenue->pluck('pendapatan')->map(fn ($v) => (int) $v)->all(),
                            'height' => 300,
                            'totalLabel' => 'Pendapatan',
                         ]) }}"></div>
                @endif
            </div>
        </div>

        {{-- Top products & customers --}}
        <div class="grid gap-5 xl:grid-cols-2">
            <div class="card overflow-hidden">
                <div class="flex items-center justify-between gap-2 border-b border-slate-100 p-5">
                    <div>
                        <h3 class="font-display text-base font-bold text-slate-900">Produk terlaris</h3>
                        <p class="text-xs text-slate-400">Delapan produk dengan pendapatan tertinggi.</p>
                    </div>
                    <span class="badge bg-amber-50 text-amber-700"><x-icon name="star" class="h-3.5 w-3.5" /> Top 8</span>
                </div>

                @if ($topProducts->isEmpty())
                    <div class="p-5"><x-empty-state icon="package" title="Belum ada penjualan" class="!py-8" message="Belum ada produk terjual pada periode ini." /></div>
                @else
                    <div class="overflow-x-auto">
                        <table class="table-modern min-w-[480px]">
                            <thead>
                                <tr>
                                    <th class="w-10">#</th>
                                    <th>Produk</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-right">Pendapatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($topProducts as $index => $product)
                                    <tr>
                                        <td>
                                            <span class="flex h-7 w-7 items-center justify-center rounded-lg text-[10px] font-extrabold {{ $index === 0 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500' }}">
                                                {{ $index + 1 }}
                                            </span>
                                        </td>
                                        <td class="text-xs font-semibold text-slate-800">{{ $product->nama_produk_snapshot }}</td>
                                        <td class="text-center text-xs font-semibold text-slate-600">{{ (int) $product->qty }}</td>
                                        <td class="text-right text-xs font-bold text-slate-800">Rp {{ number_format((int) $product->pendapatan, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="card overflow-hidden">
                <div class="flex items-center justify-between gap-2 border-b border-slate-100 p-5">
                    <div>
                        <h3 class="font-display text-base font-bold text-slate-900">Pelanggan teratas</h3>
                        <p class="text-xs text-slate-400">Lima pelanggan dengan belanja tertinggi.</p>
                    </div>
                    <span class="badge bg-violet-50 text-violet-700"><x-icon name="users" class="h-3.5 w-3.5" /> Top 5</span>
                </div>

                @if ($topCustomers->isEmpty())
                    <div class="p-5"><x-empty-state icon="users" title="Belum ada data" class="!py-8" message="Belum ada pelanggan bertransaksi pada periode ini." /></div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach ($topCustomers as $index => $customer)
                            <div class="flex items-center gap-3 px-5 py-4">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-[11px] font-extrabold {{ $index === 0 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $index + 1 }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-xs font-bold text-slate-800">{{ $customer->nama }}</p>
                                    <p class="truncate text-[10px] text-slate-400">{{ $customer->email }}</p>
                                </div>
                                <div class="shrink-0 text-right">
                                    <p class="text-xs font-extrabold text-slate-800">Rp {{ number_format((int) $customer->total_belanja, 0, ',', '.') }}</p>
                                    <p class="text-[10px] text-slate-400">{{ (int) $customer->total_pesanan }} pesanan</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Recent paid orders --}}
        <div class="card overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-5">
                <div>
                    <h3 class="font-display text-base font-bold text-slate-900">Transaksi terbaru</h3>
                    <p class="text-xs text-slate-400">Delapan transaksi lunas terakhir.</p>
                </div>
                <a href="{{ route('reports.index') }}" class="btn-white btn-sm">Buka laporan</a>
            </div>

            <div class="overflow-x-auto">
                <table class="table-modern min-w-[720px]">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th>Metode</th>
                            <th class="text-right">Grand Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentOrders as $order)
                            <tr>
                                <td class="font-mono text-xs font-bold text-brand-600">{{ $order->kode_pesanan }}</td>
                                <td class="text-xs text-slate-500">{{ $order->tgl_pesan->translatedFormat('d M Y, H:i') }}</td>
                                <td class="text-xs font-semibold text-slate-700">{{ $order->user->nama }}</td>
                                <td class="text-[11px] text-slate-500">{{ $order->metode_pengambilan }}</td>
                                <td class="text-right text-xs font-extrabold text-slate-900">Rp {{ number_format((int) $order->grand_total, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="!py-10 text-center text-sm text-slate-400">Belum ada transaksi lunas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
