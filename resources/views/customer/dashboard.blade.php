@extends('layouts.dashboard')

@section('title', 'Dashboard Pelanggan')

@section('content')
    <div class="space-y-6">
        {{-- Greeting --}}
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-600 via-brand-500 to-indigo-600 p-6 text-white shadow-[0_30px_70px_-35px_rgba(67,56,202,0.75)] sm:p-8">
            <div class="pointer-events-none absolute -right-10 -top-10 h-48 w-48 rounded-full bg-white/15 blur-2xl"></div>
            <div class="pointer-events-none absolute -bottom-16 left-1/3 h-48 w-48 rounded-full bg-accent-400/25 blur-3xl"></div>

            <div class="relative flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <span class="badge bg-white/15 text-white backdrop-blur">
                        <x-icon name="sparkles" class="h-3.5 w-3.5 text-amber-300" /> Dashboard pelanggan
                    </span>
                    <h2 class="mt-3 font-display text-2xl font-extrabold sm:text-3xl">
                        Halo, {{ \Illuminate\Support\Str::before(auth()->user()->nama, ' ') }} 👋
                    </h2>
                    <p class="mt-1.5 max-w-xl text-sm leading-relaxed text-indigo-50/85">
                        Pantau pesanan cetak, status pembayaran, dan progres produksi Anda dalam satu tempat.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2.5">
                    <a href="{{ route('catalog.index') }}" class="btn !bg-white !text-brand-700 shadow-lg hover:-translate-y-0.5">
                        <x-icon name="plus" class="h-4.5 w-4.5" /> Pesan Cetak Baru
                    </a>
                    <a href="{{ route('saved.index') }}" class="btn border border-white/30 bg-white/10 !text-white hover:bg-white/20">
                        <x-icon name="bookmark" class="h-4.5 w-4.5" /> Produk Tersimpan
                    </a>
                    <a href="{{ route('customer.orders.index') }}" class="btn border border-white/30 bg-white/10 !text-white hover:bg-white/20">
                        <x-icon name="receipt" class="h-4.5 w-4.5" /> Semua Pesanan
                    </a>
                </div>
            </div>
        </div>

        @if (($cartCount ?? 0) > 0)
            <div class="flex flex-col gap-3 rounded-3xl border border-brand-200 bg-gradient-to-r from-brand-50 via-white to-white p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
                <div class="flex items-start gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-500 to-indigo-600 text-white shadow-lg shadow-brand-500/25">
                        <x-icon name="cart" class="h-5 w-5" />
                    </span>
                    <div>
                        <p class="text-sm font-bold text-slate-900">Progres pesanan tersimpan otomatis</p>
                        <p class="mt-0.5 text-xs text-slate-500">
                            {{ $cartCount }} item di keranjang · Rp {{ number_format($cartSubtotal, 0, ',', '.') }}
                            @if ($hasCheckoutDraft)
                                · draft checkout tersimpan
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('cart.index') }}" class="btn-white btn-sm">
                        <x-icon name="cart" class="h-4 w-4" /> Lanjutkan pesanan
                    </a>
                    <a href="{{ route('checkout.index') }}" class="btn-primary btn-sm">
                        <x-icon name="arrow-right" class="h-4 w-4" /> Lanjut ke checkout
                    </a>
                </div>
            </div>
        @endif

        {{-- Stats --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat-card label="Total pesanan" :value="$totalOrders" icon="receipt" accent="brand" hint="Seluruh pesanan Anda" />
            <x-stat-card label="Sedang diproses" :value="$activeOrders" icon="printer" accent="violet" :hint="$waitingPayment > 0 ? $waitingPayment.' menunggu pembayaran' : 'Semua pembayaran beres'" />
            <x-stat-card label="Total belanja" :value="$totalSpent" icon="wallet" accent="emerald" format="currency" hint="Pesanan yang sudah lunas" />
            <x-stat-card label="Status pembayaran" :value="$waitingPayment > 0 ? $waitingPayment.' belum lunas' : 'Semua lunas'" icon="shield" accent="sky" hint="Diperbarui otomatis" />
        </div>

        <div class="grid gap-5 xl:grid-cols-[1.5fr_1fr]">
            {{-- Active order --}}
            <div class="card overflow-hidden">
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 p-5">
                    <div>
                        <h3 class="font-display text-base font-bold text-slate-900">Pesanan aktif</h3>
                        <p class="text-xs text-slate-400">Progres produksi pesanan terbaru Anda.</p>
                    </div>
                    @if ($activeOrder)
                        <x-status-badge :status="$activeOrder->status_pesanan" />
                    @endif
                </div>

                @if ($activeOrder)
                    <div class="p-5">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <p class="font-mono text-xs font-bold text-brand-600">{{ $activeOrder->kode_pesanan }}</p>
                                <p class="mt-0.5 text-sm font-bold text-slate-900">
                                    {{ $activeOrder->detail->first()?->nama_produk_snapshot ?? 'Pesanan cetak' }}
                                    @if ($activeOrder->detail->count() > 1)
                                        <span class="text-xs font-medium text-slate-400">+{{ $activeOrder->detail->count() - 1 }} item lain</span>
                                    @endif
                                </p>
                                <p class="mt-0.5 text-[11px] text-slate-400">
                                    Dibuat {{ $activeOrder->tgl_pesan->translatedFormat('d M Y') }}
                                    · Estimasi {{ $activeOrder->estimasi_selesai?->translatedFormat('d M Y') ?? '-' }}
                                </p>
                            </div>
                            <p class="font-display text-lg font-extrabold text-slate-900">Rp {{ number_format((int) $activeOrder->grand_total, 0, ',', '.') }}</p>
                        </div>

                        <div class="mt-5 h-2 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-gradient-to-r from-brand-500 to-accent-500"
                                 style="width: {{ (($activeOrder->tahapanAktif() + 1) / count(\App\Models\Order::TAHAPAN)) * 100 }}%"></div>
                        </div>

                        <div class="mt-3 flex items-center justify-between text-[10px] font-semibold text-slate-400">
                            <span>{{ $activeOrder->status_pesanan }}</span>
                            <span>Tahap {{ $activeOrder->tahapanAktif() + 1 }}/{{ count(\App\Models\Order::TAHAPAN) }}</span>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            @if ($activeOrder->status_pesanan === \App\Models\Order::STATUS_MENUNGGU)
                                <a href="{{ route('payments.show', $activeOrder) }}" class="btn-primary btn-sm">
                                    <x-icon name="credit-card" class="h-4 w-4" /> Selesaikan Pembayaran
                                </a>
                            @endif
                            <a href="{{ route('customer.orders.show', $activeOrder) }}" class="btn-white btn-sm">
                                <x-icon name="eye" class="h-4 w-4" /> Detail pesanan
                            </a>
                            <a href="{{ route('tracking.show', $activeOrder->kode_pesanan) }}" class="btn-ghost btn-sm">
                                <x-icon name="search" class="h-4 w-4" /> Lacak
                            </a>
                        </div>
                    </div>
                @else
                    <div class="p-5">
                        <x-empty-state icon="package" title="Belum ada pesanan aktif"
                                       message="Saat ini tidak ada pesanan yang sedang diproses. Yuk mulai pesan cetak pertama Anda!">
                            <a href="{{ route('catalog.index') }}" class="btn-primary btn-sm mt-1">
                                <x-icon name="plus" class="h-4 w-4" /> Lihat katalog
                            </a>
                        </x-empty-state>
                    </div>
                @endif
            </div>

            {{-- Spending chart --}}
            <div class="card p-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="font-display text-base font-bold text-slate-900">Pengeluaran 6 bulan</h3>
                        <p class="text-xs text-slate-400">Total transaksi lunas per bulan.</p>
                    </div>
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                        <x-icon name="chart" class="h-4.5 w-4.5" />
                    </span>
                </div>

                <div class="mt-2"
                     data-chart="{{ json_encode([
                        'type' => 'bar',
                        'labels' => $spendLabels,
                        'values' => $spendValues,
                        'height' => 232,
                        'name' => 'Pengeluaran',
                        'distributed' => true,
                     ]) }}"></div>
            </div>
        </div>

        {{-- Recent orders --}}
        <div class="card overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-5">
                <div>
                    <h3 class="font-display text-base font-bold text-slate-900">Pesanan terbaru</h3>
                    <p class="text-xs text-slate-400">Lima transaksi terakhir Anda.</p>
                </div>
                <a href="{{ route('customer.orders.index') }}" class="btn-ghost btn-sm">
                    Lihat semua <x-icon name="arrow-right" class="h-4 w-4" />
                </a>
            </div>

            @if ($recentOrders->isEmpty())
                <div class="p-5">
                    <x-empty-state icon="receipt" title="Belum ada riwayat pesanan" message="Pesanan yang Anda buat akan tampil di sini." />
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="table-modern min-w-[680px]">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Tanggal</th>
                                <th>Total</th>
                                <th>Pembayaran</th>
                                <th>Status</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentOrders as $order)
                                <tr>
                                    <td class="font-mono text-xs font-bold text-brand-600">{{ $order->kode_pesanan }}</td>
                                    <td class="text-xs text-slate-500">{{ $order->tgl_pesan->translatedFormat('d M Y') }}</td>
                                    <td class="text-xs font-bold text-slate-800">Rp {{ number_format((int) $order->grand_total, 0, ',', '.') }}</td>
                                    <td><x-payment-badge :status="$order->status_pembayaran" /></td>
                                    <td><x-status-badge :status="$order->status_pesanan" /></td>
                                    <td class="text-right">
                                        <a href="{{ route('customer.orders.show', $order) }}"
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
    </div>

    <div data-welcome="customer-{{ auth()->id() }}" data-welcome-title="Selamat datang, {{ \Illuminate\Support\Str::before(auth()->user()->nama, ' ') }}! 🎉" class="hidden">
        <p class="text-indigo-100/85">Senang melihat Anda kembali. Berikut ringkasan cepat akun Anda:</p>
        <div class="mt-4 grid grid-cols-2 gap-2 text-left">
            <div class="rounded-xl bg-white/10 px-3 py-2.5">
                <p class="text-[10px] uppercase tracking-wider text-indigo-200/70">Total pesanan</p>
                <p class="font-display text-lg font-bold text-white">{{ $totalOrders }}</p>
            </div>
            <div class="rounded-xl bg-white/10 px-3 py-2.5">
                <p class="text-[10px] uppercase tracking-wider text-indigo-200/70">Total belanja</p>
                <p class="font-display text-lg font-bold text-white">Rp {{ number_format($totalSpent, 0, ',', '.') }}</p>
            </div>
        </div>
        <a href="{{ route('catalog.index') }}" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white px-4 py-3 text-sm font-bold text-brand-700 transition hover:bg-indigo-50">
            Pesan cetak baru <x-icon name="arrow-right" class="h-4 w-4" />
        </a>
    </div>
@endsection
