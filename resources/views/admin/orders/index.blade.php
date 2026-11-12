@extends('layouts.dashboard')

@section('title', 'Manajemen Pesanan')

@section('content')
    <div class="space-y-5">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="font-display text-xl font-extrabold text-slate-900">Manajemen Pesanan</h2>
                <p class="mt-0.5 text-sm text-slate-500">Pantau seluruh transaksi, verifikasi pembayaran, dan kelola status produksi.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.production.board') }}" class="btn-white btn-sm">
                    <x-icon name="printer" class="h-4 w-4" /> Board produksi
                </a>
                <a href="{{ route('admin.orders.index', ['status' => \App\Models\Order::STATUS_MENUNGGU]) }}" class="btn-primary btn-sm">
                    <x-icon name="clock" class="h-4 w-4" /> Menunggu bayar
                </a>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.orders.index') }}" class="card space-y-3 p-4">
            <div class="grid gap-3 lg:grid-cols-[1.4fr_1fr_1fr_auto]">
                <div class="relative">
                    <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                        <x-icon name="search" class="h-4.5 w-4.5" />
                    </span>
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari kode pesanan / nama pelanggan..." class="input !pl-10">
                </div>

                <select name="status" class="select" onchange="this.form.submit()">
                    <option value="">Semua status pesanan</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                    @endforeach
                </select>

                <select name="pembayaran" class="select" onchange="this.form.submit()">
                    <option value="">Semua pembayaran</option>
                    @foreach (['Lunas', 'Menunggu Pembayaran', 'Gagal', 'Kadaluarsa'] as $status)
                        <option value="{{ $status }}" @selected(request('pembayaran') === $status)>{{ $status }}</option>
                    @endforeach
                </select>

                <div class="flex gap-2">
                    <button type="submit" class="btn-primary btn-sm !px-4">Filter</button>
                    @if (request()->hasAny(['q', 'status', 'pembayaran', 'dari', 'sampai']))
                        <a href="{{ route('admin.orders.index') }}" class="btn-white btn-sm">Reset</a>
                    @endif
                </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:max-w-md">
                <div>
                    <label for="dari" class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-slate-400">Dari tanggal</label>
                    <input id="dari" type="date" name="dari" value="{{ request('dari') }}" class="input !py-2 !text-xs">
                </div>
                <div>
                    <label for="sampai" class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-slate-400">Sampai tanggal</label>
                    <input id="sampai" type="date" name="sampai" value="{{ request('sampai') }}" class="input !py-2 !text-xs">
                </div>
            </div>
        </form>

        <div class="card overflow-hidden">
            @if ($orders->isEmpty())
                <div class="p-5">
                    <x-empty-state icon="receipt" title="Tidak ada pesanan" message="Belum ada pesanan yang cocok dengan filter yang dipilih.">
                        <a href="{{ route('admin.orders.index') }}" class="btn-white btn-sm mt-1">
                            <x-icon name="refresh" class="h-4 w-4" /> Reset filter
                        </a>
                    </x-empty-state>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="table-modern min-w-[900px]">
                        <thead>
                            <tr>
                                <th>Kode & Tanggal</th>
                                <th>Pelanggan</th>
                                <th class="text-center">Item</th>
                                <th>Total</th>
                                <th>Pembayaran</th>
                                <th>Status Pesanan</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $order)
                                <tr>
                                    <td>
                                        <p class="font-mono text-xs font-bold text-brand-600">{{ $order->kode_pesanan }}</p>
                                        <p class="text-[10px] text-slate-400">{{ $order->tgl_pesan->translatedFormat('d M Y, H:i') }} WIB</p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-bold text-slate-800">{{ $order->user->nama }}</p>
                                        <p class="text-[10px] text-slate-400">{{ $order->user->no_hp }}</p>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-slate-100 text-slate-600">{{ $order->detail_count }}</span>
                                    </td>
                                    <td class="text-xs font-bold text-slate-800">Rp {{ number_format((int) $order->grand_total, 0, ',', '.') }}</td>
                                    <td>
                                        <x-payment-badge :status="$order->status_pembayaran" />
                                        @if ($order->transaksiTerakhir)
                                            <p class="mt-1 text-[9px] font-semibold uppercase tracking-wider text-slate-400">
                                                {{ $order->transaksiTerakhir->payment_type ?? '-' }}
                                            </p>
                                        @endif
                                    </td>
                                    <td><x-status-badge :status="$order->status_pesanan" /></td>
                                    <td>
                                        <div class="flex items-center justify-end gap-1">
                                            @if ($order->isPaid() && ! $order->isCancelled() && $order->nextStatus() && $order->status_pesanan !== \App\Models\Order::STATUS_SELESAI)
                                                <form method="POST" action="{{ route('admin.orders.advance', $order) }}"
                                                      data-confirm="Status pesanan akan dilanjutkan ke tahap berikutnya."
                                                      data-confirm-button="Ya, lanjutkan" data-confirm-icon="question"
                                                      data-confirm-title="Lanjutkan status pesanan?">
                                                    @csrf
                                                    <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-emerald-500 transition hover:bg-emerald-50 hover:text-emerald-700" title="Lanjutkan status">
                                                        <x-icon name="arrow-right" class="h-4 w-4" />
                                                    </button>
                                                </form>
                                            @endif
                                            <a href="{{ route('admin.orders.invoice', $order) }}" target="_blank"
                                               class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700" title="Invoice">
                                                <x-icon name="print" class="h-4 w-4" />
                                            </a>
                                            <a href="{{ route('admin.orders.show', $order) }}"
                                               class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-brand-50 hover:text-brand-600" title="Detail">
                                                <x-icon name="eye" class="h-4 w-4" />
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 px-4 py-3">
                    <p class="text-[11px] text-slate-400">
                        Menampilkan {{ $orders->firstItem() }}–{{ $orders->lastItem() }} dari {{ $orders->total() }} pesanan
                    </p>
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
