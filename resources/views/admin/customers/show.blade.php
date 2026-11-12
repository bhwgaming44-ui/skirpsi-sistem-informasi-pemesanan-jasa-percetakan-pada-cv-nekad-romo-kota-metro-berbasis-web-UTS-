@extends('layouts.dashboard')

@section('title', 'Profil Pelanggan')

@section('content')
    <div class="space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <a href="{{ route('admin.customers.index') }}" class="inline-flex items-center gap-1 text-[11px] font-bold text-slate-400 transition hover:text-brand-600">
                    <x-icon name="arrow-left" class="h-3.5 w-3.5" /> Kembali ke daftar pelanggan
                </a>
                <h2 class="mt-1 font-display text-xl font-extrabold text-slate-900">{{ $customer->nama }}</h2>
                <p class="mt-0.5 text-xs text-slate-500">Pelanggan sejak {{ $customer->created_at?->translatedFormat('d F Y') ?? '-' }}</p>
            </div>
            <a href="{{ route('admin.orders.index', ['q' => $customer->nama]) }}" class="btn-white btn-sm">
                <x-icon name="receipt" class="h-4 w-4" /> Cari pesanan pelanggan
            </a>
        </div>

        <div class="grid gap-5 xl:grid-cols-[1fr_1.8fr]">
            {{-- Profile --}}
            <aside class="space-y-4">
                <div class="card overflow-hidden">
                    <div class="relative overflow-hidden bg-gradient-to-br from-ink-900 via-ink-900 to-brand-900 p-6 text-white">
                        <div class="pointer-events-none absolute -right-8 -top-8 h-32 w-32 rounded-full bg-brand-500/30 blur-2xl"></div>
                        <span class="relative flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-500 to-accent-500 text-xl font-extrabold shadow-lg">
                            {{ strtoupper(substr($customer->nama, 0, 1)) }}
                        </span>
                        <p class="relative mt-3 font-display text-lg font-bold">{{ $customer->nama }}</p>
                        <p class="relative text-xs text-indigo-200/70">@ {{ $customer->username }}</p>
                        <span class="relative mt-3 inline-flex items-center gap-1.5 rounded-full bg-emerald-400/15 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-emerald-300">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span> Akun pelanggan
                        </span>
                    </div>

                    <div class="divide-y divide-slate-100">
                        <div class="flex items-start gap-2.5 px-5 py-3.5">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600">
                                <x-icon name="mail" class="h-4 w-4" />
                            </span>
                            <div class="min-w-0 leading-tight">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Email</p>
                                <p class="truncate text-xs font-semibold text-slate-800">{{ $customer->email }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5 px-5 py-3.5">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600">
                                <x-icon name="phone" class="h-4 w-4" />
                            </span>
                            <div class="min-w-0 leading-tight">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">No. HP / WhatsApp</p>
                                <p class="truncate text-xs font-semibold text-slate-800">{{ $customer->no_hp }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-2.5 px-5 py-3.5">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600">
                                <x-icon name="map-pin" class="h-4 w-4" />
                            </span>
                            <div class="min-w-0 leading-tight">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Alamat</p>
                                <p class="text-xs font-semibold leading-relaxed text-slate-800">{{ $customer->alamat ?: 'Belum diisi' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                    <div class="card p-4">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                                <x-icon name="wallet" class="h-4.5 w-4.5" />
                            </span>
                            <div class="leading-tight">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total belanja (lunas)</p>
                                <p class="font-display text-base font-extrabold text-slate-900">Rp {{ number_format($totalSpent, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="card p-4">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                                <x-icon name="receipt" class="h-4.5 w-4.5" />
                            </span>
                            <div class="leading-tight">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total pesanan</p>
                                <p class="font-display text-base font-extrabold text-slate-900">{{ $totalOrders }} pesanan</p>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            {{-- Orders --}}
            <div class="card overflow-hidden">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-5">
                    <div>
                        <h3 class="font-display text-base font-bold text-slate-900">Riwayat pesanan</h3>
                        <p class="text-xs text-slate-400">Semua transaksi yang dibuat pelanggan ini.</p>
                    </div>
                    <span class="badge bg-slate-100 text-slate-500">{{ $orders->total() }} transaksi</span>
                </div>

                @if ($orders->isEmpty())
                    <div class="p-5">
                        <x-empty-state icon="receipt" title="Belum ada pesanan"
                                       message="Pelanggan ini belum pernah membuat pesanan." />
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="table-modern min-w-[700px]">
                            <thead>
                                <tr>
                                    <th>Kode / Tanggal</th>
                                    <th>Total</th>
                                    <th>Pembayaran</th>
                                    <th>Status</th>
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
                                        <td class="text-xs font-bold text-slate-800">Rp {{ number_format((int) $order->grand_total, 0, ',', '.') }}</td>
                                        <td><x-payment-badge :status="$order->status_pembayaran" /></td>
                                        <td><x-status-badge :status="$order->status_pesanan" /></td>
                                        <td class="text-right">
                                            <a href="{{ route('admin.orders.show', $order) }}"
                                               class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-brand-50 hover:text-brand-600" title="Detail pesanan">
                                                <x-icon name="eye" class="h-4 w-4" />
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($orders->hasPages())
                        <div class="border-t border-slate-100 px-4 py-3">{{ $orders->links() }}</div>
                    @endif
                @endif
            </div>
        </div>
    </div>
@endsection
