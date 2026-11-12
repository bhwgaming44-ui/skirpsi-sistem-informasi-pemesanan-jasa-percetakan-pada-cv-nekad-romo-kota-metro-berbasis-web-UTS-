@extends('layouts.app')

@section('title', 'Status ' . $order->kode_pesanan)

@section('content')
    <section class="relative overflow-hidden bg-ink-950 pb-20 pt-12 text-white">
        <img src="{{ asset('images/products/nota.jpg') }}" alt="" class="absolute inset-0 h-full w-full object-cover opacity-10">
        <div class="absolute inset-0 bg-gradient-to-br from-ink-950 via-ink-950/95 to-brand-900/60"></div>
        <div class="blob -right-12 -top-10 h-64 w-64 bg-accent-500/20"></div>

        <div class="container-page relative">
            <nav class="flex items-center gap-2 text-[11px] font-semibold text-indigo-200/60">
                <a href="{{ route('tracking.index') }}" class="transition hover:text-white">Lacak Pesanan</a>
                <x-icon name="chevron-right" class="h-3 w-3" />
                <span class="text-white">{{ $order->kode_pesanan }}</span>
            </nav>

            <div class="mt-4 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-indigo-200/60">Kode pesanan</p>
                    <h1 class="mt-1 font-display text-3xl font-extrabold tracking-tight sm:text-4xl">{{ $order->kode_pesanan }}</h1>
                    <p class="mt-2 text-sm text-indigo-100/70">
                        Dibuat {{ $order->tgl_pesan->translatedFormat('d F Y, H:i') }} WIB · a/n {{ $order->user->nama }}
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <x-payment-badge :status="$order->status_pembayaran" class="!px-3 !py-1.5 !text-xs" />
                    <x-status-badge :status="$order->status_pesanan" class="!px-3 !py-1.5 !text-xs" />
                </div>
            </div>
        </div>
    </section>

    <section class="container-page -mt-14 relative z-10 pb-16">
        <div class="grid gap-5 lg:grid-cols-[1.6fr_1fr]">
            <div class="space-y-5">
                <div class="card p-5 sm:p-6">
                    <div class="flex items-center justify-between gap-2">
                        <h2 class="font-display text-lg font-bold text-slate-900">Progres produksi</h2>
                        <span class="badge bg-brand-50 text-brand-700">
                            Tahap {{ $order->tahapanAktif() + 1 }} / {{ count(\App\Models\Order::TAHAPAN) }}
                        </span>
                    </div>

                    <div class="mt-4 h-2 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full bg-gradient-to-r from-brand-500 to-accent-500 transition-all duration-700"
                             style="width: {{ (($order->tahapanAktif() + 1) / count(\App\Models\Order::TAHAPAN)) * 100 }}%"></div>
                    </div>

                    <div class="mt-6 space-y-5">
                        @foreach (\App\Models\Order::TAHAPAN as $index => $tahapan)
                            @php
                                $reached = $index <= $order->tahapanAktif();
                                $isCurrent = $index === $order->tahapanAktif();
                            @endphp
                            <div class="relative flex gap-4">
                                @if (! $loop->last)
                                    <span class="absolute left-[15px] top-9 h-[calc(100%+0.25rem)] w-px {{ $reached ? 'bg-gradient-to-b from-brand-400 to-brand-200' : 'bg-slate-200' }}"></span>
                                @endif
                                <span class="timeline-dot {{ $reached ? 'timeline-dot-active' : '' }} {{ $isCurrent ? 'animate-pulse-ring' : '' }}">
                                    @if ($tahapan === \App\Models\Order::STATUS_SELESAI)
                                        <x-icon name="check" class="h-3.5 w-3.5" />
                                    @elseif ($tahapan === \App\Models\Order::STATUS_CETAK)
                                        <x-icon name="printer" class="h-3.5 w-3.5" />
                                    @elseif ($tahapan === \App\Models\Order::STATUS_SIAP)
                                        <x-icon name="truck" class="h-3.5 w-3.5" />
                                    @else
                                        <span class="text-[10px] font-bold">{{ $index + 1 }}</span>
                                    @endif
                                </span>
                                <div class="flex-1 pb-1">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <p class="text-sm font-bold {{ $reached ? 'text-slate-900' : 'text-slate-400' }}">{{ $tahapan }}</p>
                                        @if ($isCurrent)
                                            <span class="badge bg-brand-50 text-brand-700">
                                                <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-brand-500"></span> Sedang berjalan
                                            </span>
                                        @endif
                                    </div>
                                    <p class="mt-0.5 text-xs leading-relaxed {{ $reached ? 'text-slate-500' : 'text-slate-300' }}">
                                        {{ match ($tahapan) {
                                            \App\Models\Order::STATUS_MENUNGGU => 'Pesanan menunggu pembayaran melalui payment gateway.',
                                            \App\Models\Order::STATUS_ANTREAN => 'Pembayaran terverifikasi, pesanan masuk antrean workshop.',
                                            \App\Models\Order::STATUS_CETAK => 'Operator sedang mencetak pesanan Anda.',
                                            \App\Models\Order::STATUS_SELESAI_CETAK => 'Cetak selesai, masuk quality control dan finishing.',
                                            \App\Models\Order::STATUS_SIAP => 'Pesanan siap diambil di toko atau diserahkan ke kurir.',
                                            default => 'Pesanan telah selesai. Terima kasih telah mempercayakan cetak Anda kepada kami.',
                                        } }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="card p-5 sm:p-6">
                    <h2 class="font-display text-lg font-bold text-slate-900">Riwayat pembaruan</h2>
                    <div class="mt-4 space-y-4">
                        @forelse ($order->riwayat as $riwayat)
                            <div class="flex gap-3">
                                <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-600">
                                    <x-icon name="check" class="h-3.5 w-3.5" />
                                </span>
                                <div class="flex-1 border-b border-dashed border-slate-100 pb-3 last:border-0 last:pb-0">
                                    <div class="flex flex-wrap items-center justify-between gap-1">
                                        <p class="text-xs font-bold text-slate-800">{{ $riwayat->status_tahapan }}</p>
                                        <p class="text-[10px] font-semibold text-slate-400">{{ $riwayat->created_at->translatedFormat('d M Y, H:i') }} WIB</p>
                                    </div>
                                    <p class="mt-0.5 text-[11px] leading-relaxed text-slate-500">{{ $riwayat->keterangan }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400">Belum ada riwayat.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <aside class="space-y-4">
                <div class="card overflow-hidden">
                    <div class="border-b border-slate-100 bg-gradient-to-br from-brand-600 to-indigo-600 p-5 text-white">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-white/70">Grand total</p>
                        <p class="mt-1 font-display text-3xl font-extrabold">Rp {{ number_format((int) $order->grand_total, 0, ',', '.') }}</p>
                        <p class="mt-1 text-[11px] text-indigo-100/80">Termasuk ongkir Rp {{ number_format((int) $order->ongkir, 0, ',', '.') }}</p>
                    </div>

                    <div class="space-y-3 p-5">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Estimasi selesai</p>
                            <p class="mt-0.5 text-sm font-bold text-slate-800">{{ $order->estimasi_selesai?->translatedFormat('d F Y') ?? 'Menunggu konfirmasi' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Metode pengambilan</p>
                            <p class="mt-0.5 text-sm font-bold text-slate-800">{{ $order->metode_pengambilan }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Jumlah item</p>
                            <p class="mt-0.5 text-sm font-bold text-slate-800">{{ $order->detail->count() }} item cetak</p>
                        </div>
                    </div>
                </div>

                <div class="card p-5">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Item pesanan</h3>
                    <div class="mt-3 space-y-2.5">
                        @foreach ($order->detail as $detail)
                            <div class="rounded-xl bg-slate-50 px-3 py-2.5">
                                <p class="text-xs font-bold text-slate-800">{{ $detail->nama_produk_snapshot }}</p>
                                <p class="text-[10px] text-slate-400">{{ $detail->nama_bahan_snapshot }} ·
                                    {{ $detail->satuan_snapshot === 'meter' ? $detail->ukuranTeks() : $detail->jumlah.' '.$detail->satuan_snapshot }}
                                </p>
                                @if ($detail->finishing->isNotEmpty())
                                    <p class="mt-1 text-[10px] text-brand-600">{{ $detail->finishing->pluck('nama_finishing_snapshot')->join(' • ') }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-2xl border border-brand-100 bg-brand-50/60 p-5">
                    <p class="text-xs font-bold text-brand-800">Simpan kode pesanan ini</p>
                    <p class="mt-1 text-[11px] leading-relaxed text-brand-700/80">
                        Gunakan kode <span class="font-mono font-bold">{{ $order->kode_pesanan }}</span> untuk melacak pesanan kapan saja.
                    </p>
                    <button type="button" data-copy="{{ $order->kode_pesanan }}" class="btn-white btn-sm mt-3 w-full">
                        <x-icon name="clipboard" class="h-4 w-4" /> Salin kode
                    </button>
                </div>

                <a href="{{ route('tracking.index') }}" class="btn-ghost w-full">
                    <x-icon name="arrow-left" class="h-4 w-4" /> Lacak pesanan lain
                </a>
            </aside>
        </div>
    </section>
@endsection
