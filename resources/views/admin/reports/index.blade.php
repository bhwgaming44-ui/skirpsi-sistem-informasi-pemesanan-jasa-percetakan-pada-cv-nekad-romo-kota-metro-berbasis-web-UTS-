@extends('layouts.dashboard')

@section('title', 'Laporan Penjualan')

@section('content')
    @php
        $query = request()->only(['dari', 'sampai']);
        $periodeTeks = $from->translatedFormat('d M Y').' — '.$to->translatedFormat('d M Y');
    @endphp

    <div class="space-y-5">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="font-display text-xl font-extrabold text-slate-900">Laporan Penjualan</h2>
                <p class="mt-0.5 text-sm text-slate-500">Rekap transaksi lunas periode <span class="font-semibold text-slate-700">{{ $periodeTeks }}</span>.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('reports.export', $query) }}" class="btn-white btn-sm">
                    <x-icon name="download" class="h-4 w-4" /> Export CSV
                </a>
                <a href="{{ route('reports.print', $query) }}" target="_blank" class="btn-primary btn-sm">
                    <x-icon name="print" class="h-4 w-4" /> Cetak Laporan
                </a>
            </div>
        </div>

        {{-- Filter --}}
        <form method="GET" action="{{ route('reports.index') }}" class="card flex flex-col gap-3 p-4 sm:flex-row sm:items-end">
            <div class="flex-1">
                <label for="dari" class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-slate-400">Dari tanggal</label>
                <input id="dari" type="date" name="dari" value="{{ $from->format('Y-m-d') }}" class="input">
            </div>
            <div class="flex-1">
                <label for="sampai" class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-slate-400">Sampai tanggal</label>
                <input id="sampai" type="date" name="sampai" value="{{ $to->format('Y-m-d') }}" class="input">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary btn-sm !px-5">
                    <x-icon name="filter" class="h-4 w-4" /> Terapkan
                </button>
                <a href="{{ route('reports.index') }}" class="btn-white btn-sm">Bulan ini</a>
            </div>
        </form>

        {{-- Stats --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat-card label="Total pendapatan" :value="$metrics['revenue']" icon="wallet" accent="emerald" format="currency" hint="Transaksi lunas periode ini" />
            <x-stat-card label="Jumlah transaksi" :value="$metrics['orders']" icon="receipt" accent="brand" hint="Pesanan berstatus lunas" />
            <x-stat-card label="Pelanggan transaksi" :value="$metrics['customers']" icon="users" accent="sky" hint="Pelanggan unik" />
            <x-stat-card label="Rata-rata transaksi" :value="$metrics['average']" icon="chart" accent="violet" format="currency" hint="Nilai order rata-rata" />
        </div>

        {{-- Charts --}}
        <div class="grid gap-5 xl:grid-cols-[1.6fr_1fr]">
            <div class="card p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="font-display text-base font-bold text-slate-900">Pendapatan harian</h3>
                        <p class="text-xs text-slate-400">{{ $periodeTeks }}</p>
                    </div>
                    <span class="badge bg-emerald-50 text-emerald-700">
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
                <div>
                    <h3 class="font-display text-base font-bold text-slate-900">Pendapatan per kategori</h3>
                    <p class="text-xs text-slate-400">Kontribusi tiap kategori layanan.</p>
                </div>
                @if ($categoryRevenue->isEmpty())
                    <x-empty-state icon="chart" title="Belum ada data" class="mt-4 !py-8" message="Tidak ada transaksi lunas pada periode ini." />
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
                        <p class="text-xs text-slate-400">Berdasarkan pendapatan.</p>
                    </div>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                        <x-icon name="star" class="h-4.5 w-4.5" />
                    </span>
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
                        <p class="text-xs text-slate-400">Berdasarkan total belanja.</p>
                    </div>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                        <x-icon name="users" class="h-4.5 w-4.5" />
                    </span>
                </div>

                @if ($topCustomers->isEmpty())
                    <div class="p-5"><x-empty-state icon="users" title="Belum ada data" class="!py-8" message="Belum ada pelanggan bertransaksi pada periode ini." /></div>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach ($topCustomers as $index => $customer)
                            <div class="flex items-center gap-3 px-5 py-3.5">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl text-[11px] font-extrabold {{ $index === 0 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500' }}">
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

        {{-- Orders table --}}
        <div class="card overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 p-5">
                <div>
                    <h3 class="font-display text-base font-bold text-slate-900">Daftar transaksi lunas</h3>
                    <p class="text-xs text-slate-400">{{ $orders->total() }} transaksi pada periode ini.</p>
                </div>
                <span class="badge bg-slate-100 text-slate-500">
                    <x-icon name="calendar" class="h-3.5 w-3.5" /> {{ $periodeTeks }}
                </span>
            </div>

            @if ($orders->isEmpty())
                <div class="p-5">
                    <x-empty-state icon="receipt" title="Tidak ada transaksi" message="Tidak ada transaksi lunas pada rentang tanggal yang dipilih." />
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="table-modern min-w-[760px]">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Tanggal</th>
                                <th>Pelanggan</th>
                                <th>Metode</th>
                                <th>Total Produk</th>
                                <th>Ongkir</th>
                                <th class="text-right">Grand Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $order)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.orders.show', $order) }}" class="font-mono text-xs font-bold text-brand-600 transition hover:text-brand-700">
                                            {{ $order->kode_pesanan }}
                                        </a>
                                    </td>
                                    <td class="text-xs text-slate-500">{{ $order->tgl_pesan->translatedFormat('d M Y, H:i') }}</td>
                                    <td class="text-xs font-semibold text-slate-700">{{ $order->user->nama }}</td>
                                    <td class="text-[11px] text-slate-500">{{ $order->metode_pengambilan }}</td>
                                    <td class="text-xs text-slate-700">Rp {{ number_format((int) $order->total_produk, 0, ',', '.') }}</td>
                                    <td class="text-xs text-slate-700">{{ (float) $order->ongkir > 0 ? 'Rp '.number_format((int) $order->ongkir, 0, ',', '.') : 'Gratis' }}</td>
                                    <td class="text-right text-xs font-extrabold text-slate-900">Rp {{ number_format((int) $order->grand_total, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-slate-50/80">
                                <td colspan="6" class="px-4 py-3 text-right text-[11px] font-bold uppercase tracking-wider text-slate-500">Total halaman ini</td>
                                <td class="px-4 py-3 text-right font-display text-sm font-extrabold text-brand-600">
                                    Rp {{ number_format($orders->getCollection()->sum(fn ($order) => (int) $order->grand_total), 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 px-4 py-3">
                    <p class="text-[11px] text-slate-400">{{ $orders->firstItem() }}–{{ $orders->lastItem() }} dari {{ $orders->total() }} transaksi</p>
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
