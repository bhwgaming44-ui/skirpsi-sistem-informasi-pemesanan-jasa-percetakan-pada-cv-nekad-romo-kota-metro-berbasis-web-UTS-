@extends('layouts.dashboard')

@section('title', 'Dashboard Admin')

@section('content')
    @php
        $periodeLabel = match ($days) {
            7 => '7 hari terakhir',
            90 => '90 hari terakhir',
            default => '30 hari terakhir',
        };
    @endphp

    <div class="space-y-6">
        {{-- Header --}}
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-ink-900 via-ink-900 to-brand-900 p-6 text-white shadow-[0_30px_70px_-35px_rgba(30,27,75,0.8)] sm:p-8">
            <div class="pointer-events-none absolute -right-10 -top-10 h-52 w-52 rounded-full bg-brand-500/25 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-16 left-1/4 h-52 w-52 rounded-full bg-accent-500/15 blur-3xl"></div>

            <div class="relative flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <span class="badge bg-white/10 text-white ring-1 ring-white/15">
                        <span class="relative flex h-2 w-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                        </span>
                        Operasional aktif
                    </span>
                    <h2 class="mt-3 font-display text-2xl font-extrabold sm:text-3xl">
                        Selamat bekerja, {{ \Illuminate\Support\Str::before(auth()->user()->nama, ' ') }} 👋
                    </h2>
                    <p class="mt-1.5 max-w-xl text-sm leading-relaxed text-indigo-100/70">
                        Ringkasan performa toko {{ $periodeLabel }}. Ada
                        <span class="font-bold text-white">{{ $metrics['production'] }}</span> pesanan dalam produksi
                        dan <span class="font-bold text-white">{{ $metrics['waiting'] }}</span> menunggu pembayaran.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-1.5 rounded-xl bg-white/10 p-1 backdrop-blur">
                        @foreach ([7 => '7 Hari', 30 => '30 Hari', 90 => '90 Hari'] as $value => $label)
                            <button type="submit" name="periode" value="{{ $value }}"
                                    class="rounded-lg px-3 py-1.5 text-[11px] font-bold transition {{ $days === $value ? 'bg-white text-brand-700 shadow' : 'text-indigo-100/70 hover:bg-white/10 hover:text-white' }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </form>
                    <a href="{{ route('admin.production.board') }}" class="btn !bg-white !text-brand-700 shadow-lg hover:-translate-y-0.5">
                        <x-icon name="printer" class="h-4.5 w-4.5" /> Antrean Produksi
                    </a>
                </div>
            </div>
        </div>

        {{-- Stats --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat-card label="Pendapatan" :value="$metrics['revenue']" icon="wallet" accent="emerald" format="currency" :trend="$growth['revenue']" />
            <x-stat-card label="Pesanan lunas" :value="$metrics['orders']" icon="receipt" accent="brand" :trend="$growth['orders']" />
            <x-stat-card label="Pelanggan transaksi" :value="$metrics['customers']" icon="users" accent="sky" :trend="$growth['customers']" />
            <x-stat-card label="Rata-rata order" :value="$metrics['average']" icon="chart" accent="violet" format="currency" :trend="$growth['average']" />
        </div>

        {{-- Mini insights --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="card flex items-center gap-3 p-4">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                    <x-icon name="clock" class="h-5 w-5" />
                </span>
                <div class="leading-tight">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Menunggu bayar</p>
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
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tingkat selesai</p>
                    <p class="font-display text-lg font-extrabold text-slate-900">
                        <span data-counter="{{ $metrics['completion_rate'] }}" data-format="percent">{{ $metrics['completion_rate'] }}%</span>
                    </p>
                </div>
            </div>
            <div class="card flex items-center gap-3 p-4">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                    <x-icon name="users" class="h-5 w-5" />
                </span>
                <div class="leading-tight">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total pelanggan</p>
                    <p class="font-display text-lg font-extrabold text-slate-900">{{ $metrics['total_customers'] }} akun</p>
                </div>
            </div>
        </div>

        {{-- Revenue + status --}}
        <div class="grid gap-5 xl:grid-cols-[1.6fr_1fr]">
            <div class="card p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="font-display text-base font-bold text-slate-900">Tren pendapatan</h3>
                        <p class="text-xs text-slate-400">Pendapatan pesanan lunas per hari · {{ $periodeLabel }}</p>
                    </div>
                    <span class="badge bg-emerald-50 text-emerald-700">
                        <x-icon name="trending-up" class="h-3.5 w-3.5" />
                        Rp {{ number_format($metrics['revenue'], 0, ',', '.') }}
                    </span>
                </div>
                <div class="mt-3"
                     data-chart="{{ json_encode([
                        'type' => 'area',
                        'labels' => $revenueSeries['labels'],
                        'values' => $revenueSeries['values'],
                        'height' => 300,
                        'name' => 'Pendapatan',
                     ]) }}"></div>
            </div>

            <div class="card p-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="font-display text-base font-bold text-slate-900">Status pesanan</h3>
                        <p class="text-xs text-slate-400">Distribusi seluruh pesanan periode ini.</p>
                    </div>
                </div>
                <div class="mt-2"
                     data-chart="{{ json_encode([
                        'type' => 'donut',
                        'labels' => $statusSeries['labels'],
                        'values' => $statusSeries['values'],
                        'height' => 300,
                        'currency' => false,
                        'totalLabel' => 'Pesanan',
                     ]) }}"></div>
            </div>
        </div>

        {{-- Monthly + category + top products --}}
        <div class="grid gap-5 xl:grid-cols-3">
            <div class="card p-5 xl:col-span-2">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="font-display text-base font-bold text-slate-900">Pendapatan 6 bulan</h3>
                        <p class="text-xs text-slate-400">Perbandingan performa penjualan bulanan.</p>
                    </div>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                        <x-icon name="chart" class="h-4.5 w-4.5" />
                    </span>
                </div>
                <div class="mt-2"
                     data-chart="{{ json_encode([
                        'type' => 'bar',
                        'labels' => $monthlyRevenue['labels'],
                        'values' => $monthlyRevenue['values'],
                        'height' => 288,
                        'name' => 'Pendapatan',
                     ]) }}"></div>
            </div>

            <div class="card p-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="font-display text-base font-bold text-slate-900">Pendapatan kategori</h3>
                        <p class="text-xs text-slate-400">Kontribusi tiap kategori layanan.</p>
                    </div>
                </div>
                @if ($categoryRevenue->isEmpty())
                    <x-empty-state icon="chart" title="Belum ada data" class="mt-4 !py-10" message="Belum ada transaksi lunas pada periode ini." />
                @else
                    <div class="mt-2"
                         data-chart="{{ json_encode([
                            'type' => 'donut',
                            'labels' => $categoryRevenue->pluck('nama_kategori')->all(),
                            'values' => $categoryRevenue->pluck('pendapatan')->map(fn ($v) => (int) $v)->all(),
                            'height' => 288,
                            'totalLabel' => 'Pendapatan',
                         ]) }}"></div>
                @endif
            </div>
        </div>

        {{-- Queue + top products --}}
        <div class="grid gap-5 xl:grid-cols-[1.6fr_1fr]">
            <div class="card overflow-hidden">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-5">
                    <div>
                        <h3 class="font-display text-base font-bold text-slate-900">Antrean produksi</h3>
                        <p class="text-xs text-slate-400">Pesanan yang sedang dikerjakan workshop.</p>
                    </div>
                    <a href="{{ route('admin.production.board') }}" class="btn-ghost btn-sm">
                        Buka board <x-icon name="arrow-right" class="h-4 w-4" />
                    </a>
                </div>

                @if ($queue->isEmpty())
                    <div class="p-5">
                        <x-empty-state icon="printer" title="Antrean kosong" message="Tidak ada pesanan dalam proses produksi saat ini." />
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="table-modern min-w-[640px]">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Pelanggan</th>
                                    <th>Status</th>
                                    <th>Estimasi</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($queue as $order)
                                    <tr>
                                        <td>
                                            <p class="font-mono text-xs font-bold text-brand-600">{{ $order->kode_pesanan }}</p>
                                            <p class="text-[10px] text-slate-400">{{ $order->tgl_pesan->translatedFormat('d M Y') }}</p>
                                        </td>
                                        <td class="text-xs font-semibold text-slate-700">{{ $order->user->nama }}</td>
                                        <td><x-status-badge :status="$order->status_pesanan" /></td>
                                        <td class="text-xs text-slate-500">{{ $order->estimasi_selesai?->translatedFormat('d M Y') ?? '-' }}</td>
                                        <td class="text-right">
                                            <a href="{{ route('admin.orders.show', $order) }}"
                                               class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-brand-50 hover:text-brand-600" title="Detail">
                                                <x-icon name="eye" class="h-4 w-4" />
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="card p-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="font-display text-base font-bold text-slate-900">Produk terlaris</h3>
                        <p class="text-xs text-slate-400">Berdasarkan pendapatan periode ini.</p>
                    </div>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                        <x-icon name="star" class="h-4.5 w-4.5" />
                    </span>
                </div>

                @if ($topProducts->isEmpty())
                    <x-empty-state icon="package" title="Belum ada penjualan" class="mt-4 !py-10" message="Data produk terlaris muncul setelah ada pesanan lunas." />
                @else
                    <div class="mt-4 space-y-3">
                        @foreach ($topProducts as $index => $product)
                            <div class="flex items-center gap-3">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-xs font-extrabold {{ $index === 0 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $index + 1 }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-xs font-bold text-slate-800">{{ $product->nama_produk_snapshot }}</p>
                                    <p class="text-[10px] text-slate-400">{{ (int) $product->qty }} unit terjual</p>
                                </div>
                                <p class="shrink-0 text-xs font-bold text-slate-800">Rp {{ number_format((int) $product->pendapatan, 0, ',', '.') }}</p>
                            </div>
                        @endforeach
                    </div>
                    <a href="{{ route('reports.index') }}" class="btn-ghost btn-sm mt-4 w-full">
                        <x-icon name="chart" class="h-4 w-4" /> Lihat laporan lengkap
                    </a>
                @endif
            </div>
        </div>

        {{-- Recent orders --}}
        <div class="card overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-5">
                <div>
                    <h3 class="font-display text-base font-bold text-slate-900">Pesanan terbaru</h3>
                    <p class="text-xs text-slate-400">Enam pesanan terakhir yang masuk.</p>
                </div>
                <a href="{{ route('admin.orders.index') }}" class="btn-white btn-sm">
                    Semua pesanan <x-icon name="arrow-right" class="h-4 w-4" />
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="table-modern min-w-[760px]">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Pelanggan</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Pembayaran</th>
                            <th>Status</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentOrders as $order)
                            <tr>
                                <td class="font-mono text-xs font-bold text-brand-600">{{ $order->kode_pesanan }}</td>
                                <td class="text-xs font-semibold text-slate-700">{{ $order->user->nama }}</td>
                                <td class="text-xs text-slate-500">{{ $order->tgl_pesan->translatedFormat('d M Y, H:i') }}</td>
                                <td class="text-xs font-bold text-slate-800">Rp {{ number_format((int) $order->grand_total, 0, ',', '.') }}</td>
                                <td><x-payment-badge :status="$order->status_pembayaran" /></td>
                                <td><x-status-badge :status="$order->status_pesanan" /></td>
                                <td class="text-right">
                                    <a href="{{ route('admin.orders.show', $order) }}"
                                       class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-brand-50 hover:text-brand-600" title="Detail">
                                        <x-icon name="eye" class="h-4 w-4" />
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="!py-10 text-center text-sm text-slate-400">Belum ada pesanan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
