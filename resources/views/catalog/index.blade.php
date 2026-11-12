@extends('layouts.app')

@section('title', 'Katalog Layanan Cetak')
@section('description', 'Jelajahi layanan cetak CV Nekad Romo Kota Metro: banner, brosur, kartu nama, undangan, kaos, mug, nota, dan paper bag custom.')

@section('content')
    <div x-data="catalogFilter({
        endpoint: $el.dataset.endpoint,
        search: $el.dataset.search,
        sort: $el.dataset.sort,
        category: $el.dataset.category,
        ids: $el.dataset.ids,
        total: Number($el.dataset.total)
    })"
         data-endpoint="{{ route('catalog.filter', [], false) }}"
         data-search="{{ $search }}"
         data-sort="{{ $sort }}"
         data-category="{{ $activeCategory > 0 ? $activeCategory : '' }}"
         data-total="{{ $products->total() }}"
         data-ids="{{ implode(',', $allProductIds) }}">
        <section class="relative overflow-hidden bg-ink-950 py-12 text-white lg:py-16">
            <img src="{{ asset('images/products/banner.jpg') }}" alt="" class="absolute inset-0 h-full w-full object-cover opacity-20">
            <div class="absolute inset-0 bg-gradient-to-r from-ink-950 via-ink-950/90 to-brand-900/60"></div>
            <div class="blob -right-10 top-0 h-64 w-64 bg-brand-500/25"></div>

            <div class="container-page relative">
                <nav class="flex items-center gap-2 text-[11px] font-semibold text-indigo-200/60">
                    <a href="{{ route('home') }}" class="transition hover:text-white">Beranda</a>
                    <x-icon name="chevron-right" class="h-3 w-3" />
                    <span class="text-white">Katalog</span>
                </nav>

                <div class="mt-4 flex flex-wrap items-end justify-between gap-4">
                    <div class="max-w-2xl">
                        <h1 class="font-display text-3xl font-extrabold tracking-tight sm:text-4xl">Katalog Layanan Cetak</h1>
                        <p class="mt-2 max-w-xl text-sm leading-relaxed text-indigo-100/70">
                            Pilih layanan, tentukan bahan dan finishing, lalu lihat estimasi harga secara langsung sebelum masuk keranjang. Ingin memesan beberapa layanan sekaligus? Gunakan tombol <span class="font-semibold text-white">Pilih banyak produk</span>.
                        </p>
                    </div>
                    <div class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 py-3 backdrop-blur">
                        <x-icon name="package" class="h-6 w-6 text-accent-400" />
                        <div class="leading-tight">
                            <p class="font-display text-lg font-bold" x-text="total">{{ $products->total() }}</p>
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-indigo-200/60"
                               x-text="hasFilters ? 'Layanan ditemukan' : 'Layanan tersedia'">
                                Layanan tersedia
                            </p>
                        </div>
                    </div>
                </div>

                <form method="GET" action="{{ route('catalog.index') }}" @submit.prevent="load(1)"
                      class="mt-7 flex flex-col gap-3 sm:flex-row" role="search">
                    <div class="relative flex-1">
                        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                            <x-icon name="search" class="h-5 w-5" />
                        </span>
                        <input type="search" name="q" x-model="search" x-ref="search"
                               @input.debounce.250ms="load(1)" @search="load(1)"
                               value="{{ $search }}" placeholder="Cari banner, brosur, kaos, mug..."
                               autocomplete="off"
                               class="input !h-12 !rounded-2xl !border-white/15 !bg-white/95 !pl-12 !pr-12 !text-slate-800 shadow-lg">
                        <button type="button" x-show="search !== ''" x-cloak @click="clearSearch()"
                                class="absolute right-3 top-1/2 flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-full bg-slate-100 text-slate-500 transition hover:bg-rose-50 hover:text-rose-500"
                                aria-label="Hapus kata kunci">
                            <x-icon name="close" class="h-3.5 w-3.5" />
                        </button>
                    </div>

                    @if ($activeCategory)
                        <input type="hidden" name="kategori" value="{{ $activeCategory }}">
                    @endif

                    <div class="flex gap-3">
                        <select name="sort" x-model="sort" @change="load(1)"
                                class="select !h-12 !w-auto !rounded-2xl !border-white/15 !bg-white/95 !text-slate-800 shadow-lg">
                            <option value="terbaru" @selected($sort === 'terbaru')>Urutkan: Unggulan</option>
                            <option value="nama" @selected($sort === 'nama')>Nama A-Z</option>
                            <option value="termurah" @selected($sort === 'termurah')>Harga termurah</option>
                            <option value="termahal" @selected($sort === 'termahal')>Harga termahal</option>
                        </select>
                        <button type="submit" class="btn-primary !h-12 !rounded-2xl !px-5">
                            <x-icon name="search" class="h-4.5 w-4.5" /> Cari
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <section class="container-page -mt-6 relative z-10 pb-16" :class="selectMode ? '!pb-44' : ''">
            <div class="card p-4 sm:p-5">
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('catalog.index', array_filter(['q' => $search ?: null, 'sort' => $sort !== 'terbaru' ? $sort : null])) }}"
                       @click.prevent="setCategory('')"
                       data-active="{{ ! $activeCategory ? 'true' : 'false' }}"
                       :data-active="category === '' ? 'true' : 'false'"
                       class="badge bg-slate-100 text-slate-600 transition hover:bg-slate-200 data-[active=true]:!bg-brand-600 data-[active=true]:!text-white data-[active=true]:shadow-md data-[active=true]:shadow-brand-500/25">
                        <x-icon name="grid" class="h-3.5 w-3.5" /> Semua
                    </a>
                    @foreach ($categories as $category)
                        <a href="{{ route('catalog.index', array_filter(['kategori' => $category->id_kategori, 'q' => $search ?: null, 'sort' => $sort !== 'terbaru' ? $sort : null])) }}"
                           @click.prevent="setCategory('{{ $category->id_kategori }}')"
                           data-active="{{ $activeCategory === $category->id_kategori ? 'true' : 'false' }}"
                           :data-active="category === '{{ $category->id_kategori }}' ? 'true' : 'false'"
                           class="badge bg-slate-100 text-slate-600 transition hover:bg-slate-200 data-[active=true]:!bg-brand-600 data-[active=true]:!text-white data-[active=true]:shadow-md data-[active=true]:shadow-brand-500/25">
                            <x-icon :name="$category->ikon" class="h-3.5 w-3.5" />
                            {{ $category->nama_kategori }}
                            <span class="opacity-60">({{ $category->produk_count }})</span>
                        </a>
                    @endforeach

                    <button type="button" @click="toggleSelectMode()"
                            data-active="false"
                            :data-active="selectMode ? 'true' : 'false'"
                            class="badge border border-dashed border-brand-300 bg-brand-50/60 text-brand-700 transition hover:bg-brand-100 data-[active=true]:!border-brand-600 data-[active=true]:!bg-brand-600 data-[active=true]:!text-white">
                        <x-icon name="check" class="h-3.5 w-3.5" />
                        <span x-text="selectMode ? 'Selesai memilih' : 'Pilih banyak produk'">Pilih banyak produk</span>
                    </button>

                    <a href="{{ route('catalog.index') }}"
                       @click.prevent="resetFilters()" x-show="hasFilters"
                       @if (! $search && $sort === 'terbaru' && ! $activeCategory) x-cloak @endif
                       class="ml-auto inline-flex items-center gap-1 text-[11px] font-bold text-rose-500 transition hover:text-rose-600">
                        <x-icon name="refresh" class="h-3.5 w-3.5" /> Reset filter
                    </a>
                </div>
            </div>

            <div class="mt-5 scroll-mt-24" x-ref="results" @click="onResultsClick($event)">
                @include('catalog.partials.results')
            </div>
        </section>

        {{-- Panel pilih banyak produk --}}
        <div x-show="selectMode" x-cloak x-transition.opacity.duration.200ms
             class="fixed inset-x-3 bottom-4 z-40 mx-auto max-w-3xl">
            <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white/95 p-3 shadow-[0_30px_70px_-25px_rgba(15,23,42,0.55)] backdrop-blur sm:flex-row sm:items-center sm:justify-between sm:p-4">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-indigo-600 text-white shadow-lg shadow-brand-500/25">
                        <x-icon name="cart" class="h-5 w-5" />
                    </span>
                    <div class="leading-tight">
                        <p class="text-sm font-bold text-slate-800">
                            <span x-text="selected.length">0</span> produk dipilih
                        </p>
                        <p class="text-[11px] text-slate-500">
                            Produk masuk keranjang dengan bahan pertama &amp; ukuran standar.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" @click="toggleSelectAll()" class="btn-white btn-sm flex-1 sm:flex-none">
                        <span x-text="allSelected ? 'Batalkan semua' : 'Pilih semua (' + allIds.length + ')'">Pilih semua</span>
                    </button>

                    <form method="POST" action="{{ route('cart.bulk') }}" class="flex-1 sm:flex-none">
                        @csrf
                        <template x-for="id in selected" :key="id">
                            <input type="hidden" name="product_ids[]" :value="id">
                        </template>
                        <button type="submit"
                                class="btn-primary btn-sm w-full disabled:cursor-not-allowed disabled:opacity-50"
                                :disabled="selected.length === 0">
                            <x-icon name="cart" class="h-4 w-4" /> Tambah ke keranjang
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
