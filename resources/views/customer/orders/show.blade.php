@extends('layouts.dashboard')

@section('title', 'Detail Pesanan')

@section('content')
    @php
        $paid = $order->isPaid();
        $canCancel = $order->canBeCancelledByCustomer();
    @endphp

    <div class="space-y-5">
        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <a href="{{ route('customer.orders.index') }}" class="inline-flex items-center gap-1 text-[11px] font-bold text-slate-400 transition hover:text-brand-600">
                    <x-icon name="arrow-left" class="h-3.5 w-3.5" /> Kembali ke daftar pesanan
                </a>
                <h2 class="mt-1 font-display text-xl font-extrabold text-slate-900">{{ $order->kode_pesanan }}</h2>
                <p class="mt-0.5 text-xs text-slate-500">Dibuat {{ $order->tgl_pesan->translatedFormat('d F Y, H:i') }} WIB</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <x-payment-badge :status="$order->status_pembayaran" class="!px-3 !py-1.5 !text-xs" />
                <x-status-badge :status="$order->status_pesanan" class="!px-3 !py-1.5 !text-xs" />
            </div>
        </div>

        <div class="grid gap-5 xl:grid-cols-[1.55fr_1fr]">
            <div class="space-y-5">
                {{-- Progress --}}
                <div class="card p-5">
                    <div class="flex items-center justify-between gap-2">
                        <h3 class="font-display text-base font-bold text-slate-900">Progres pesanan</h3>
                        <span class="text-[10px] font-bold text-slate-400">Tahap {{ $order->tahapanAktif() + 1 }}/{{ count($statuses) }}</span>
                    </div>
                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full bg-gradient-to-r from-brand-500 to-accent-500"
                             style="width: {{ (($order->tahapanAktif() + 1) / count($statuses)) * 100 }}%"></div>
                    </div>

                    <div class="mt-4 grid gap-1.5 sm:grid-cols-2">
                        @foreach ($statuses as $index => $status)
                            @php $reached = $index <= $order->tahapanAktif(); @endphp
                            <div class="flex items-center gap-2 rounded-lg px-2 py-1.5 {{ $index === $order->tahapanAktif() ? 'bg-brand-50' : '' }}">
                                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-[9px] font-bold {{ $reached ? 'bg-brand-500 text-white' : 'bg-slate-100 text-slate-400' }}">
                                    @if ($reached)
                                        <x-icon name="check" class="h-3 w-3" />
                                    @else
                                        {{ $index + 1 }}
                                    @endif
                                </span>
                                <span class="truncate text-[11px] font-semibold {{ $reached ? 'text-slate-800' : 'text-slate-400' }}">{{ $status }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Items --}}
                <div class="card overflow-hidden">
                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 p-5">
                        <div>
                            <h3 class="font-display text-base font-bold text-slate-900">Rincian item cetak</h3>
                            <p class="text-xs text-slate-400">{{ $order->detail->count() }} item dalam pesanan ini.</p>
                        </div>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @foreach ($order->detail as $detail)
                            <div class="p-5">
                                <div class="flex flex-wrap items-start justify-between gap-2">
                                    <div>
                                        <p class="text-sm font-bold text-slate-900">{{ $detail->nama_produk_snapshot }}</p>
                                        <p class="text-[11px] text-slate-400">{{ $detail->nama_bahan_snapshot }}</p>
                                    </div>
                                    <p class="font-display text-base font-extrabold text-slate-900">Rp {{ number_format((int) $detail->subtotal, 0, ',', '.') }}</p>
                                </div>

                                <div class="mt-3 grid grid-cols-2 gap-2 text-[11px] sm:grid-cols-4">
                                    <div class="rounded-lg bg-slate-50 px-2.5 py-2">
                                        <p class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Ukuran</p>
                                        <p class="mt-0.5 font-semibold text-slate-700">{{ $detail->satuan_snapshot === 'meter' ? $detail->ukuranTeks() : '-' }}</p>
                                    </div>
                                    <div class="rounded-lg bg-slate-50 px-2.5 py-2">
                                        <p class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Jumlah</p>
                                        <p class="mt-0.5 font-semibold text-slate-700">{{ $detail->jumlah }} {{ $detail->satuan_snapshot }}</p>
                                    </div>
                                    <div class="rounded-lg bg-slate-50 px-2.5 py-2">
                                        <p class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Harga bahan</p>
                                        <p class="mt-0.5 font-semibold text-slate-700">Rp {{ number_format((int) $detail->harga_bahan_saat_beli, 0, ',', '.') }}</p>
                                    </div>
                                    <div class="rounded-lg bg-slate-50 px-2.5 py-2">
                                        <p class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Desain</p>
                                        <p class="mt-0.5 font-semibold text-slate-700">{{ $detail->tipe_desain === 'Jasa Desain Nekad Romo' ? 'Jasa desain' : 'Sendiri' }}</p>
                                    </div>
                                </div>

                                @if ($detail->finishing->isNotEmpty())
                                    <div class="mt-2.5 flex flex-wrap items-center gap-1.5">
                                        <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Finishing:</span>
                                        @foreach ($detail->finishing as $finishing)
                                            <span class="rounded-md bg-brand-50 px-1.5 py-0.5 text-[10px] font-semibold text-brand-700">
                                                {{ $finishing->nama_finishing_snapshot }}
                                                <span class="text-brand-500/70">(+Rp {{ number_format((int) $finishing->biaya_saat_beli, 0, ',', '.') }})</span>
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                @if ($detail->catatan_desain)
                                    <p class="mt-2.5 rounded-lg bg-amber-50 px-3 py-2 text-[11px] leading-relaxed text-amber-700">
                                        <x-icon name="info" class="mr-1 inline h-3.5 w-3.5" />
                                        {{ $detail->catatan_desain }}
                                    </p>
                                @endif

                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                    @if ($detail->file_desain)
                                        <a href="{{ route('orders.design', [$order, $detail]) }}" class="btn-white btn-sm">
                                            <x-icon name="download" class="h-4 w-4" /> {{ $detail->nama_file_desain ?? 'Unduh desain' }}
                                        </a>
                                    @else
                                        <span class="text-[11px] text-slate-400">Belum ada file desain diunggah.</span>
                                    @endif

                                    @if ($detail->biaya_desain > 0)
                                        <span class="badge bg-violet-50 text-violet-700">Jasa desain +Rp {{ number_format((int) $detail->biaya_desain, 0, ',', '.') }}</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- History --}}
                <div class="card p-5">
                    <h3 class="font-display text-base font-bold text-slate-900">Riwayat status</h3>
                    <div class="mt-4 space-y-4">
                        @forelse ($order->riwayat as $riwayat)
                            <div class="relative flex gap-3">
                                <span class="timeline-dot {{ $loop->last ? 'timeline-dot-active' : '' }} !h-7 !w-7">
                                    <x-icon name="check" class="h-3 w-3" />
                                </span>
                                <div class="-mt-0.5 flex-1">
                                    <div class="flex flex-wrap items-center justify-between gap-1">
                                        <p class="text-xs font-bold text-slate-800">{{ $riwayat->status_tahapan }}</p>
                                        <p class="text-[10px] font-semibold text-slate-400">{{ $riwayat->created_at->translatedFormat('d M Y, H:i') }} WIB</p>
                                    </div>
                                    <p class="mt-0.5 text-[11px] leading-relaxed text-slate-500">{{ $riwayat->keterangan }}</p>
                                    @if ($riwayat->user)
                                        <p class="mt-0.5 text-[9px] font-semibold uppercase tracking-wider text-slate-300">oleh {{ $riwayat->user->nama }}</p>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400">Belum ada riwayat status.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <aside class="space-y-4">
                <div class="card overflow-hidden">
                    <div class="bg-gradient-to-br from-brand-600 to-indigo-600 p-5 text-white">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-white/70">Grand total</p>
                        <p class="mt-1 font-display text-3xl font-extrabold">Rp {{ number_format((int) $order->grand_total, 0, ',', '.') }}</p>
                        <div class="mt-3 space-y-1 border-t border-white/15 pt-3 text-[11px] text-indigo-100/80">
                            <div class="flex justify-between"><span>Total produk</span><span>Rp {{ number_format((int) $order->total_produk, 0, ',', '.') }}</span></div>
                            <div class="flex justify-between"><span>Ongkir</span><span>{{ (float) $order->ongkir > 0 ? 'Rp '.number_format((int) $order->ongkir, 0, ',', '.') : 'Gratis' }}</span></div>
                        </div>
                    </div>

                    <div class="space-y-2.5 p-5">
                        @if (! $paid)
                            <a href="{{ route('payments.show', $order) }}" class="btn-primary w-full">
                                <x-icon name="credit-card" class="h-4.5 w-4.5" /> Bayar Sekarang
                            </a>
                        @endif
                        <a href="{{ route('orders.invoice', $order) }}" target="_blank" class="btn-white w-full">
                            <x-icon name="print" class="h-4.5 w-4.5" /> Cetak Invoice
                        </a>
                        <a href="{{ route('tracking.show', $order->kode_pesanan) }}" class="btn-ghost w-full">
                            <x-icon name="search" class="h-4.5 w-4.5" /> Halaman lacak
                        </a>

                        @if ($canCancel)
                            <form method="POST" action="{{ route('customer.orders.cancel', $order) }}"
                                  data-confirm="Pesanan {{ $order->kode_pesanan }} akan dibatalkan dan tidak dapat dikembalikan."
                                  data-confirm-button="Ya, batalkan pesanan" data-confirm-icon="warning"
                                  data-confirm-title="Batalkan pesanan?">
                                @csrf
                                <button type="submit" class="mt-1 inline-flex w-full items-center justify-center gap-1.5 rounded-xl px-4 py-2 text-xs font-bold text-rose-500 transition hover:bg-rose-50">
                                    <x-icon name="close" class="h-4 w-4" /> Batalkan pesanan
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="card p-5">
                    <h3 class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-500">
                        <x-icon name="truck" class="h-4 w-4 text-brand-500" /> Pengambilan
                    </h3>
                    <p class="mt-2 text-sm font-bold text-slate-800">{{ $order->metode_pengambilan }}</p>
                    <p class="mt-1 text-[11px] leading-relaxed text-slate-500">
                        {{ $order->alamat_pengiriman ?: $storeSetting->alamat }}
                    </p>
                    <div class="mt-3 flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-2">
                        <x-icon name="calendar" class="h-4 w-4 text-brand-500" />
                        <div class="leading-tight">
                            <p class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Estimasi selesai</p>
                            <p class="text-xs font-bold text-slate-800">{{ $order->estimasi_selesai?->translatedFormat('d F Y') ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                @if ($order->catatan)
                    <div class="card p-5">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Catatan pesanan</h3>
                        <p class="mt-2 text-[11px] leading-relaxed text-slate-600">{{ $order->catatan }}</p>
                    </div>
                @endif

                @if ($order->transaksiTerakhir)
                    <div class="card p-5">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Transaksi</h3>
                        <div class="mt-2 space-y-1.5 text-[11px]">
                            <div class="flex justify-between"><span class="text-slate-400">Metode</span><span class="font-semibold uppercase text-slate-700">{{ $order->transaksiTerakhir->payment_type ?? '-' }}</span></div>
                            <div class="flex justify-between"><span class="text-slate-400">Status</span><span class="font-semibold uppercase text-slate-700">{{ $order->transaksiTerakhir->transaction_status }}</span></div>
                            <div class="flex justify-between"><span class="text-slate-400">Waktu</span><span class="font-semibold text-slate-700">{{ $order->transaksiTerakhir->payment_time?->translatedFormat('d M Y, H:i') ?? '-' }}</span></div>
                        </div>
                    </div>
                @endif
            </aside>
        </div>
    </div>
@endsection
