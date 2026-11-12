@props([
    'product',
    'saved' => false,
    'variant' => 'icon',
])

@php
    $isIcon = $variant === 'icon';
    $rootClass = $isIcon ? 'absolute right-3 top-3 z-20' : 'w-full';
@endphp

<form method="POST" action="{{ route('saved.toggle', $product) }}"
      x-data="savedProductButton"
      data-saved="{{ $saved ? 'true' : 'false' }}"
      data-url="{{ route('saved.toggle', $product) }}"
      @submit.prevent="toggle"
      {{ $attributes->merge(['class' => $rootClass]) }}>
    @csrf

    @if ($isIcon)
        <button type="submit"
                class="group flex h-9 w-9 items-center justify-center rounded-full border border-white/70 bg-white/90 text-slate-600 shadow-lg backdrop-blur transition duration-200 hover:-translate-y-0.5 hover:text-brand-600 disabled:cursor-wait disabled:opacity-70 data-[saved=true]:border-brand-500 data-[saved=true]:bg-brand-600 data-[saved=true]:text-white data-[saved=true]:shadow-brand-600/30"
                data-saved="{{ $saved ? 'true' : 'false' }}"
                :data-saved="saved ? 'true' : 'false'"
                :aria-pressed="saved ? 'true' : 'false'"
                :title="saved ? 'Hapus dari produk tersimpan' : 'Simpan produk'"
                :aria-label="saved ? 'Hapus dari produk tersimpan' : 'Simpan produk'"
                :disabled="busy">
            <x-icon name="bookmark" class="h-4 w-4 group-data-[saved=true]:fill-current" />
        </button>
    @else
        <button type="submit"
                class="btn-white w-full !justify-center !py-3 disabled:cursor-wait disabled:opacity-70 data-[saved=true]:!border-brand-300 data-[saved=true]:!bg-brand-50 data-[saved=true]:!text-brand-700"
                data-saved="{{ $saved ? 'true' : 'false' }}"
                :data-saved="saved ? 'true' : 'false'"
                :aria-pressed="saved ? 'true' : 'false'"
                :disabled="busy">
            <x-icon name="bookmark" class="h-4 w-4" />
            <span x-text="saved ? 'Tersimpan di Produk Saya' : 'Simpan Produk'">
                {{ $saved ? 'Tersimpan di Produk Saya' : 'Simpan Produk' }}
            </span>
        </button>
    @endif
</form>
