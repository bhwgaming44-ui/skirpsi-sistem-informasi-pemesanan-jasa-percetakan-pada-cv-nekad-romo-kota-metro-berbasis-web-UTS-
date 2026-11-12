@extends('layouts.app')

@section('title', 'Lacak Pesanan')

@section('content')
    <section class="relative overflow-hidden bg-ink-950 py-14 text-white">
        <img src="{{ asset('images/products/nota.jpg') }}" alt="" class="absolute inset-0 h-full w-full object-cover opacity-10">
        <div class="absolute inset-0 bg-gradient-to-br from-ink-950 via-ink-950/95 to-brand-900/50"></div>
        <div class="blob -right-10 top-0 h-64 w-64 bg-brand-500/25"></div>

        <div class="container-page relative max-w-3xl text-center">
            <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3.5 py-1.5 text-[11px] font-bold text-white backdrop-blur">
                <x-icon name="search" class="h-3.5 w-3.5 text-accent-400" /> Pelacakan real-time
            </span>
            <h1 class="mt-4 font-display text-3xl font-extrabold tracking-tight sm:text-4xl">Lacak Pesanan Anda</h1>
            <p class="mx-auto mt-2 max-w-xl text-sm leading-relaxed text-indigo-100/70">
                Masukkan kode pesanan (contoh: <span class="font-mono font-semibold text-white">NR-20261110-A1B2</span>)
                untuk melihat status produksi tanpa perlu login.
            </p>

            <form method="GET" action="{{ route('tracking.index') }}" class="mx-auto mt-7 flex max-w-xl flex-col gap-3 sm:flex-row">
                <div class="relative flex-1">
                    <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                        <x-icon name="receipt" class="h-5 w-5" />
                    </span>
                    <input type="text" name="kode" value="{{ $code }}" required autofocus
                           placeholder="Masukkan kode pesanan"
                           class="input !h-13 !rounded-2xl !border-white/15 !bg-white/95 !pl-12 font-mono !text-slate-800 shadow-xl">
                </div>
                <button type="submit" class="btn-primary !h-13 !rounded-2xl !px-6">
                    <x-icon name="search" class="h-5 w-5" /> Lacak
                </button>
            </form>

            @auth
                @php
                    $recentForTracking = \App\Models\Order::query()
                        ->where('id_user', auth()->id())
                        ->latest('tgl_pesan')
                        ->limit(3)
                        ->get();
                @endphp

                @if ($recentForTracking->isNotEmpty())
                    <div class="mt-5 flex flex-wrap items-center justify-center gap-2">
                        <span class="text-[11px] font-semibold text-indigo-200/60">Pesanan terakhir:</span>
                        @foreach ($recentForTracking as $recent)
                            <a href="{{ route('tracking.index', ['kode' => $recent->kode_pesanan]) }}"
                               class="rounded-full border border-white/15 bg-white/10 px-2.5 py-1 font-mono text-[10px] font-semibold text-white backdrop-blur transition hover:bg-white/20">
                                {{ $recent->kode_pesanan }}
                            </a>
                        @endforeach
                    </div>
                @endif
            @endauth
        </div>
    </section>

    <section class="container-page -mt-8 relative z-10 pb-16">
        @if ($order)
            <div class="card overflow-hidden">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-5 sm:p-6">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Kode pesanan</p>
                        <h2 class="font-display text-xl font-extrabold tracking-tight text-slate-900">{{ $order->kode_pesanan }}</h2>
                        <p class="mt-1 text-xs text-slate-500">
                            Dibuat {{ $order->tgl_pesan->translatedFormat('d F Y, H:i') }} WIB
                            · {{ $order->user->nama }}
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <x-payment-badge :status="$order->status_pembayaran" />
                        <x-status-badge :status="$order->status_pesanan" />
                    </div>
                </div>

                {{-- Stepper --}}
                <div class="p-5 sm:p-6">
                    <div class="relative">
                        <div class="absolute left-4 right-4 top-4 h-1 rounded-full bg-slate-100 sm:left-6 sm:right-6"></div>
                        <div class="absolute left-4 top-4 h-1 rounded-full bg-gradient-to-r from-brand-500 to-accent-500 transition-all duration-700 sm:left-6"
                             style="width: calc((100% - 3rem) * {{ $order->tahapanAktif() / max(count(\App\Models\Order::TAHAPAN) - 1, 1) }})"></div>

                        <div class="relative grid grid-cols-3 gap-y-6 sm:grid-cols-6">
                            @foreach (\App\Models\Order::TAHAPAN as $index => $tahapan)
                                @php $reached = $index <= $order->tahapanAktif(); @endphp
                                <div class="flex flex-col items-center text-center">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white shadow-sm transition
                                        {{ $reached ? 'bg-gradient-to-br from-brand-500 to-accent-500 text-white ring-4 ring-brand-100' : 'bg-slate-100 text-slate-400' }}">
                                        @if ($index === 0)
                                            <x-icon name="receipt" class="h-3.5 w-3.5" />
                                        @elseif ($tahapan === \App\Models\Order::STATUS_SELESAI)
                                            <x-icon name="check" class="h-3.5 w-3.5" />
                                        @elseif ($tahapan === \App\Models\Order::STATUS_CETAK)
                                            <x-icon name="printer" class="h-3.5 w-3.5" />
                                        @else
                                            <span class="text-[10px] font-bold">{{ $index + 1 }}</span>
                                        @endif
                                    </span>
                                    <p class="mt-2 max-w-20 text-[10px] font-semibold leading-tight {{ $reached ? 'text-slate-800' : 'text-slate-400' }}">
                                        {{ str_replace(['Pesanan Masuk (Antrean)', 'Siap Diambil/Dikirim'], ['Antrean', 'Siap Diambil'], $tahapan) }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-6 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-xl bg-slate-50 px-4 py-3">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Estimasi selesai</p>
                            <p class="mt-0.5 text-sm font-bold text-slate-800">{{ $order->estimasi_selesai?->translatedFormat('d F Y') ?? '-' }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-4 py-3">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Metode</p>
                            <p class="mt-0.5 text-sm font-bold text-slate-800">{{ $order->metode_pengambilan }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 px-4 py-3">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Grand total</p>
                            <p class="mt-0.5 text-sm font-bold text-brand-600">Rp {{ number_format((int) $order->grand_total, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-4 lg:grid-cols-2">
                        <div>
                            <h3 class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-500">
                                <x-icon name="package" class="h-4 w-4 text-brand-500" /> Item pesanan
                            </h3>
                            <div class="mt-3 space-y-2.5">
                                @foreach ($order->detail as $detail)
                                    <div class="flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50/60 px-3.5 py-3">
                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white text-brand-600 shadow-sm">
                                            <x-icon name="printer" class="h-4 w-4" />
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs font-bold text-slate-800">{{ $detail->nama_produk_snapshot }}</p>
                                            <p class="text-[10px] text-slate-400">{{ $detail->nama_bahan_snapshot }}</p>
                                            <p class="text-[10px] text-slate-400">
                                                {{ $detail->satuan_snapshot === 'meter' ? $detail->ukuranTeks() : $detail->jumlah.' '.$detail->satuan_snapshot }}
                                                @if ($detail->finishing->isNotEmpty())
                                                    · {{ $detail->finishing->pluck('nama_finishing_snapshot')->join(', ') }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <h3 class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-500">
                                <x-icon name="clock" class="h-4 w-4 text-brand-500" /> Riwayat status
                            </h3>
                            <div class="mt-3 space-y-4">
                                @forelse ($order->riwayat as $riwayat)
                                    <div class="relative flex gap-3">
                                        <span class="timeline-dot {{ $loop->last ? 'timeline-dot-active' : '' }} !h-7 !w-7">
                                            <x-icon name="check" class="h-3 w-3" />
                                        </span>
                                        <div class="-mt-0.5 flex-1">
                                            <div class="flex flex-wrap items-center justify-between gap-1">
                                                <p class="text-[11px] font-bold text-slate-800">{{ $riwayat->status_tahapan }}</p>
                                                <p class="text-[9px] font-semibold text-slate-400">{{ $riwayat->created_at->translatedFormat('d M, H:i') }}</p>
                                            </div>
                                            <p class="text-[10px] leading-relaxed text-slate-500">{{ $riwayat->keterangan }}</p>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-xs text-slate-400">Belum ada riwayat status.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap gap-2 border-t border-slate-100 pt-5">
                        <a href="{{ route('tracking.show', $order->kode_pesanan) }}" class="btn-white btn-sm">
                            <x-icon name="eye" class="h-4 w-4" /> Halaman lacak lengkap
                        </a>
                        @auth
                            @if ($order->id_user === auth()->id())
                                <a href="{{ route('customer.orders.show', $order) }}" class="btn-primary btn-sm">
                                    <x-icon name="receipt" class="h-4 w-4" /> Detail pesanan saya
                                </a>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="btn-ghost btn-sm">Masuk untuk detail lengkap</a>
                        @endauth
                    </div>
                </div>
            </div>

        @elseif ($code !== '')
            <div class="card p-8 text-center">
                <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-rose-50 text-rose-500">
                    <x-icon name="search" class="h-7 w-7" />
                </span>
                <h2 class="mt-4 font-display text-xl font-extrabold text-slate-900">Pesanan tidak ditemukan</h2>
                <p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-slate-500">
                    Kode <span class="font-mono font-bold text-slate-700">{{ $code }}</span> tidak terdaftar.
                    Periksa kembali kode pada invoice atau hubungi admin kami.
                </p>
                <div class="mt-5 flex flex-wrap justify-center gap-2">
                    <a href="{{ route('tracking.index') }}" class="btn-white btn-sm">
                        <x-icon name="refresh" class="h-4 w-4" /> Coba kode lain
                    </a>
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $storeSetting->no_telepon ?? '6281278901234') }}" target="_blank" rel="noopener" class="btn-primary btn-sm">
                        <x-icon name="whatsapp" class="h-4 w-4" /> Hubungi admin
                    </a>
                </div>
            </div>

        @else
            <div class="grid gap-4 sm:grid-cols-3">
                @foreach ([
                    ['icon' => 'receipt', 'title' => 'Siapkan kode pesanan', 'text' => 'Kode dikirim setelah pesanan dibuat, formatnya NR-YYYYMMDD-XXXX.'],
                    ['icon' => 'search', 'title' => 'Masukkan kode', 'text' => 'Tempelkan kode pada kolom pencarian di atas, lalu tekan tombol Lacak.'],
                    ['icon' => 'truck', 'title' => 'Pantau progres', 'text' => 'Lihat posisi pesanan dari antrean sampai siap diambil/dikirim.'],
                ] as $step)
                    <div class="card p-5 reveal">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                            <x-icon :name="$step['icon']" class="h-5 w-5" />
                        </span>
                        <h3 class="mt-3 text-sm font-bold text-slate-900">{{ $step['title'] }}</h3>
                        <p class="mt-1 text-xs leading-relaxed text-slate-500">{{ $step['text'] }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
@endsection
