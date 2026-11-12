@props(['product', 'saved' => false, 'selectable' => false])

@php
    $hargaMulai = $product->hargaMulai();
    $satuanLabel = match ($product->satuan) {
        'meter' => 'm²',
        'lembar' => 'lembar',
        'pcs' => 'pcs',
        'rim' => 'rim',
        default => $product->satuan,
    };
@endphp

<article {{ $attributes->merge(['class' => 'card-hover shine group relative flex flex-col overflow-hidden reveal']) }}>
    <x-save-button :product="$product" :saved="$saved" />

    <a href="{{ route('catalog.show', $product) }}" class="relative block aspect-[4/3] overflow-hidden bg-slate-100">
        <img src="{{ $product->gambarUrl() }}" alt="{{ $product->nama_produk }}" loading="lazy"
             class="h-full w-full object-cover transition duration-700 group-hover:scale-110">
        <div class="absolute inset-0 bg-gradient-to-t from-ink-950/70 via-ink-950/5 to-transparent opacity-90"></div>

        <div class="absolute left-3 top-3 flex flex-wrap gap-1.5">
            @if ($product->is_featured)
                <span class="badge bg-amber-400/95 text-amber-950 shadow-sm">
                    <x-icon name="star" class="h-3 w-3" /> Unggulan
                </span>
            @endif
            <span class="badge bg-white/90 text-slate-700 shadow-sm backdrop-blur">
                {{ $product->kategori->nama_kategori }}
            </span>
        </div>

        <div class="absolute bottom-3 left-3 right-3 flex items-end justify-between gap-2 text-white">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-white/70">Mulai dari</p>
                <p class="font-display text-xl font-bold">
                    Rp {{ number_format($hargaMulai, 0, ',', '.') }}
                    <span class="text-xs font-medium text-white/70">/{{ $satuanLabel }}</span>
                </p>
            </div>
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white/15 backdrop-blur transition group-hover:bg-white group-hover:text-brand-700">
                <x-icon name="arrow-right" class="h-4 w-4" />
            </span>
        </div>
    </a>

    <div class="flex flex-1 flex-col gap-3 p-5">
        <div>
            <h3 class="text-base font-bold leading-snug text-slate-900">
                <a href="{{ route('catalog.show', $product) }}" class="transition hover:text-brand-700">
                    {{ $product->nama_produk }}
                </a>
            </h3>
            <p class="mt-1.5 line-clamp-2 text-sm leading-relaxed text-slate-500">
                {{ \Illuminate\Support\Str::limit(strip_tags($product->deskripsi), 110) }}
            </p>
        </div>

        <div class="mt-auto flex flex-wrap items-center gap-2 text-[11px] font-semibold text-slate-500">
            <span class="inline-flex items-center gap-1 rounded-lg bg-slate-100 px-2 py-1">
                <x-icon name="box" class="h-3.5 w-3.5" /> Min. {{ $product->min_order }} {{ $satuanLabel }}
            </span>
            <span class="inline-flex items-center gap-1 rounded-lg bg-slate-100 px-2 py-1">
                <x-icon name="clock" class="h-3.5 w-3.5" /> {{ $product->estimasi_hari }} hari
            </span>
            <span class="inline-flex items-center gap-1 rounded-lg bg-slate-100 px-2 py-1">
                <x-icon name="layers" class="h-3.5 w-3.5" /> {{ $product->bahan_count ?? $product->bahanAktif?->count() ?? $product->bahan->count() }} bahan
            </span>
        </div>

        @if ($selectable)
            <label x-show="selectMode" x-cloak
                   class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-dashed border-slate-300 bg-slate-50/70 px-3 py-2 text-xs font-semibold text-slate-600 transition hover:border-brand-300 hover:bg-brand-50/60">
                <input type="checkbox" class="peer sr-only"
                       :checked="isSelected({{ $product->id_produk }})"
                       @change="toggleSelected({{ $product->id_produk }})">
                <span class="flex h-5 w-5 items-center justify-center rounded-md border-2 border-slate-300 bg-white text-transparent transition peer-checked:border-brand-600 peer-checked:bg-brand-600 peer-checked:text-white">
                    <x-icon name="check" class="h-3 w-3" />
                </span>
                <span>Pilih produk ini</span>
            </label>
        @endif

        <a href="{{ route('catalog.show', $product) }}"
           class="btn-primary w-full !py-2.5 text-xs">
            <x-icon name="cart" class="h-4 w-4" /> Pesan Sekarang
        </a>
    </div>
</article>
