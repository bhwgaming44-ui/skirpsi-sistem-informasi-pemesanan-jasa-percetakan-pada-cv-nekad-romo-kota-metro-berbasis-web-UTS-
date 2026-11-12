@extends('layouts.dashboard')

@section('title', 'Pesanan Saya')

@section('content')
    <div class="space-y-5">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="font-display text-xl font-extrabold text-slate-900">Riwayat pesanan</h2>
                <p class="mt-0.5 text-sm text-slate-500">Semua pesanan cetak yang pernah Anda buat.</p>
            </div>
            <a href="{{ route('catalog.index') }}" class="btn-primary btn-sm">
                <x-icon name="plus" class="h-4 w-4" /> Pesan baru
            </a>
        </div>

        <form method="GET" action="{{ route('customer.orders.index') }}" class="card flex flex-col gap-3 p-4 sm:flex-row sm:items-center">
            <div class="relative flex-1">
                <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                    <x-icon name="search" class="h-4.5 w-4.5" />
                </span>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari kode pesanan..."
                       class="input !pl-10">
            </div>
            <select name="status" class="select sm:!w-64" onchange="this.form.submit()">
                <option value="">Semua status</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary btn-sm !px-4">Cari</button>
                @if (request()->filled('q') || request()->filled('status'))
                    <a href="{{ route('customer.orders.index') }}" class="btn-white btn-sm">Reset</a>
                @endif
            </div>
        </form>

        @if ($orders->isEmpty())
            <x-empty-state icon="receipt" title="Belum ada pesanan"
                           message="Pesanan yang Anda buat akan muncul di sini beserta status produksinya.">
                <a href="{{ route('catalog.index') }}" class="btn-primary btn-sm mt-1">
                    <x-icon name="cart" class="h-4 w-4" /> Mulai pesan cetak
                </a>
            </x-empty-state>
        @else
            <div class="grid gap-4">
                @foreach ($orders as $order)
                    <article class="card-hover overflow-hidden">
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 bg-slate-50/50 px-4 py-3 sm:px-5">
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white text-brand-600 shadow-sm">
                                    <x-icon name="receipt" class="h-4 w-4" />
                                </span>
                                <div>
                                    <p class="font-mono text-xs font-bold text-slate-800">{{ $order->kode_pesanan }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $order->tgl_pesan->translatedFormat('d F Y, H:i') }} WIB</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <x-payment-badge :status="$order->status_pembayaran" />
                                <x-status-badge :status="$order->status_pesanan" />
                            </div>
                        </div>

                        <div class="flex flex-col gap-4 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
                            <div class="flex flex-wrap items-center gap-x-6 gap-y-2">
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Item</p>
                                    <p class="text-sm font-semibold text-slate-800">{{ $order->detail_count }} item cetak</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total</p>
                                    <p class="text-sm font-extrabold text-brand-600">Rp {{ number_format((int) $order->grand_total, 0, ',', '.') }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Pengambilan</p>
                                    <p class="text-sm font-semibold text-slate-800">{{ $order->metode_pengambilan === 'Ambil di Toko' ? 'Ambil di toko' : 'Kirim kurir' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Estimasi</p>
                                    <p class="text-sm font-semibold text-slate-800">{{ $order->estimasi_selesai?->translatedFormat('d M Y') ?? '-' }}</p>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                @if ($order->status_pesanan === \App\Models\Order::STATUS_MENUNGGU)
                                    <a href="{{ route('payments.show', $order) }}" class="btn-primary btn-sm">
                                        <x-icon name="credit-card" class="h-4 w-4" /> Bayar
                                    </a>
                                @endif
                                <a href="{{ route('customer.orders.show', $order) }}" class="btn-white btn-sm">
                                    <x-icon name="eye" class="h-4 w-4" /> Detail
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            @if ($orders->hasPages())
                <div>{{ $orders->links() }}</div>
            @endif
        @endif
    </div>
@endsection
