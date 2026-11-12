@extends('layouts.app')

@section('title', 'Keranjang Pesanan')

@section('content')
    <section class="border-b border-slate-200/70 bg-white">
        <div class="container-page py-8">
            <nav class="flex items-center gap-2 text-[11px] font-semibold text-slate-400">
                <a href="{{ route('home') }}" class="hover:text-brand-600">Beranda</a>
                <x-icon name="chevron-right" class="h-3 w-3" />
                <a href="{{ route('catalog.index') }}" class="hover:text-brand-600">Katalog</a>
                <x-icon name="chevron-right" class="h-3 w-3" />
                <span class="text-slate-700">Keranjang</span>
            </nav>
            <div class="mt-3 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 class="font-display text-3xl font-extrabold tracking-tight text-slate-900">Keranjang Pesanan</h1>
                    <p class="mt-1 text-sm text-slate-500">{{ count($items) }} item siap diproses. Periksa kembali spesifikasi sebelum checkout.</p>
                </div>
                <a href="{{ route('catalog.index') }}" class="btn-white btn-sm">
                    <x-icon name="plus" class="h-4 w-4" /> Tambah item lain
                </a>
            </div>
        </div>
    </section>

    <section class="container-page py-8 lg:py-10">
        <div class="grid gap-6 lg:grid-cols-[1.6fr_1fr]">
            <div class="space-y-4">
                @foreach ($items as $item)
                    @php
                        $product = $item['product'];
                        $quote = $item['quote'];
                        $satuan = $product->satuan;
                        $ukuran = $satuan === 'meter'
                            ? rtrim(rtrim(number_format($quote['panjang'], 2, ',', '.'), '0'), ',').' × '.rtrim(rtrim(number_format($quote['lebar'], 2, ',', '.'), '0'), ',').' m'
                            : $quote['jumlah'].' '.$satuan;
                    @endphp

                    <article class="card overflow-hidden">
                        <div class="flex flex-col gap-4 p-4 sm:flex-row sm:p-5">
                            <a href="{{ route('catalog.show', $product) }}" class="relative block h-28 w-full shrink-0 overflow-hidden rounded-2xl bg-slate-100 sm:h-28 sm:w-32">
                                <img src="{{ $product->gambarUrl() }}" alt="{{ $product->nama_produk }}" class="h-full w-full object-cover transition duration-500 hover:scale-105">
                            </a>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-brand-500">{{ $product->kategori->nama_kategori }}</p>
                                        <h2 class="mt-0.5 text-base font-bold text-slate-900">
                                            <a href="{{ route('catalog.show', $product) }}" class="transition hover:text-brand-700">{{ $product->nama_produk }}</a>
                                        </h2>
                                    </div>
                                    <form method="POST" action="{{ route('cart.destroy', $item['id']) }}"
                                          data-confirm="Item ini akan dihapus dari keranjang." data-confirm-button="Ya, hapus"
                                          data-confirm-icon="warning" data-confirm-title="Hapus item?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-rose-50 hover:text-rose-600" title="Hapus item">
                                            <x-icon name="trash" class="h-4 w-4" />
                                        </button>
                                    </form>
                                </div>

                                <div class="mt-2.5 flex flex-wrap gap-1.5">
                                    <span class="badge bg-slate-100 text-slate-600"><x-icon name="layers" class="h-3 w-3" /> {{ $item['material']->nama_bahan }}</span>
                                    <span class="badge bg-slate-100 text-slate-600"><x-icon name="box" class="h-3 w-3" /> {{ $ukuran }} · {{ $quote['jumlah'] }} {{ $satuan === 'meter' ? 'pcs' : '' }}</span>
                                    <span class="badge {{ $item['tipe_desain'] === 'Jasa Desain Nekad Romo' ? 'bg-violet-50 text-violet-700' : 'bg-emerald-50 text-emerald-700' }}">
                                        <x-icon name="palette" class="h-3 w-3" />
                                        {{ $item['tipe_desain'] === 'Jasa Desain Nekad Romo' ? 'Jasa desain' : 'Desain sendiri' }}
                                    </span>
                                </div>

                                @if ($quote['finishings']->isNotEmpty())
                                    <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Finishing:</span>
                                        @foreach ($quote['finishings'] as $finishing)
                                            <span class="rounded-md bg-brand-50 px-1.5 py-0.5 text-[10px] font-semibold text-brand-700">{{ $finishing->nama_finishing }}</span>
                                        @endforeach
                                    </div>
                                @endif

                                @if ($item['file_desain'] || $item['catatan_desain'])
                                    <div class="mt-2 flex flex-wrap items-center gap-3 text-[11px] text-slate-500">
                                        @if ($item['file_desain'])
                                            <span class="inline-flex items-center gap-1 text-emerald-600">
                                                <x-icon name="file" class="h-3.5 w-3.5" /> {{ $item['nama_file_desain'] ?? 'File desain terunggah' }}
                                            </span>
                                        @endif
                                        @if ($item['catatan_desain'])
                                            <span class="inline-flex items-center gap-1">
                                                <x-icon name="info" class="h-3.5 w-3.5" /> {{ \Illuminate\Support\Str::limit($item['catatan_desain'], 60) }}
                                            </span>
                                        @endif
                                    </div>
                                @endif

                                <div class="mt-3 flex flex-wrap items-end justify-between gap-2 border-t border-dashed border-slate-200 pt-3">
                                    <div class="text-[11px] leading-relaxed text-slate-400">
                                        <p>Harga bahan: Rp {{ number_format($quote['harga_dasar'], 0, ',', '.') }}</p>
                                        @if ($quote['biaya_finishing'] > 0)
                                            <p>Finishing: Rp {{ number_format($quote['biaya_finishing'], 0, ',', '.') }}</p>
                                        @endif
                                        @if ($quote['biaya_desain'] > 0)
                                            <p>Jasa desain: Rp {{ number_format($quote['biaya_desain'], 0, ',', '.') }}</p>
                                        @endif
                                    </div>
                                    <p class="font-display text-lg font-extrabold text-slate-900">Rp {{ number_format($quote['subtotal'], 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach

                @auth
                    <p class="flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50/70 px-4 py-2.5 text-[11px] font-semibold text-emerald-700">
                        <x-icon name="check-circle" class="h-4 w-4" />
                        Keranjang dan draft checkout tersimpan otomatis di akun Anda. Keluar lalu masuk kembali, pesanan tetap bisa dilanjutkan.
                    </p>
                @else
                    <p class="flex items-center gap-2 rounded-2xl border border-amber-200 bg-amber-50/70 px-4 py-2.5 text-[11px] font-semibold text-amber-800">
                        <x-icon name="info" class="h-4 w-4" />
                        Masuk ke akun agar keranjang tersimpan permanen dan bisa dilanjutkan kapan saja.
                    </p>
                @endauth

                <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-dashed border-slate-300 bg-white/60 px-4 py-3">
                    <p class="text-xs text-slate-500">
                        <x-icon name="info" class="mr-1 inline h-3.5 w-3.5 text-brand-500" />
                        Harga di keranjang dihitung ulang otomatis saat checkout untuk memastikan akurasi.
                    </p>
                    <form method="POST" action="{{ route('cart.clear') }}"
                          data-confirm="Semua item akan dihapus dari keranjang." data-confirm-button="Ya, kosongkan"
                          data-confirm-title="Kosongkan keranjang?">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-1.5 text-xs font-bold text-rose-500 transition hover:text-rose-600">
                            <x-icon name="trash" class="h-3.5 w-3.5" /> Kosongkan keranjang
                        </button>
                    </form>
                </div>
            </div>

            {{-- Summary --}}
            <aside class="sticky-summary">
                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[var(--shadow-soft)]">
                    <div class="border-b border-slate-100 p-5">
                        <h2 class="font-display text-lg font-bold text-slate-900">Ringkasan pesanan</h2>
                        <p class="mt-0.5 text-xs text-slate-400">Total sementara sebelum ongkir.</p>
                    </div>

                    <div class="space-y-3 p-5 text-sm">
                        <div class="flex items-center justify-between text-slate-500">
                            <span>Subtotal ({{ count($items) }} item)</span>
                            <span class="font-semibold text-slate-800">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-500">
                            <span>Ongkos kirim</span>
                            <span class="text-xs font-semibold text-slate-400">Dihitung saat checkout</span>
                        </div>

                        <div class="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2.5">
                            <span class="text-xs font-semibold text-slate-500">Metode pengambilan</span>
                            <span class="text-xs font-bold text-slate-800">Toko / Kurir</span>
                        </div>

                        <div class="flex items-end justify-between border-t border-dashed border-slate-200 pt-3">
                            <span class="font-display text-base font-bold text-slate-900">Total</span>
                            <span class="font-display text-2xl font-extrabold text-brand-600">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>

                        @auth
                            <a href="{{ route('checkout.index') }}" class="btn-primary w-full !py-3.5 shadow-xl shadow-brand-500/25">
                                <x-icon name="credit-card" class="h-5 w-5" /> Lanjut ke Checkout
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn-primary w-full !py-3.5 shadow-xl shadow-brand-500/25">
                                <x-icon name="lock" class="h-5 w-5" /> Masuk untuk Checkout
                            </a>
                            <p class="text-center text-[11px] text-slate-400">
                                Belum punya akun?
                                <a href="{{ route('register') }}" class="font-bold text-brand-600">Daftar gratis</a>
                            </p>
                        @endauth

                        <a href="{{ route('catalog.index') }}" class="btn-white w-full">
                            <x-icon name="arrow-left" class="h-4 w-4" /> Lanjut belanja
                        </a>
                    </div>

                    <div class="border-t border-slate-100 bg-slate-50/60 p-5">
                        <div class="grid gap-2.5 text-[11px] text-slate-500">
                            <p class="flex items-center gap-2"><x-icon name="shield" class="h-4 w-4 text-emerald-500" /> Pembayaran terverifikasi otomatis</p>
                            <p class="flex items-center gap-2"><x-icon name="clock" class="h-4 w-4 text-brand-500" /> Estimasi produksi tampil di checkout</p>
                            <p class="flex items-center gap-2"><x-icon name="file" class="h-4 w-4 text-violet-500" /> File desain tersimpan aman</p>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </section>
@endsection
