@extends('layouts.app')

@section('title', 'Pembayaran ' . $order->kode_pesanan)

@php
    $paid = $order->isPaid();
    $hasToken = filled($order->snap_token);
    $isExpired = $expiresAt->isPast();
    $metode = $order->metode_pengambilan;

    $gatewayLabel = match ($transaction?->transaction_status) {
        'settlement', 'capture' => 'Lunas',
        'pending' => 'Menunggu pembayaran',
        'expire' => 'Kadaluarsa',
        'deny', 'failure' => 'Gagal',
        'cancel' => 'Dibatalkan',
        'refund', 'partial_refund' => 'Dana dikembalikan',
        'chargeback', 'partial_chargeback' => 'Chargeback',
        default => 'Menunggu pembayaran',
    };

    $steps = [
        ['label' => 'Pesanan dibuat', 'icon' => 'receipt', 'state' => 'done'],
        ['label' => 'Pembayaran', 'icon' => 'credit-card', 'state' => $paid ? 'done' : 'active'],
        ['label' => 'Verifikasi otomatis', 'icon' => 'shield', 'state' => $paid ? 'done' : 'next'],
        ['label' => 'Masuk produksi', 'icon' => 'printer', 'state' => $paid ? 'active' : 'next'],
    ];
@endphp

@section('content')
    {{-- Header --}}
    <section class="relative overflow-hidden border-b border-slate-200/70 bg-white">
        <div class="pointer-events-none absolute inset-0 bg-mesh opacity-70"></div>
        <div class="container-page relative py-6">
            <nav class="flex flex-wrap items-center gap-2 text-[11px] font-semibold text-slate-400">
                <a href="{{ route('customer.orders.index') }}" class="hover:text-brand-600">Pesanan Saya</a>
                <x-icon name="chevron-right" class="h-3 w-3" />
                <span class="text-slate-700">{{ $order->kode_pesanan }}</span>
            </nav>
            <div class="mt-3 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 class="font-display text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">
                        {{ $paid ? 'Pembayaran Berhasil' : 'Selesaikan Pembayaran' }}
                    </h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Kode pesanan <span class="font-bold text-slate-800">{{ $order->kode_pesanan }}</span>
                        · {{ $order->tgl_pesan->translatedFormat('d F Y, H:i') }} WIB
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="badge bg-white text-slate-600 ring-1 ring-inset ring-slate-200">
                        <x-icon name="shield" class="h-3.5 w-3.5 text-emerald-500" /> Midtrans Snap
                    </span>
                    <x-payment-badge :status="$order->status_pembayaran" class="!px-3 !py-1.5 !text-xs" />
                </div>
            </div>
        </div>
    </section>

    <section class="container-page py-8 pb-28 lg:pb-10">
        <div class="grid gap-6 lg:grid-cols-[1.55fr_1fr]">

            {{-- ============ PANEL PEMBAYARAN ============ --}}
            <div class="space-y-5">
                @if ($paid)
                    {{-- Lunas --}}
                    <div class="animate-pop relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-500 via-emerald-500 to-teal-600 p-6 text-white shadow-[0_30px_70px_-30px_rgba(16,185,129,0.6)] sm:p-8">
                        <div class="blob -right-8 -top-8 h-40 w-40 bg-white/20"></div>
                        <div class="blob -bottom-10 left-1/4 h-40 w-40 bg-teal-300/25"></div>

                        <span class="relative flex h-14 w-14 items-center justify-center rounded-2xl bg-white/20 shadow-inner backdrop-blur">
                            <x-icon name="check-circle" class="h-7 w-7" />
                        </span>
                        <h2 class="relative mt-4 font-display text-2xl font-extrabold">Terima kasih, pembayaran lunas! 🎉</h2>
                        <p class="relative mt-2 max-w-lg text-sm leading-relaxed text-emerald-50/90">
                            Pesanan Anda sudah masuk antrean produksi workshop. Kami akan memperbarui status
                            secara bertahap sampai siap diambil atau dikirim.
                        </p>

                        <div class="relative mt-5 grid gap-3 sm:grid-cols-2">
                            <div class="rounded-2xl bg-white/15 px-4 py-3 backdrop-blur">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-50/70">Estimasi selesai</p>
                                <p class="mt-0.5 font-display text-lg font-bold">{{ $order->estimasi_selesai?->translatedFormat('d F Y') ?? '-' }}</p>
                            </div>
                            <div class="rounded-2xl bg-white/15 px-4 py-3 backdrop-blur">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-50/70">Metode pengambilan</p>
                                <p class="mt-0.5 font-display text-lg font-bold">{{ $metode }}</p>
                            </div>
                        </div>

                        <div class="relative mt-5 flex flex-wrap gap-2.5">
                            <a href="{{ route('orders.invoice', $order) }}" target="_blank"
                               class="btn !bg-white !text-emerald-700 shadow-lg hover:-translate-y-0.5">
                                <x-icon name="print" class="h-4.5 w-4.5" /> Cetak Invoice
                            </a>
                            <a href="{{ route('customer.orders.show', $order) }}"
                               class="btn border border-white/30 bg-white/10 !text-white hover:bg-white/20">
                                <x-icon name="receipt" class="h-4.5 w-4.5" /> Detail Pesanan
                            </a>
                            <a href="{{ route('catalog.index') }}"
                               class="btn border border-white/30 bg-white/10 !text-white hover:bg-white/20">
                                <x-icon name="plus" class="h-4.5 w-4.5" /> Pesan Lagi
                            </a>
                        </div>

                        <div data-payment-success
                             data-order-code="{{ $order->kode_pesanan }}"
                             data-invoice-url="{{ route('orders.invoice', $order) }}"
                             data-estimate="{{ $order->estimasi_selesai?->translatedFormat('d F Y') ?? '-' }}"></div>
                    </div>

                    {{-- Timeline --}}
                    <div class="card p-5 sm:p-6">
                        <h2 class="font-display text-base font-bold text-slate-900">Progres pesanan</h2>
                        <p class="mt-0.5 text-xs text-slate-400">Setiap perubahan status dicatat otomatis oleh sistem.</p>

                        <div class="mt-5 space-y-5">
                            @foreach ($order->riwayat as $riwayat)
                                <div class="relative flex gap-4">
                                    <span class="timeline-dot {{ $loop->last ? 'timeline-dot-active' : '' }}">
                                        @if ($riwayat->status_tahapan === 'Pembayaran Lunas')
                                            <x-icon name="wallet" class="h-4 w-4" />
                                        @else
                                            <x-icon name="check" class="h-4 w-4" />
                                        @endif
                                    </span>
                                    <div class="-mt-0.5 flex-1">
                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <p class="text-sm font-bold text-slate-800">{{ $riwayat->status_tahapan }}</p>
                                            <p class="text-[10px] font-semibold text-slate-400">{{ $riwayat->created_at->translatedFormat('d M Y, H:i') }}</p>
                                        </div>
                                        <p class="mt-0.5 text-xs leading-relaxed text-slate-500">{{ $riwayat->keterangan }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                @elseif ($configured)
                    {{-- ===== HERO PEMBAYARAN ===== --}}
                    <div class="relative overflow-hidden rounded-3xl bg-ink-950 text-white shadow-[0_30px_80px_-30px_rgba(30,27,75,0.65)]">
                        <div class="animate-gradient absolute inset-0 bg-[linear-gradient(120deg,#15152b_0%,#2f287a_38%,#4f46e5_72%,#06b6d4_100%)] opacity-95"></div>
                        <div class="blob -right-10 -top-12 h-48 w-48 bg-accent-400/25"></div>
                        <div class="blob -bottom-16 -left-8 h-48 w-48 bg-brand-400/25"></div>

                        <div class="relative p-6 sm:p-8">
                            <div class="flex flex-wrap items-start justify-between gap-5">
                                <div class="min-w-0">
                                    <span class="badge bg-white/10 text-white ring-1 ring-inset ring-white/20 backdrop-blur">
                                        <span class="relative flex h-2 w-2">
                                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-300 opacity-80"></span>
                                            <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-400"></span>
                                        </span>
                                        Pembayaran aman · {{ $setting->modeLabel() }}
                                    </span>

                                    <p class="mt-4 text-[10px] font-bold uppercase tracking-[0.2em] text-white/60">Total tagihan</p>
                                    <p class="mt-1 font-display text-4xl font-extrabold tracking-tight sm:text-5xl">
                                        Rp {{ number_format((int) $order->grand_total, 0, ',', '.') }}
                                    </p>
                                    <p class="mt-2 text-xs text-indigo-100/80">
                                        {{ $setting->displayName() }} · {{ $order->kode_pesanan }}
                                    </p>
                                </div>

                                <div x-data="paymentCountdown(@js($expiresAt->toIso8601String()))" class="w-full max-w-[190px] rounded-2xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur sm:w-auto">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-white/60">Bayar sebelum</p>
                                    <p class="mt-1 font-mono text-2xl font-bold tabular-nums" x-text="label">--:--:--</p>
                                    <p class="mt-1 text-[10px] text-white/50">{{ $expiresAt->translatedFormat('d M Y, H:i') }} WIB</p>
                                    <p class="mt-1 text-[10px] font-semibold text-amber-200" x-show="expired" x-cloak>Sesi kedaluwarsa — muat ulang sesi.</p>
                                </div>
                            </div>

                            {{-- QR dekoratif & langkah --}}
                            <div class="mt-6 grid gap-4 sm:grid-cols-[auto_1fr] sm:items-center">
                                <div class="animate-float relative mx-auto flex h-24 w-24 items-center justify-center rounded-2xl bg-white/10 p-2 ring-1 ring-inset ring-white/20 backdrop-blur sm:mx-0">
                                    <div class="grid grid-cols-6 gap-0.5">
                                        @for ($i = 0; $i < 36; $i++)
                                            <span class="h-1.5 w-1.5 rounded-[1px] {{ $i % 3 === 0 ? 'bg-white' : ($i % 4 === 0 ? 'bg-white/20' : 'bg-white/70') }}"></span>
                                        @endfor
                                    </div>
                                </div>

                                <div class="grid gap-2 sm:grid-cols-2">
                                    @foreach ($steps as $step)
                                        <div class="flex items-center gap-2 rounded-xl px-2 py-1.5 {{ $step['state'] === 'active' ? 'bg-white/10' : '' }}">
                                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full {{ $step['state'] === 'done' ? 'bg-emerald-400/20 text-emerald-200' : ($step['state'] === 'active' ? 'bg-white text-brand-700' : 'bg-white/10 text-white/50') }}">
                                                <x-icon :name="$step['icon']" class="h-3.5 w-3.5" />
                                            </span>
                                            <span class="text-[11px] font-semibold {{ $step['state'] === 'next' ? 'text-white/50' : 'text-white/90' }}">{{ $step['label'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mt-6 flex flex-wrap items-center gap-x-4 gap-y-2 text-[10px] font-semibold text-white/55">
                                <span class="flex items-center gap-1.5"><x-icon name="lock" class="h-3.5 w-3.5 text-emerald-300" /> Enkripsi SSL</span>
                                <span class="flex items-center gap-1.5"><x-icon name="shield" class="h-3.5 w-3.5 text-emerald-300" /> Verifikasi otomatis</span>
                                <span class="flex items-center gap-1.5"><x-icon name="refresh" class="h-3.5 w-3.5 text-emerald-300" /> Status real-time</span>
                            </div>
                        </div>
                    </div>

                    {{-- ===== METODE PEMBAYARAN ===== --}}
                    <div class="card p-5 sm:p-6">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h2 class="font-display text-base font-bold text-slate-900">Pilih metode pembayaran</h2>
                                <p class="mt-0.5 text-xs text-slate-400">Klik salah satu metode untuk membuka jendela pembayaran Midtrans.</p>
                            </div>
                            <span class="badge bg-emerald-50 text-emerald-700">
                                <x-icon name="check-circle" class="h-3.5 w-3.5" /> Semua kanal aktif
                            </span>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-2.5 sm:grid-cols-3">
                            @foreach ($channels as $channel)
                                <button type="button" data-pay-trigger
                                        class="group relative flex items-center gap-2.5 overflow-hidden rounded-2xl border border-slate-200 bg-white px-3 py-3 text-left transition-all duration-300 hover:-translate-y-0.5 hover:border-brand-300 hover:shadow-md hover:shadow-brand-500/10">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-50 text-slate-500 transition group-hover:bg-brand-50 group-hover:text-brand-600">
                                        <x-icon :name="$channel['icon']" class="h-4 w-4" />
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-[11px] font-bold text-slate-800">{{ $channel['label'] }}</span>
                                        <span class="block truncate text-[10px] text-slate-400">{{ $channel['note'] }}</span>
                                    </span>
                                    <x-icon name="arrow-right" class="h-3.5 w-3.5 shrink-0 text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-brand-500" />
                                </button>
                            @endforeach
                        </div>

                        <div class="mt-5 flex items-start gap-2.5 rounded-2xl border border-brand-100 bg-brand-50/60 px-4 py-3">
                            <x-icon name="info" class="mt-0.5 h-4 w-4 shrink-0 text-brand-600" />
                            <p class="text-[11px] leading-relaxed text-brand-700/85">
                                Nama toko pada QRIS dan halaman Snap: <b>{{ $setting->displayName() }}</b>.
                                Setelah jendela pembayaran terbuka, pilih kanal lalu ikuti instruksi (scan QRIS,
                                transfer VA, atau bayar di gerai).
                            </p>
                        </div>

                        @if ($isExpired)
                            <div class="mt-4 flex items-start gap-2.5 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3">
                                <x-icon name="clock" class="mt-0.5 h-4 w-4 shrink-0 text-amber-600" />
                                <p class="text-[11px] leading-relaxed text-amber-700">
                                    Sesi pembayaran sebelumnya sudah kedaluwarsa. Klik tombol di bawah untuk membuat sesi baru.
                                </p>
                            </div>
                        @endif

                        @if ($hasToken && ! $isExpired)
                            <button type="button" id="pay-button" class="btn-primary shine mt-4 w-full !py-4 text-base shadow-xl shadow-brand-500/25">
                                <x-icon name="credit-card" class="h-5 w-5" />
                                Bayar Sekarang — Rp {{ number_format((int) $order->grand_total, 0, ',', '.') }}
                            </button>
                        @else
                            <button type="button" id="refresh-token" class="btn-primary mt-4 w-full !py-4">
                                <x-icon name="refresh" class="h-5 w-5" /> Buat Sesi Pembayaran
                            </button>
                            <p class="mt-2 text-center text-[11px] text-slate-400">Sesi pembayaran {{ $isExpired ? 'sudah kedaluwarsa' : 'belum tersedia' }}. Klik untuk membuat sesi baru.</p>
                        @endif

                        <div class="mt-4 flex items-center justify-center gap-2 text-[11px] font-semibold text-slate-400">
                            <x-icon name="lock" class="h-3.5 w-3.5 text-emerald-500" />
                            Transaksi diproses &amp; diversifikasi oleh Midtrans
                        </div>
                    </div>

                    {{-- Monitoring --}}
                    <div data-payment-poll="{{ route('payments.status', $order) }}"
                         class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3">
                        <div class="flex items-center gap-2.5">
                            <span class="relative flex h-2.5 w-2.5">
                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                            </span>
                            <div class="leading-tight">
                                <p class="text-xs font-bold text-slate-800">Memantau status pembayaran</p>
                                <p class="text-[10px] text-slate-400">Halaman diperbarui otomatis setelah pembayaran terverifikasi.</p>
                            </div>
                        </div>
                        <span class="badge bg-slate-100 text-slate-500">Live</span>
                    </div>

                @else
                    {{-- ===== MODE SIMULASI ===== --}}
                    <div class="overflow-hidden rounded-3xl border border-amber-200 bg-white shadow-[var(--shadow-soft)]">
                        <div class="relative overflow-hidden bg-gradient-to-br from-amber-400 via-amber-500 to-orange-500 p-6 text-amber-950 sm:p-8">
                            <div class="blob -right-8 -top-10 h-40 w-40 bg-white/25"></div>
                            <span class="badge relative bg-white/40 text-amber-950 backdrop-blur">
                                <x-icon name="info" class="h-3.5 w-3.5" /> Mode Demo / Simulasi
                            </span>
                            <p class="relative mt-3 text-[10px] font-bold uppercase tracking-[0.18em] text-amber-900/70">Total tagihan</p>
                            <p class="relative mt-1 font-display text-4xl font-extrabold">Rp {{ number_format((int) $order->grand_total, 0, ',', '.') }}</p>
                            <p class="relative mt-2 max-w-lg text-xs leading-relaxed text-amber-900/80">
                                Belum ada mode Midtrans aktif. Untuk keperluan demo &amp; pengujian skripsi,
                                pembayaran dapat disimulasikan di bawah ini.
                            </p>
                        </div>

                        <div class="p-6 sm:p-8">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-2.5">
                                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-slate-700 shadow-sm">
                                            <x-icon name="qr" class="h-5 w-5" />
                                        </span>
                                        <div class="leading-tight">
                                            <p class="text-xs font-bold text-slate-800">QRIS Simulasi</p>
                                            <p class="text-[10px] text-slate-400">Pindai tidak diperlukan pada mode demo</p>
                                        </div>
                                    </div>
                                    <span class="badge bg-amber-100 text-amber-700">Menunggu</span>
                                </div>
                                <div class="mt-3 grid grid-cols-3 gap-1 rounded-lg bg-white p-2">
                                    @for ($i = 0; $i < 24; $i++)
                                        <span class="h-2 rounded-sm {{ $i % 3 === 0 ? 'bg-slate-800' : ($i % 2 === 0 ? 'bg-slate-300' : 'bg-slate-500') }}"></span>
                                    @endfor
                                </div>
                            </div>

                            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                                <form method="POST" action="{{ route('payments.simulate', $order) }}"
                                      data-confirm="Simulasi pembayaran berhasil akan menandai pesanan sebagai LUNAS dan masuk antrean produksi."
                                      data-confirm-button="Ya, bayar sekarang" data-confirm-icon="success"
                                      data-confirm-title="Simulasikan pembayaran berhasil?">
                                    @csrf
                                    <input type="hidden" name="hasil" value="sukses">
                                    <button type="submit" class="btn-primary w-full !bg-gradient-to-r !from-emerald-500 !to-teal-500 !py-3.5 shadow-lg shadow-emerald-500/25">
                                        <x-icon name="check-circle" class="h-5 w-5" /> Simulasi Berhasil
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('payments.simulate', $order) }}"
                                      data-confirm="Simulasi pembayaran gagal akan menandai pembayaran sebagai GAGAL."
                                      data-confirm-button="Ya, simulasikan gagal" data-confirm-icon="error"
                                      data-confirm-title="Simulasikan pembayaran gagal?">
                                    @csrf
                                    <input type="hidden" name="hasil" value="gagal">
                                    <button type="submit" class="btn-danger w-full !bg-gradient-to-r !from-rose-500 !to-pink-500 !py-3.5">
                                        <x-icon name="close" class="h-5 w-5" /> Simulasi Gagal
                                    </button>
                                </form>
                            </div>

                            <p class="mt-4 text-center text-[11px] leading-relaxed text-slate-400">
                                Admin dapat mengaktifkan Midtrans pada menu <b>Pembayaran</b> agar pembayaran asli
                                aktif tanpa mengubah kode program.
                            </p>
                        </div>
                    </div>

                    <div data-payment-poll="{{ route('payments.status', $order) }}"></div>
                @endif

                {{-- ===== DETAIL TRANSAKSI ===== --}}
                @if ($transaction)
                    <div class="card p-5 sm:p-6">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h2 class="font-display text-base font-bold text-slate-900">Detail transaksi</h2>
                            <span class="badge {{ $transaction->isSettled() ? 'bg-emerald-50 text-emerald-700' : ($transaction->transaction_status === 'pending' ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700') }}">
                                {{ $gatewayLabel }}
                            </span>
                        </div>

                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            <div class="rounded-xl bg-slate-50 px-4 py-3">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Metode</p>
                                <p class="mt-0.5 text-sm font-semibold uppercase text-slate-800">{{ $transaction->payment_type ?? 'qris' }}</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 px-4 py-3">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Status gateway</p>
                                <p class="mt-0.5 text-sm font-semibold uppercase text-slate-800">{{ $transaction->transaction_status }}</p>
                            </div>
                            @if ($transaction->va_number)
                                <div class="rounded-xl bg-slate-50 px-4 py-3 sm:col-span-2">
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Nomor Virtual Account</p>
                                    <div class="mt-1 flex items-center gap-2">
                                        <p class="font-mono text-base font-bold tracking-wider text-slate-800">{{ $transaction->va_number }}</p>
                                        <button type="button" data-copy="{{ $transaction->va_number }}"
                                                class="rounded-lg p-1 text-slate-400 transition hover:bg-white hover:text-brand-600" title="Salin nomor">
                                            <x-icon name="clipboard" class="h-4 w-4" />
                                        </button>
                                    </div>
                                </div>
                            @endif
                            @if ($transaction->qr_code_url)
                                <div class="flex items-center gap-3 rounded-xl bg-slate-50 px-4 py-3 sm:col-span-2">
                                    <img src="{{ $transaction->qr_code_url }}" alt="QRIS pembayaran"
                                         class="h-24 w-24 rounded-lg bg-white p-1 shadow-sm">
                                    <div>
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">QRIS</p>
                                        <p class="mt-1 text-xs text-slate-500">Pindai kode QR ini dengan aplikasi bank atau e-wallet.</p>
                                    </div>
                                </div>
                            @endif
                            <div class="rounded-xl bg-slate-50 px-4 py-3">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">ID Transaksi</p>
                                <p class="mt-0.5 truncate font-mono text-xs font-semibold text-slate-700">{{ $transaction->transaction_id_gateway ?? '-' }}</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 px-4 py-3">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Waktu pembayaran</p>
                                <p class="mt-0.5 text-sm font-semibold text-slate-800">{{ $transaction->payment_time?->translatedFormat('d M Y, H:i') ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- ============ RINGKASAN PESANAN ============ --}}
            <aside class="sticky-summary space-y-4">
                @if (! $paid && $configured)
                    <div class="rounded-2xl border border-amber-200 bg-gradient-to-br from-amber-50 to-white p-4">
                        <div class="flex items-center justify-between gap-2">
                            <p class="flex items-center gap-1.5 text-xs font-bold text-amber-800">
                                <x-icon name="clock" class="h-4 w-4" /> Batas waktu pembayaran
                            </p>
                            <x-icon name="lock" class="h-4 w-4 text-amber-500" />
                        </div>
                        <div x-data="paymentCountdown(@js($expiresAt->toIso8601String()))" class="mt-2">
                            <p class="font-mono text-3xl font-extrabold tabular-nums text-amber-900" x-text="label">--:--:--</p>
                            <p class="mt-1 text-[11px] text-amber-700/80">{{ $expiresAt->translatedFormat('d F Y, H:i') }} WIB</p>
                        </div>
                    </div>
                @endif

                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[var(--shadow-card)]">
                    <div class="border-b border-slate-100 p-5">
                        <div class="flex items-center justify-between gap-2">
                            <h2 class="font-display text-base font-bold text-slate-900">Rincian pesanan</h2>
                            <span class="badge bg-slate-100 text-slate-500">{{ $order->detail->count() }} item</span>
                        </div>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @foreach ($order->detail as $detail)
                            <div class="flex gap-3 p-4">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                                    <x-icon name="printer" class="h-4.5 w-4.5" />
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-bold text-slate-800">{{ $detail->nama_produk_snapshot }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $detail->nama_bahan_snapshot }}</p>
                                    <p class="mt-0.5 text-[10px] text-slate-400">
                                        {{ $detail->satuan_snapshot === 'meter' ? $detail->ukuranTeks().' · '.$detail->jumlah.' pcs' : $detail->jumlah.' '.$detail->satuan_snapshot }}
                                        @if ($detail->finishing->isNotEmpty())
                                            · {{ $detail->finishing->pluck('nama_finishing_snapshot')->join(', ') }}
                                        @endif
                                    </p>
                                </div>
                                <p class="shrink-0 text-xs font-bold text-slate-800">Rp {{ number_format((int) $detail->subtotal, 0, ',', '.') }}</p>
                            </div>
                        @endforeach
                    </div>

                    <div class="space-y-2 border-t border-slate-100 p-5 text-sm">
                        <div class="flex items-center justify-between text-slate-500">
                            <span>Total produk</span>
                            <span class="font-semibold text-slate-800">Rp {{ number_format((int) $order->total_produk, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-500">
                            <span>Ongkos kirim</span>
                            <span class="font-semibold text-slate-800">{{ (float) $order->ongkir > 0 ? 'Rp '.number_format((int) $order->ongkir, 0, ',', '.') : 'Gratis' }}</span>
                        </div>
                        <div class="flex items-end justify-between border-t border-dashed border-slate-200 pt-3">
                            <span class="font-display text-sm font-bold text-slate-900">Grand total</span>
                            <span class="font-display text-xl font-extrabold text-brand-600">Rp {{ number_format((int) $order->grand_total, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <h3 class="flex items-center gap-1.5 text-xs font-bold text-slate-800">
                        <x-icon name="map-pin" class="h-4 w-4 text-brand-500" /> {{ $metode }}
                    </h3>
                    <p class="mt-1.5 text-[11px] leading-relaxed text-slate-500">
                        {{ $order->alamat_pengiriman ?: $storeSetting->alamat }}
                    </p>
                    @if ($order->catatan)
                        <div class="mt-3 rounded-xl bg-slate-50 px-3 py-2">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Catatan</p>
                            <p class="mt-0.5 text-[11px] text-slate-600">{{ $order->catatan }}</p>
                        </div>
                    @endif
                </div>

                <div class="rounded-2xl border border-brand-100 bg-brand-50/60 p-5">
                    <p class="flex items-center gap-1.5 text-xs font-bold text-brand-800">
                        <x-icon name="whatsapp" class="h-4 w-4" /> Butuh bantuan?
                    </p>
                    <p class="mt-1 text-[11px] leading-relaxed text-brand-700/80">
                        Hubungi {{ $storeSetting->nama_perusahaan }} di {{ $storeSetting->no_telepon }}
                        jika pembayaran Anda belum terverifikasi.
                    </p>
                </div>

                <a href="{{ route('tracking.show', $order->kode_pesanan) }}"
                   class="flex items-center justify-between gap-2 rounded-2xl border border-brand-100 bg-white px-4 py-3 text-xs font-bold text-brand-700 transition hover:bg-brand-50">
                    <span class="flex items-center gap-2"><x-icon name="search" class="h-4 w-4" /> Lacak pesanan publik</span>
                    <x-icon name="arrow-right" class="h-4 w-4" />
                </a>
            </aside>
        </div>
    </section>

    {{-- CTA lengket versi mobile --}}
    @if (! $paid && $configured)
        <div class="fixed inset-x-0 bottom-0 z-30 border-t border-slate-200 bg-white/95 px-4 py-3 backdrop-blur-xl lg:hidden">
            <div class="mx-auto flex max-w-2xl items-center gap-3">
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total tagihan</p>
                    <p class="truncate font-display text-lg font-extrabold text-slate-900">Rp {{ number_format((int) $order->grand_total, 0, ',', '.') }}</p>
                </div>
                <button type="button" data-pay-trigger class="btn-primary shrink-0 !px-6 !py-3">
                    <x-icon name="credit-card" class="h-4.5 w-4.5" /> Bayar
                </button>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    @if ($configured && ! $paid)
        <script src="{{ $setting->snapBaseUrl() }}" data-client-key="{{ $setting->client_key }}"></script>
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('paymentCountdown', (target) => ({
                    remaining: 0,
                    expired: false,
                    timer: null,
                    init() {
                        this.update();
                        this.timer = setInterval(() => this.update(), 1000);
                    },
                    update() {
                        const diff = new Date(target).getTime() - Date.now();
                        this.remaining = Math.max(0, diff);
                        this.expired = diff <= 0;
                    },
                    get hours() {
                        return Math.floor(this.remaining / 3600000);
                    },
                    get minutes() {
                        return Math.floor((this.remaining % 3600000) / 60000);
                    },
                    get seconds() {
                        return Math.floor((this.remaining % 60000) / 1000);
                    },
                    get label() {
                        const pad = (value) => String(value).padStart(2, '0');

                        return `${pad(this.hours)}:${pad(this.minutes)}:${pad(this.seconds)}`;
                    },
                }));
            });

            (() => {
                const finishUrl = @json(route('payments.finish', $order));
                const tokenUrl = @json(route('payments.snap-token', $order));
                const statusUrl = @json(route('payments.status', $order));
                let snapToken = @json($isExpired ? null : $order->snap_token);

                const fail = (message) => {
                    if (window.Swal) {
                        window.Swal.fire({
                            icon: 'error',
                            title: 'Pembayaran gagal dimulai',
                            text: message,
                            confirmButtonText: 'Tutup',
                            customClass: { popup: 'swal-neo' },
                            showClass: { popup: 'swal-pop-in' },
                        });
                    } else {
                        alert(message);
                    }
                };

                const openSnap = (token) => {
                    if (! window.snap) {
                        fail('Skrip Midtrans belum termuat. Periksa koneksi internet Anda.');

                        return;
                    }

                    window.snap.pay(token, {
                        onSuccess: () => { window.location.href = finishUrl; },
                        onPending: () => { window.location.href = finishUrl; },
                        onError: () => { fail('Transaksi tidak dapat diproses. Silakan coba metode pembayaran lain.'); },
                        onClose: async () => {
                            try {
                                const response = await fetch(statusUrl, { headers: { Accept: 'application/json' } });
                                const data = await response.json();

                                if (data.paid) {
                                    window.location.href = finishUrl;

                                    return;
                                }
                            } catch (error) {
                                // abaikan bila koneksi sedang tidak stabil
                            }

                            if (window.Swal) {
                                window.Swal.fire({
                                    icon: 'info',
                                    title: 'Pembayaran belum selesai',
                                    text: 'Anda menutup jendela pembayaran sebelum transaksi selesai. Pesanan masih menunggu pembayaran.',
                                    confirmButtonText: 'Coba lagi',
                                    customClass: { popup: 'swal-neo' },
                                });
                            }
                        },
                    });
                };

                const setBusy = (busy) => {
                    document.querySelectorAll('[data-pay-trigger], #pay-button, #refresh-token').forEach((button) => {
                        button.disabled = busy;
                        button.classList.toggle('opacity-60', busy);
                        button.classList.toggle('cursor-not-allowed', busy);
                    });
                };

                const refreshToken = async () => {
                    setBusy(true);

                    try {
                        const response = await fetch(tokenUrl, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                        });

                        const data = await response.json();

                        if (! response.ok || ! data.token) {
                            throw new Error(data.message ?? 'Gagal membuat sesi pembayaran.');
                        }

                        snapToken = data.token;
                        openSnap(snapToken);
                    } catch (error) {
                        fail(error.message);
                    } finally {
                        setBusy(false);
                    }
                };

                const start = () => {
                    if (snapToken) {
                        openSnap(snapToken);

                        return;
                    }

                    refreshToken();
                };

                window.nexaPay = start;

                document.querySelectorAll('[data-pay-trigger]').forEach((button) => {
                    button.addEventListener('click', start);
                });

                document.getElementById('pay-button')?.addEventListener('click', start);
                document.getElementById('refresh-token')?.addEventListener('click', refreshToken);
            })();
        </script>
    @endif
@endpush
