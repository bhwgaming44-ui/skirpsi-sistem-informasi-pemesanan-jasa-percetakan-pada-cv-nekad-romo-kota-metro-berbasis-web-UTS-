@extends('layouts.dashboard')

@section('title', 'Detail Pesanan ' . $order->kode_pesanan)

@section('content')
    @php
        $paid = $order->isPaid();
        $currentIndex = $order->tahapanAktif();
        $activeStatuses = array_values(array_filter($statuses, fn ($status) => $status !== $order->status_pesanan));
    @endphp

    <div class="space-y-5">
        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-1 text-[11px] font-bold text-slate-400 transition hover:text-brand-600">
                    <x-icon name="arrow-left" class="h-3.5 w-3.5" /> Kembali ke daftar pesanan
                </a>
                <div class="mt-1 flex flex-wrap items-center gap-2">
                    <h2 class="font-display text-xl font-extrabold text-slate-900">{{ $order->kode_pesanan }}</h2>
                    <button type="button" data-copy="{{ $order->kode_pesanan }}"
                            class="rounded-lg p-1 text-slate-400 transition hover:bg-white hover:text-brand-600" title="Salin kode">
                        <x-icon name="clipboard" class="h-4 w-4" />
                    </button>
                </div>
                <p class="mt-0.5 text-xs text-slate-500">
                    Dibuat {{ $order->tgl_pesan->translatedFormat('d F Y, H:i') }} WIB · {{ $order->detail->count() }} item cetak
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <x-payment-badge :status="$order->status_pembayaran" class="!px-3 !py-1.5 !text-xs" />
                <x-status-badge :status="$order->status_pesanan" class="!px-3 !py-1.5 !text-xs" />
            </div>
        </div>

        {{-- Notices --}}
        @if (! $paid)
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3.5">
                <div class="flex items-start gap-2.5">
                    <x-icon name="alert" class="mt-0.5 h-4.5 w-4.5 shrink-0 text-amber-600" />
                    <div>
                        <p class="text-sm font-bold text-amber-800">Pembayaran belum lunas</p>
                        <p class="text-[11px] text-amber-700/80">Status produksi hanya dapat diperbarui setelah pembayaran terverifikasi.</p>
                    </div>
                </div>
                <a href="{{ route('payments.show', $order) }}" target="_blank" class="btn-white btn-sm">
                    <x-icon name="credit-card" class="h-4 w-4" /> Halaman pembayaran
                </a>
            </div>
        @endif

        @if ($order->isCancelled())
            <div class="flex items-start gap-2.5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3.5">
                <x-icon name="close" class="mt-0.5 h-4.5 w-4.5 shrink-0 text-rose-600" />
                <div>
                    <p class="text-sm font-bold text-rose-800">Pesanan dibatalkan</p>
                    <p class="text-[11px] text-rose-700/80">Pesanan ini sudah dibatalkan dan tidak dapat diubah statusnya lagi.</p>
                </div>
            </div>
        @endif

        <div class="grid gap-5 xl:grid-cols-[1.55fr_1fr]">
            <div class="space-y-5">
                {{-- Progress --}}
                <div class="card p-5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h3 class="font-display text-base font-bold text-slate-900">Progres produksi</h3>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold text-slate-400">Tahap {{ $currentIndex + 1 }}/{{ count(\App\Models\Order::TAHAPAN) }}</span>
                            @if ($paid && ! $order->isCancelled() && $order->nextStatus() && $order->nextStatus() !== \App\Models\Order::STATUS_BATAL)
                                <form method="POST" action="{{ route('admin.orders.advance', $order) }}"
                                      data-confirm="Status pesanan akan dilanjutkan ke tahap berikutnya."
                                      data-confirm-button="Ya, lanjutkan" data-confirm-icon="question"
                                      data-confirm-title="Lanjutkan status pesanan?">
                                    @csrf
                                    <button type="submit" class="btn-primary btn-sm">
                                        <x-icon name="arrow-right" class="h-4 w-4" /> Lanjutkan tahap
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full bg-gradient-to-r from-brand-500 to-accent-500 transition-all duration-700"
                             style="width: {{ (($currentIndex + 1) / count(\App\Models\Order::TAHAPAN)) * 100 }}%"></div>
                    </div>

                    <div class="mt-4 grid gap-1.5 sm:grid-cols-2">
                        @foreach (\App\Models\Order::TAHAPAN as $index => $status)
                            @php $reached = $index <= $currentIndex; @endphp
                            <div class="flex items-center gap-2 rounded-lg px-2 py-1.5 {{ $index === $currentIndex ? 'bg-brand-50' : '' }}">
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
                    <div class="border-b border-slate-100 p-5">
                        <h3 class="font-display text-base font-bold text-slate-900">Rincian item cetak</h3>
                        <p class="text-xs text-slate-400">Snapshot data saat pesanan dibuat.</p>
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
                                        <span class="text-[11px] text-slate-400">Tidak ada file desain (mungkin pakai jasa desain).</span>
                                    @endif

                                    @if ((float) $detail->biaya_desain > 0)
                                        <span class="badge bg-violet-50 text-violet-700">Jasa desain +Rp {{ number_format((int) $detail->biaya_desain, 0, ',', '.') }}</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="grid gap-2 border-t border-slate-100 bg-slate-50/60 p-5 sm:grid-cols-3">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total produk</p>
                            <p class="text-sm font-bold text-slate-800">Rp {{ number_format((int) $order->total_produk, 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Ongkos kirim</p>
                            <p class="text-sm font-bold text-slate-800">{{ (float) $order->ongkir > 0 ? 'Rp '.number_format((int) $order->ongkir, 0, ',', '.') : 'Gratis' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-brand-500">Grand total</p>
                            <p class="font-display text-lg font-extrabold text-brand-600">Rp {{ number_format((int) $order->grand_total, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>

                {{-- History --}}
                <div class="card p-5">
                    <div class="flex items-center justify-between gap-2">
                        <h3 class="font-display text-base font-bold text-slate-900">Riwayat status</h3>
                        <span class="badge bg-slate-100 text-slate-500">{{ $order->riwayat->count() }} pembaruan</span>
                    </div>

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
                {{-- Update status --}}
                @if (! $order->isCancelled())
                    <div class="card p-5">
                        <h3 class="flex items-center gap-1.5 font-display text-base font-bold text-slate-900">
                            <x-icon name="refresh" class="h-4.5 w-4.5 text-brand-500" /> Ubah status pesanan
                        </h3>
                        <p class="mt-0.5 text-xs text-slate-400">Update manual beserta catatan opsional.</p>

                        <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="mt-3 space-y-3"
                              data-confirm="Status pesanan akan diubah dan pelanggan dapat melihat pembaruan ini."
                              data-confirm-button="Ya, ubah status" data-confirm-icon="question"
                              data-confirm-title="Ubah status pesanan?">
                            @csrf

                            <div>
                                <label for="status" class="label">Status baru</label>
                                <select id="status" name="status" required class="select">
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status }}" @selected($status === $order->status_pesanan)>{{ $status }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="keterangan" class="label">Keterangan <span class="font-medium normal-case tracking-normal text-slate-400">(opsional)</span></label>
                                <textarea id="keterangan" name="keterangan" rows="2" maxlength="500"
                                          placeholder="Contoh: Proses cetak selesai, masuk finishing laminasi."
                                          class="textarea !text-xs"></textarea>
                            </div>

                            <button type="submit" class="btn-primary w-full btn-sm">
                                <x-icon name="check" class="h-4 w-4" /> Simpan status
                            </button>
                        </form>
                    </div>
                @endif

                {{-- Customer --}}
                <div class="card p-5">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Pelanggan</h3>
                    <div class="mt-3 flex items-center gap-2.5">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-accent-500 text-sm font-bold text-white">
                            {{ strtoupper(substr($order->user->nama, 0, 1)) }}
                        </span>
                        <div class="min-w-0 leading-tight">
                            <p class="truncate text-sm font-bold text-slate-900">{{ $order->user->nama }}</p>
                            <p class="truncate text-[10px] text-slate-400">{{ $order->user->email }}</p>
                        </div>
                    </div>

                    <div class="mt-3 space-y-1.5 text-[11px]">
                        <p class="flex items-center justify-between gap-2"><span class="text-slate-400">Username</span><span class="font-semibold text-slate-700">{{ $order->user->username }}</span></p>
                        <p class="flex items-center justify-between gap-2"><span class="text-slate-400">No. HP</span><span class="font-semibold text-slate-700">{{ $order->user->no_hp }}</span></p>
                    </div>

                    <a href="{{ route('admin.customers.show', $order->user) }}" class="btn-white btn-sm mt-3 w-full">
                        <x-icon name="user" class="h-4 w-4" /> Lihat profil pelanggan
                    </a>
                </div>

                {{-- Delivery --}}
                <div class="card p-5">
                    <h3 class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-500">
                        <x-icon name="truck" class="h-4 w-4 text-brand-500" /> Pengambilan
                    </h3>
                    <p class="mt-2 text-sm font-bold text-slate-800">{{ $order->metode_pengambilan }}</p>
                    <p class="mt-1 text-[11px] leading-relaxed text-slate-500">{{ $order->alamat_pengiriman ?: $storeSetting->alamat }}</p>

                    <div class="mt-3 flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-2">
                        <x-icon name="calendar" class="h-4 w-4 text-brand-500" />
                        <div class="leading-tight">
                            <p class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Estimasi selesai</p>
                            <p class="text-xs font-bold text-slate-800">{{ $order->estimasi_selesai?->translatedFormat('d F Y') ?? '-' }}</p>
                        </div>
                    </div>

                    @if ($order->catatan)
                        <div class="mt-3 rounded-xl bg-amber-50 px-3 py-2">
                            <p class="text-[9px] font-bold uppercase tracking-wider text-amber-600">Catatan pelanggan</p>
                            <p class="mt-0.5 text-[11px] leading-relaxed text-amber-700">{{ $order->catatan }}</p>
                        </div>
                    @endif
                </div>

                {{-- Transaction --}}
                @if ($order->transaksiTerakhir)
                    @php $trx = $order->transaksiTerakhir; @endphp
                    <div class="card p-5">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Transaksi gateway</h3>
                            <span class="badge {{ $trx->isSettled() ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ $trx->transaction_status }}
                            </span>
                        </div>

                        <div class="mt-3 space-y-1.5 text-[11px]">
                            <p class="flex items-center justify-between gap-2"><span class="text-slate-400">Metode</span><span class="font-semibold uppercase text-slate-700">{{ $trx->payment_type ?? '-' }}</span></p>
                            <p class="flex items-center justify-between gap-2"><span class="text-slate-400">Nominal</span><span class="font-semibold text-slate-700">Rp {{ number_format((int) $trx->gross_amount, 0, ',', '.') }}</span></p>
                            @if ($trx->va_number)
                                <p class="flex items-center justify-between gap-2"><span class="text-slate-400">VA</span><span class="font-mono font-semibold text-slate-700">{{ $trx->va_number }}</span></p>
                            @endif
                            <p class="flex items-center justify-between gap-2"><span class="text-slate-400">Waktu bayar</span><span class="font-semibold text-slate-700">{{ $trx->payment_time?->translatedFormat('d M Y, H:i') ?? '-' }}</span></p>
                            <p class="flex items-center justify-between gap-2"><span class="text-slate-400">ID gateway</span><span class="truncate font-mono text-[10px] font-semibold text-slate-600">{{ $trx->transaction_id_gateway ?? '-' }}</span></p>
                        </div>
                    </div>
                @endif

                <div class="space-y-2">
                    <a href="{{ route('admin.orders.invoice', $order) }}" target="_blank" class="btn-primary w-full">
                        <x-icon name="print" class="h-4.5 w-4.5" /> Cetak Invoice
                    </a>
                    <a href="{{ route('tracking.show', $order->kode_pesanan) }}" target="_blank" class="btn-white w-full">
                        <x-icon name="search" class="h-4.5 w-4.5" /> Halaman lacak pelanggan
                    </a>
                </div>
            </aside>
        </div>
    </div>
@endsection
