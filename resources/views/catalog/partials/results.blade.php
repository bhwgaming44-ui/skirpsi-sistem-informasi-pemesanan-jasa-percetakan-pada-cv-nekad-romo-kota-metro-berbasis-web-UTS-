@php
    $hasilDitemukan = $products->total();
@endphp

<div class="flex flex-wrap items-center justify-between gap-2" aria-live="polite">
    <p class="text-sm text-slate-500">
        @if ($search !== '')
            Hasil pencarian untuk <span class="font-bold text-slate-800">"{{ $search }}"</span>
            — <span class="font-semibold text-slate-700">{{ $hasilDitemukan }}</span> layanan ditemukan.
        @elseif ($hasilDitemukan > 0)
            Menampilkan
            <span class="font-semibold text-slate-700">{{ $products->firstItem() }}–{{ $products->lastItem() }}</span>
            dari <span class="font-semibold text-slate-700">{{ $hasilDitemukan }}</span> layanan.
        @else
            Tidak ada layanan yang cocok dengan filter Anda.
        @endif
    </p>

    @if ($activeCategory > 0)
        <span class="badge bg-brand-50 text-brand-700 ring-1 ring-inset ring-brand-100">
            <x-icon name="filter" class="h-3.5 w-3.5" />
            {{ $categoryName ?? 'Kategori terpilih' }}
        </span>
    @endif
</div>

<div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
    @forelse ($products as $product)
        <x-product-card :product="$product" :saved="in_array($product->id_produk, $savedProductIds ?? [], true)" selectable />
    @empty
        <div class="sm:col-span-2 lg:col-span-3">
            <x-empty-state icon="search" title="Layanan tidak ditemukan"
                           message="Coba kata kunci lain, pilih kategori berbeda, atau reset filter untuk melihat seluruh katalog.">
                <a href="{{ route('catalog.index') }}" data-reset-filter class="btn-primary btn-sm mt-2">
                    <x-icon name="refresh" class="h-4 w-4" /> Lihat semua produk
                </a>
            </x-empty-state>
        </div>
    @endforelse
</div>

@if ($products->hasPages())
    <div class="mt-8" data-catalog-pagination>
        {{ $products->links() }}
    </div>
@endif
