@extends('layouts.app')

@section('title', $product->nama_produk)
@section('description', \Illuminate\Support\Str::limit(strip_tags($product->deskripsi), 150))

@php
    $satuanLabel = match ($product->satuan) {
        'meter' => 'm²',
        'lembar' => 'lembar',
        'pcs' => 'pcs',
        'rim' => 'rim',
        default => $product->satuan,
    };

    $calculatorConfig = [
        'materialId' => (string) old('material_id', $product->bahanAktif->first()->id_bahan ?? 0),
        'panjang' => (float) old('panjang', 1),
        'lebar' => (float) old('lebar', 1),
        'jumlah' => (int) old('jumlah', 1),
        'finishingIds' => array_map('strval', old('finishing_ids', [])),
        'tipeDesain' => old('tipe_desain', 'Punya Desain Sendiri'),
        'materials' => $product->bahanAktif->map(fn ($bahan) => [
            'id' => (string) $bahan->id_bahan,
            'nama' => $bahan->nama_bahan,
            'harga' => (float) $bahan->harga_satuan,
        ])->values()->all(),
        'finishings' => $product->finishing->map(fn ($finishing) => [
            'id' => (string) $finishing->id_finishing,
            'nama' => $finishing->nama_finishing,
            'biaya' => (float) $finishing->biaya_default,
        ])->values()->all(),
        'designFee' => (int) config('percetakan.biaya_desain', 25000),
        'satuan' => $product->satuan,
    ];
@endphp

@section('content')
    <section class="border-b border-slate-200/70 bg-white">
        <div class="container-page py-5">
            <nav class="flex flex-wrap items-center gap-2 text-[11px] font-semibold text-slate-400">
                <a href="{{ route('home') }}" class="transition hover:text-brand-600">Beranda</a>
                <x-icon name="chevron-right" class="h-3 w-3" />
                <a href="{{ route('catalog.index') }}" class="transition hover:text-brand-600">Katalog</a>
                <x-icon name="chevron-right" class="h-3 w-3" />
                <a href="{{ route('catalog.index', ['kategori' => $product->id_kategori]) }}" class="transition hover:text-brand-600">{{ $product->kategori->nama_kategori }}</a>
                <x-icon name="chevron-right" class="h-3 w-3" />
                <span class="text-slate-700">{{ $product->nama_produk }}</span>
            </nav>
        </div>
    </section>

    <section class="container-page py-8 lg:py-10"
             x-data="priceCalculator({{ \Illuminate\Support\Js::from($calculatorConfig) }})">

        <div class="grid gap-8 lg:grid-cols-[1.55fr_1fr]">
            {{-- ================= LEFT ================= --}}
            <div class="space-y-6">
                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[var(--shadow-card)]"
                     x-data="{ activeImage: @js($product->gambarUrl()) }">
                    <div class="relative aspect-[16/10] overflow-hidden bg-slate-100">
                        <img :src="activeImage" src="{{ $product->gambarUrl() }}" alt="{{ $product->nama_produk }}" class="h-full w-full object-cover transition duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-ink-950/60 via-transparent to-transparent"></div>
                        <div class="absolute left-4 top-4 flex flex-wrap gap-2">
                            @if ($product->is_featured)
                                <span class="badge bg-amber-400 text-amber-950 shadow"><x-icon name="star" class="h-3 w-3" /> Unggulan</span>
                            @endif
                            <span class="badge bg-white/90 text-slate-700 shadow backdrop-blur">{{ $product->kategori->nama_kategori }}</span>
                        </div>
                        <div class="absolute bottom-4 left-4 right-4 flex flex-wrap items-end justify-between gap-2 text-white">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-wider text-white/70">Harga mulai</p>
                                <p class="font-display text-2xl font-extrabold">
                                    Rp {{ number_format($product->hargaMulai(), 0, ',', '.') }}
                                    <span class="text-sm font-medium text-white/70">/ {{ $satuanLabel }}</span>
                                </p>
                            </div>
                            <span class="badge bg-white/15 text-white backdrop-blur">
                                <x-icon name="clock" class="h-3.5 w-3.5" /> Estimasi {{ $product->estimasi_hari }} hari
                            </span>
                        </div>
                    </div>

                    @if ($product->galeri->isNotEmpty())
                        @php
                            $galleryImages = collect([
                                ['url' => $product->gambarUrl(), 'label' => 'Gambar utama'],
                            ])->concat($product->galeri->map(fn ($image) => [
                                'url' => $image->url(),
                                'label' => 'Foto galeri',
                            ]));
                        @endphp

                        <div class="flex gap-2.5 overflow-x-auto border-b border-slate-100 bg-white px-4 py-3 sm:px-6">
                            @foreach ($galleryImages as $image)
                                <button type="button"
                                        @click="activeImage = @js($image['url'])"
                                        class="group relative h-16 w-20 shrink-0 overflow-hidden rounded-xl border-2 transition sm:h-20 sm:w-24"
                                        :class="activeImage === @js($image['url']) ? 'border-brand-500 ring-2 ring-brand-100' : 'border-slate-200 opacity-75 hover:border-brand-300 hover:opacity-100'"
                                        title="{{ $image['label'] }}">
                                    <img src="{{ $image['url'] }}" alt="{{ $product->nama_produk }} — {{ $image['label'] }}"
                                         class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                                </button>
                            @endforeach
                        </div>
                    @endif

                    <div class="p-6 sm:p-8">
                        <h1 class="font-display text-2xl font-extrabold leading-tight text-slate-900 sm:text-3xl">{{ $product->nama_produk }}</h1>
                        <p class="mt-3 text-sm leading-relaxed text-slate-500">{{ $product->deskripsi }}</p>

                        <div class="mt-6 grid gap-3 sm:grid-cols-3">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-3.5">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white text-brand-600 shadow-sm">
                                    <x-icon name="box" class="h-4 w-4" />
                                </span>
                                <p class="mt-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">Satuan</p>
                                <p class="text-sm font-bold text-slate-800">{{ $satuanLabel }}</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-3.5">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white text-brand-600 shadow-sm">
                                    <x-icon name="layers" class="h-4 w-4" />
                                </span>
                                <p class="mt-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">Pilihan bahan</p>
                                <p class="text-sm font-bold text-slate-800">{{ $product->bahanAktif->count() }} jenis</p>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-3.5">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white text-brand-600 shadow-sm">
                                    <x-icon name="scissors" class="h-4 w-4" />
                                </span>
                                <p class="mt-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">Finishing</p>
                                <p class="text-sm font-bold text-slate-800">{{ $product->finishing->count() }} opsi</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ============ ORDER FORM ============ --}}
                <form id="order-form" method="POST" action="{{ route('cart.store') }}" enctype="multipart/form-data"
                      class="space-y-6">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id_produk }}">

                    @if ($errors->any())
                        <div class="flex items-start gap-2.5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                            <x-icon name="alert" class="mt-0.5 h-4.5 w-4.5 shrink-0" />
                            <div>
                                <p class="font-bold">Ada data yang perlu diperbaiki:</p>
                                <ul class="mt-1 list-inside list-disc space-y-0.5 text-xs">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    {{-- Bahan --}}
                    <div class="card p-5 sm:p-6">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600">
                                <span class="font-display text-xs font-extrabold">1</span>
                            </span>
                            <div>
                                <h2 class="font-display text-base font-bold text-slate-900">Pilih bahan cetak</h2>
                                <p class="text-xs text-slate-400">Harga di bawah adalah harga per {{ $satuanLabel }}.</p>
                            </div>
                        </div>

                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            @foreach ($product->bahanAktif as $bahan)
                                <label class="relative flex cursor-pointer items-start gap-3 rounded-2xl border-2 border-slate-200 bg-white p-4 transition hover:border-brand-300 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50/60 has-[:checked]:shadow-md has-[:checked]:shadow-brand-500/10">
                                    <input type="radio" name="material_id" value="{{ $bahan->id_bahan }}" x-model="materialId"
                                           class="mt-1 h-4 w-4 shrink-0 border-slate-300 text-brand-600 focus:ring-brand-200" @checked((string) old('material_id', $product->bahanAktif->first()->id_bahan) === (string) $bahan->id_bahan)>
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-sm font-bold text-slate-800">{{ $bahan->nama_bahan }}</span>
                                        <span class="mt-1 block font-display text-sm font-extrabold text-brand-600">
                                            Rp {{ number_format((float) $bahan->harga_satuan, 0, ',', '.') }}
                                            <span class="text-[10px] font-semibold text-slate-400">/ {{ $satuanLabel }}</span>
                                        </span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <x-form-error name="material_id" />
                    </div>

                    {{-- Ukuran & jumlah --}}
                    <div class="card p-5 sm:p-6">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600">
                                <span class="font-display text-xs font-extrabold">2</span>
                            </span>
                            <div>
                                <h2 class="font-display text-base font-bold text-slate-900">Atur ukuran & jumlah</h2>
                                <p class="text-xs text-slate-400">
                                    @if ($product->satuan === 'meter')
                                        Harga dihitung dari luas (panjang × lebar) × jumlah.
                                    @else
                                        Harga dihitung dari harga satuan × jumlah.
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 grid gap-4 {{ $product->satuan === 'meter' ? 'sm:grid-cols-3' : 'sm:grid-cols-2' }}">
                            @if ($product->satuan === 'meter')
                                <div>
                                    <label for="panjang" class="label">Panjang (meter)</label>
                                    <input id="panjang" name="panjang" type="number" step="0.01" min="0.1" max="50"
                                           x-model.number="panjang" value="{{ old('panjang', 1) }}" required class="input">
                                    <x-form-error name="panjang" />
                                </div>
                                <div>
                                    <label for="lebar" class="label">Lebar (meter)</label>
                                    <input id="lebar" name="lebar" type="number" step="0.01" min="0.1" max="50"
                                           x-model.number="lebar" value="{{ old('lebar', 1) }}" required class="input">
                                    <x-form-error name="lebar" />
                                </div>
                            @endif

                            <div>
                                <label for="jumlah" class="label">Jumlah ({{ $satuanLabel }})</label>
                                <div class="flex items-stretch gap-2">
                                    <button type="button" @click="jumlah = Math.max(1, Number(jumlah) - 1)"
                                            class="flex w-11 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:border-brand-300 hover:text-brand-600">
                                        <x-icon name="minus" class="h-4 w-4" />
                                    </button>
                                    <input id="jumlah" name="jumlah" type="number" min="1" max="5000"
                                           x-model.number="jumlah" value="{{ old('jumlah', 1) }}" required
                                           class="input text-center font-bold">
                                    <button type="button" @click="jumlah = Math.min(5000, Number(jumlah) + 1)"
                                            class="flex w-11 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:border-brand-300 hover:text-brand-600">
                                        <x-icon name="plus" class="h-4 w-4" />
                                    </button>
                                </div>
                                <x-form-error name="jumlah" />
                                <p class="mt-1.5 text-[11px] text-slate-400">Jumlah bebas — minimal 1 {{ $satuanLabel }}.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Finishing --}}
                    <div class="card p-5 sm:p-6">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600">
                                <span class="font-display text-xs font-extrabold">3</span>
                            </span>
                            <div>
                                <h2 class="font-display text-base font-bold text-slate-900">Finishing tambahan <span class="text-xs font-medium text-slate-400">(opsional)</span></h2>
                                <p class="text-xs text-slate-400">Biaya finishing dikalikan jumlah pesanan.</p>
                            </div>
                        </div>

                        @if ($product->finishing->isEmpty())
                            <p class="mt-4 rounded-xl bg-slate-50 px-4 py-3 text-sm text-slate-500">Produk ini tidak memiliki opsi finishing tambahan.</p>
                        @else
                            <div class="mt-4 grid gap-2.5 sm:grid-cols-2">
                                @foreach ($product->finishing as $finishing)
                                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-white p-3.5 transition hover:border-brand-300"
                                           :class="finishingIds.map(String).includes('{{ $finishing->id_finishing }}') ? '!border-brand-500 !bg-brand-50/60' : ''">
                                        <input type="checkbox" name="finishing_ids[]" value="{{ $finishing->id_finishing }}"
                                               x-model="finishingIds" class="mt-0.5 h-4 w-4 shrink-0 rounded border-slate-300 text-brand-600 focus:ring-brand-200">
                                        <span class="min-w-0 flex-1">
                                            <span class="block text-xs font-bold text-slate-800">{{ $finishing->nama_finishing }}</span>
                                            <span class="mt-0.5 block text-[11px] font-semibold text-brand-600">
                                                + Rp {{ number_format((float) $finishing->biaya_default, 0, ',', '.') }} / {{ $satuanLabel }}
                                            </span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                        <x-form-error name="finishing_ids" />
                    </div>

                    {{-- Desain & file --}}
                    <div class="card p-5 sm:p-6" x-data="{ fileName: '' }">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600">
                                <span class="font-display text-xs font-extrabold">4</span>
                            </span>
                            <div>
                                <h2 class="font-display text-base font-bold text-slate-900">Desain & file cetak</h2>
                                <p class="text-xs text-slate-400">Unggah desain sendiri atau gunakan jasa desain kami.</p>
                            </div>
                        </div>

                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            <label class="flex cursor-pointer items-start gap-3 rounded-2xl border-2 border-slate-200 p-4 transition hover:border-brand-300 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50/60">
                                <input type="radio" name="tipe_desain" value="Punya Desain Sendiri" x-model="tipeDesain"
                                       class="mt-1 h-4 w-4 border-slate-300 text-brand-600 focus:ring-brand-200" @checked(old('tipe_desain', 'Punya Desain Sendiri') === 'Punya Desain Sendiri')>
                                <span>
                                    <span class="block text-sm font-bold text-slate-800">Punya desain sendiri</span>
                                    <span class="mt-0.5 block text-[11px] text-slate-500">Tanpa biaya tambahan. Unggah file siap cetak.</span>
                                </span>
                            </label>
                            <label class="flex cursor-pointer items-start gap-3 rounded-2xl border-2 border-slate-200 p-4 transition hover:border-brand-300 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50/60">
                                <input type="radio" name="tipe_desain" value="Jasa Desain Nekad Romo" x-model="tipeDesain"
                                       class="mt-1 h-4 w-4 border-slate-300 text-brand-600 focus:ring-brand-200" @checked(old('tipe_desain') === 'Jasa Desain Nekad Romo')>
                                <span>
                                    <span class="block text-sm font-bold text-slate-800">Jasa desain Nekad Romo</span>
                                    <span class="mt-0.5 block text-[11px] text-brand-600 font-semibold">+ Rp {{ number_format((int) config('percetakan.biaya_desain'), 0, ',', '.') }} / pesanan</span>
                                </span>
                            </label>
                        </div>

                        <div class="mt-4" x-show="tipeDesain === 'Jasa Desain Nekad Romo'" x-transition>
                            <label for="catatan_desain" class="label">Brief desain</label>
                            <textarea id="catatan_desain" name="catatan_desain" rows="3"
                                      placeholder="Contoh: desain banner promo diskon 20%, warna dominan biru, ada logo toko."
                                      class="textarea">{{ old('catatan_desain') }}</textarea>
                        </div>

                        <div class="mt-4">
                            <label class="label">File desain <span class="font-medium normal-case tracking-normal text-slate-400">(JPG, PNG, WEBP, PDF, ZIP — maks. 20 MB)</span></label>
                            <label class="group flex cursor-pointer flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50/60 px-4 py-7 text-center transition hover:border-brand-400 hover:bg-brand-50/40">
                                <input type="file" name="file_desain" accept=".jpg,.jpeg,.png,.webp,.pdf,.zip"
                                       class="sr-only"
                                       @change="fileName = $event.target.files[0]?.name ?? ''">
                                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-white text-brand-500 shadow-sm ring-1 ring-slate-200">
                                    <x-icon name="upload" class="h-5 w-5" />
                                </span>
                                <span class="text-sm font-semibold text-slate-700" x-show="! fileName">Klik untuk unggah file desain</span>
                                <span class="text-sm font-semibold text-brand-700" x-show="fileName" x-text="fileName"></span>
                                <span class="text-[11px] text-slate-400">File opsional, bisa dikirim menyusul via WhatsApp.</span>
                            </label>
                            <x-form-error name="file_desain" />
                        </div>
                    </div>
                </form>
            </div>

            {{-- ================= RIGHT / SUMMARY ================= --}}
            <aside class="lg:sticky lg:top-24 lg:self-start">
                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-[var(--shadow-soft)]">
                    <div class="relative bg-gradient-to-br from-brand-600 via-brand-500 to-indigo-600 p-5 text-white">
                        <div class="pointer-events-none absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/15 blur-2xl"></div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-white/70">Estimasi total</p>
                        <p class="mt-1 font-display text-3xl font-extrabold" x-text="format(total)">Rp 0</p>
                        <p class="mt-1 text-[11px] text-indigo-100/80" x-text="labelSatuan()"></p>
                    </div>

                    <div class="space-y-3 p-5 text-sm">
                        <div class="flex items-center justify-between gap-3 text-slate-500">
                            <span class="flex items-center gap-1.5"><x-icon name="layers" class="h-4 w-4 text-slate-400" /> Harga bahan</span>
                            <span class="font-semibold text-slate-800" x-text="format(hargaDasar)">Rp 0</span>
                        </div>
                        <div class="flex items-center justify-between gap-3 text-slate-500">
                            <span class="flex items-center gap-1.5"><x-icon name="scissors" class="h-4 w-4 text-slate-400" /> Finishing</span>
                            <span class="font-semibold text-slate-800" x-text="format(biayaFinishing)">Rp 0</span>
                        </div>
                        <div class="flex items-center justify-between gap-3 text-slate-500" x-show="biayaDesain > 0">
                            <span class="flex items-center gap-1.5"><x-icon name="palette" class="h-4 w-4 text-slate-400" /> Jasa desain</span>
                            <span class="font-semibold text-slate-800" x-text="format(biayaDesain)">Rp 0</span>
                        </div>

                        <div class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 px-3 py-2.5">
                            <span class="text-xs font-semibold text-slate-500">Volume cetak</span>
                            <span class="text-xs font-bold text-slate-800" x-text="labelSatuan()">-</span>
                        </div>

                        <div class="border-t border-dashed border-slate-200 pt-3">
                            <div class="flex items-center justify-between gap-3">
                                <span class="font-display text-base font-bold text-slate-900">Total estimasi</span>
                                <span class="font-display text-xl font-extrabold text-brand-600" x-text="format(total)">Rp 0</span>
                            </div>
                            <p class="mt-1 text-[10px] leading-relaxed text-slate-400">
                                *Harga final dikonfirmasi admin setelah file desain diperiksa. Belum termasuk ongkir.
                            </p>
                        </div>

                        <button type="submit" form="order-form" class="btn-primary w-full !py-3.5 shadow-xl shadow-brand-500/25">
                            <x-icon name="cart" class="h-5 w-5" /> Tambah ke Keranjang
                        </button>
                        <x-save-button :product="$product" :saved="in_array($product->id_produk, $savedProductIds, true)" variant="full" />
                        <a href="{{ route('catalog.index') }}" class="btn-white w-full">
                            <x-icon name="arrow-left" class="h-4 w-4" /> Lanjut lihat produk
                        </a>
                    </div>

                    <div class="border-t border-slate-100 bg-slate-50/60 p-5">
                        <div class="flex items-start gap-2.5">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                                <x-icon name="shield" class="h-4.5 w-4.5" />
                            </span>
                            <div>
                                <p class="text-xs font-bold text-slate-800">Transaksi aman</p>
                                <p class="mt-0.5 text-[11px] leading-relaxed text-slate-500">
                                    Pembayaran diproses otomatis. Status pesanan diperbarui tanpa perlu konfirmasi manual.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 rounded-2xl border border-brand-100 bg-brand-50/60 p-4">
                    <p class="flex items-center gap-1.5 text-xs font-bold text-brand-800">
                        <x-icon name="info" class="h-4 w-4" /> Butuh bantuan?
                    </p>
                    <p class="mt-1 text-[11px] leading-relaxed text-brand-700/80">
                        Hubungi admin via WhatsApp untuk konsultasi bahan atau permintaan khusus.
                    </p>
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $storeSetting->no_telepon ?? '6281278901234') }}" target="_blank" rel="noopener"
                       class="mt-2.5 inline-flex items-center gap-1.5 text-xs font-bold text-brand-700 transition hover:text-brand-800">
                        <x-icon name="whatsapp" class="h-4 w-4" /> Chat admin sekarang
                    </a>
                </div>
            </aside>
        </div>

        {{-- Related --}}
        @if ($related->isNotEmpty())
            <div class="mt-14">
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <h2 class="font-display text-2xl font-extrabold text-slate-900">Produk serupa</h2>
                        <p class="mt-1 text-sm text-slate-500">Layanan lain di kategori {{ $product->kategori->nama_kategori }}.</p>
                    </div>
                    <a href="{{ route('catalog.index', ['kategori' => $product->id_kategori]) }}" class="btn-ghost btn-sm hidden sm:inline-flex">
                        Lihat semua <x-icon name="arrow-right" class="h-4 w-4" />
                    </a>
                </div>

                <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $item)
                        <x-product-card :product="$item" :saved="in_array($item->id_produk, $savedProductIds, true)" />
                    @endforeach
                </div>
            </div>
        @endif
    </section>
@endsection
