@extends('layouts.app')

@section('title', 'Checkout Pesanan')

@php
    $user = auth()->user();
    $biayaKirim = (int) round((float) $store->biaya_kirim);
    $draft = $draft ?? [];
    $oldMetode = old('metode_pengambilan', $draft['metode_pengambilan'] ?? 'Ambil di Toko');
    $oldAlamat = old('alamat_pengiriman', $draft['alamat_pengiriman'] ?? $user->alamat);
    $oldCatatan = old('catatan', $draft['catatan'] ?? '');
@endphp

@section('content')
    <section class="border-b border-slate-200/70 bg-white">
        <div class="container-page py-8">
            <nav class="flex items-center gap-2 text-[11px] font-semibold text-slate-400">
                <a href="{{ route('cart.index') }}" class="hover:text-brand-600">Keranjang</a>
                <x-icon name="chevron-right" class="h-3 w-3" />
                <span class="text-slate-700">Checkout</span>
            </nav>
            <div class="mt-3 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h1 class="font-display text-3xl font-extrabold tracking-tight text-slate-900">Checkout</h1>
                    <p class="mt-1 text-sm text-slate-500">Lengkapi detail pengambilan dan konfirmasi pesanan Anda.</p>
                </div>
                <div class="flex items-center gap-1.5 text-[11px] font-semibold text-slate-400">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-emerald-700"><x-icon name="check" class="h-3.5 w-3.5" /></span>
                    Keranjang
                    <span class="mx-1 h-px w-5 bg-slate-300"></span>
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-brand-600 text-white">2</span>
                    Checkout
                    <span class="mx-1 h-px w-5 bg-slate-300"></span>
                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-slate-100 text-slate-400">3</span>
                    Pembayaran
                </div>
            </div>
        </div>
    </section>

    <section class="container-page py-8 lg:py-10"
             x-data="checkoutDraft({
                 metode: @js($oldMetode),
                 alamat: @js($oldAlamat),
                 catatan: @js($oldCatatan),
                 biayaKirim: {{ $biayaKirim }},
                 subtotal: {{ (int) $subtotal }},
                 endpoint: '{{ route('checkout.draft') }}',
                 hasDraft: @js(! empty($draft))
             })">
        <div x-show="status !== 'idle'" x-cloak x-transition
             class="mb-5 flex flex-wrap items-center justify-between gap-2 rounded-2xl border px-4 py-2.5"
             :class="status === 'error' ? 'border-rose-200 bg-rose-50/80' : 'border-emerald-200 bg-emerald-50/80'">
            <p class="flex items-center gap-2 text-xs font-semibold"
               :class="status === 'error' ? 'text-rose-700' : 'text-emerald-700'">
                <x-icon name="check-circle" class="h-4 w-4" />
                <span x-text="status === 'saving'
                    ? 'Menyimpan progres checkout...'
                    : (status === 'error' ? 'Gagal menyimpan progres. Coba ubah kolom lagi.' : 'Progres checkout tersimpan otomatis')">Progres checkout tersimpan otomatis</span>
            </p>
            <p class="text-[11px]"
               :class="status === 'error' ? 'text-rose-600/80' : 'text-emerald-600/80'">Bisa dilanjutkan setelah keluar dan masuk kembali.</p>
        </div>

        <form method="POST" action="{{ route('checkout.store') }}"
              data-confirm="Pesanan akan dibuat dan Anda diarahkan ke halaman pembayaran."
              data-confirm-button="Ya, buat pesanan" data-confirm-icon="question" data-confirm-title="Buat pesanan sekarang?">
            @csrf

            <div class="grid gap-6 lg:grid-cols-[1.6fr_1fr]">
                <div class="space-y-5">
                    {{-- Contact --}}
                    <div class="card p-5 sm:p-6">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5">
                                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                                    <x-icon name="user" class="h-4.5 w-4.5" />
                                </span>
                                <div>
                                    <h2 class="font-display text-base font-bold text-slate-900">Data pemesan</h2>
                                    <p class="text-xs text-slate-400">Terhubung dengan akun Anda.</p>
                                </div>
                            </div>
                            <a href="{{ route('customer.profile.edit') }}" class="text-[11px] font-bold text-brand-600 hover:text-brand-700">Ubah profil</a>
                        </div>

                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            <div class="rounded-xl bg-slate-50 px-4 py-3">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Nama</p>
                                <p class="mt-0.5 text-sm font-semibold text-slate-800">{{ $user->nama }}</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 px-4 py-3">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">No. HP / WhatsApp</p>
                                <p class="mt-0.5 text-sm font-semibold text-slate-800">{{ $user->no_hp }}</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 px-4 py-3 sm:col-span-2">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Email</p>
                                <p class="mt-0.5 text-sm font-semibold text-slate-800">{{ $user->email }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Delivery method --}}
                    <div class="card p-5 sm:p-6">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                                <x-icon name="truck" class="h-4.5 w-4.5" />
                            </span>
                            <div>
                                <h2 class="font-display text-base font-bold text-slate-900">Metode pengambilan</h2>
                                <p class="text-xs text-slate-400">Pilih ambil sendiri di toko atau dikirim kurir.</p>
                            </div>
                        </div>

                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            <label class="flex cursor-pointer items-start gap-3 rounded-2xl border-2 border-slate-200 p-4 transition hover:border-brand-300 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50/60">
                                <input type="radio" name="metode_pengambilan" value="Ambil di Toko" x-model="metode" @change="save()"
                                       class="mt-1 h-4 w-4 border-slate-300 text-brand-600 focus:ring-brand-200">
                                <span class="min-w-0">
                                    <span class="flex items-center gap-1.5 text-sm font-bold text-slate-800">
                                        <x-icon name="map-pin" class="h-4 w-4 text-brand-500" /> Ambil di Toko
                                    </span>
                                    <span class="mt-1 block text-[11px] leading-relaxed text-slate-500">{{ $store->alamat }}</span>
                                    <span class="mt-1.5 inline-flex rounded-md bg-emerald-50 px-1.5 py-0.5 text-[10px] font-bold text-emerald-700">Gratis</span>
                                </span>
                            </label>

                            <label class="flex cursor-pointer items-start gap-3 rounded-2xl border-2 border-slate-200 p-4 transition hover:border-brand-300 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50/60">
                                <input type="radio" name="metode_pengambilan" value="Kirim Kurir/Ekspedisi" x-model="metode" @change="save()"
                                       class="mt-1 h-4 w-4 border-slate-300 text-brand-600 focus:ring-brand-200">
                                <span class="min-w-0">
                                    <span class="flex items-center gap-1.5 text-sm font-bold text-slate-800">
                                        <x-icon name="truck" class="h-4 w-4 text-sky-500" /> Kirim Kurir
                                    </span>
                                    <span class="mt-1 block text-[11px] leading-relaxed text-slate-500">Dikirim ke alamat Anda via kurir/ekspedisi.</span>
                                    <span class="mt-1.5 inline-flex rounded-md bg-sky-50 px-1.5 py-0.5 text-[10px] font-bold text-sky-700">+ Rp {{ number_format($biayaKirim, 0, ',', '.') }}</span>
                                </span>
                            </label>
                        </div>
                        <x-form-error name="metode_pengambilan" />

                        <div class="mt-4" x-show="metode === 'Kirim Kurir/Ekspedisi'" x-transition>
                            <label for="alamat_pengiriman" class="label">Alamat pengiriman</label>
                            <textarea id="alamat_pengiriman" name="alamat_pengiriman" rows="3"
                                      x-model="alamat" @input.debounce.600ms="save()" @blur="save()"
                                      placeholder="Nama penerima, alamat lengkap, kelurahan, kecamatan, kota, kode pos"
                                      class="textarea">{{ $oldAlamat }}</textarea>
                            <x-form-error name="alamat_pengiriman" />
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div class="card p-5 sm:p-6">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                                <x-icon name="clipboard" class="h-4.5 w-4.5" />
                            </span>
                            <div>
                                <h2 class="font-display text-base font-bold text-slate-900">Catatan tambahan <span class="text-xs font-medium text-slate-400">(opsional)</span></h2>
                                <p class="text-xs text-slate-400">Instruksi khusus untuk operator cetak.</p>
                            </div>
                        </div>
                        <textarea name="catatan" rows="3" placeholder="Contoh: warna tolong dibuat lebih cerah, finishing lipat rapi, dsb."
                                  x-model="catatan" @input.debounce.600ms="save()" @blur="save()"
                                  class="textarea mt-4">{{ $oldCatatan }}</textarea>
                        <x-form-error name="catatan" />
                    </div>

                    {{-- Payment info --}}
                    <div class="rounded-2xl border p-5 {{ $paymentReady ? 'border-emerald-200 bg-emerald-50/60' : 'border-amber-200 bg-amber-50/60' }}">
                        <div class="flex items-start gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white {{ $paymentReady ? 'text-emerald-600' : 'text-amber-600' }} shadow-sm">
                                <x-icon :name="$paymentReady ? 'credit-card' : 'info'" class="h-4.5 w-4.5" />
                            </span>
                            <div>
                                <p class="text-sm font-bold {{ $paymentReady ? 'text-emerald-800' : 'text-amber-800' }}">
                                    {{ $paymentReady ? 'Pembayaran via Midtrans Snap' : 'Mode simulasi pembayaran aktif' }}
                                </p>
                                <p class="mt-1 text-[11px] leading-relaxed {{ $paymentReady ? 'text-emerald-700/80' : 'text-amber-700/80' }}">
                                    {{ $paymentReady
                                        ? 'Setelah pesanan dibuat, Anda akan diarahkan ke halaman pembayaran Midtrans (QRIS, e-wallet, virtual account). Status pesanan diperbarui otomatis.'
                                        : 'Kunci Midtrans belum dikonfigurasi admin, jadi pembayaran dapat disimulasikan di halaman berikutnya untuk keperluan demo/skripsi.' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Summary --}}
                <aside class="sticky-summary">
                    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[var(--shadow-soft)]">
                        <div class="border-b border-slate-100 p-5">
                            <h2 class="font-display text-lg font-bold text-slate-900">Detail pesanan</h2>
                            <p class="mt-0.5 text-xs text-slate-400">{{ count($items) }} item cetak</p>
                        </div>

                        <div class="max-h-72 space-y-3 overflow-y-auto p-5">
                            @foreach ($items as $item)
                                <div class="flex gap-3">
                                    <img src="{{ $item['product']->gambarUrl() }}" alt="{{ $item['product']->nama_produk }}"
                                         class="h-14 w-14 shrink-0 rounded-xl object-cover">
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-xs font-bold text-slate-800">{{ $item['product']->nama_produk }}</p>
                                        <p class="truncate text-[10px] text-slate-400">{{ $item['material']->nama_bahan }}</p>
                                        <p class="mt-0.5 text-[10px] text-slate-400">
                                            {{ $item['product']->satuan === 'meter' ? $item['quote']['panjang'].' × '.$item['quote']['lebar'].' m' : $item['quote']['jumlah'].' '.$item['product']->satuan }}
                                        </p>
                                    </div>
                                    <p class="shrink-0 text-xs font-bold text-slate-800">Rp {{ number_format($item['quote']['subtotal'], 0, ',', '.') }}</p>
                                </div>
                            @endforeach
                        </div>

                        <div class="space-y-2.5 border-t border-slate-100 p-5 text-sm">
                            <div class="flex items-center justify-between text-slate-500">
                                <span>Total produk</span>
                                <span class="font-semibold text-slate-800">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-500">
                                <span>Ongkos kirim</span>
                                <span class="font-semibold text-slate-800" x-text="metode === 'Kirim Kurir/Ekspedisi' ? rupiah(biayaKirim) : 'Gratis'">Gratis</span>
                            </div>
                            <div class="flex items-end justify-between border-t border-dashed border-slate-200 pt-3">
                                <span class="font-display text-base font-bold text-slate-900">Grand total</span>
                                <span class="font-display text-2xl font-extrabold text-brand-600"
                                      x-text="rupiah(subtotal + (metode === 'Kirim Kurir/Ekspedisi' ? biayaKirim : 0))">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                            </div>

                            <button type="submit" class="btn-primary mt-1 w-full !py-3.5 shadow-xl shadow-brand-500/25">
                                <x-icon name="check-circle" class="h-5 w-5" /> Buat Pesanan
                            </button>

                            <p class="text-center text-[10px] leading-relaxed text-slate-400">
                                Dengan menekan tombol di atas, Anda menyetujui syarat layanan CV Nekad Romo.
                            </p>
                        </div>
                    </div>
                </aside>
            </div>
        </form>
    </section>
@endsection
