@extends('layouts.app')

@section('title', 'Produk Tersimpan')
@section('description', 'Daftar layanan cetak CV Nekad Romo yang Anda simpan untuk dipesan kembali kapan saja.')

@section('content')
    <section class="relative overflow-hidden bg-ink-950 py-12 text-white lg:py-14">
        <img src="{{ asset('images/products/poster.jpg') }}" alt="" class="absolute inset-0 h-full w-full object-cover opacity-20">
        <div class="absolute inset-0 bg-gradient-to-r from-ink-950 via-ink-950/90 to-brand-900/60"></div>
        <div class="blob -right-10 top-0 h-64 w-64 bg-rose-500/20"></div>

        <div class="container-page relative">
            <nav class="flex items-center gap-2 text-[11px] font-semibold text-indigo-200/60">
                <a href="{{ route('home') }}" class="transition hover:text-white">Beranda</a>
                <x-icon name="chevron-right" class="h-3 w-3" />
                <a href="{{ route('catalog.index') }}" class="transition hover:text-white">Katalog</a>
                <x-icon name="chevron-right" class="h-3 w-3" />
                <span class="text-white">Produk Tersimpan</span>
            </nav>

            <div class="mt-4 flex flex-wrap items-end justify-between gap-4">
                <div class="max-w-2xl">
                    <span class="badge bg-white/10 text-white backdrop-blur">
                        <x-icon name="bookmark" class="h-3.5 w-3.5 text-rose-300" /> Wishlist cetak Anda
                    </span>
                    <h1 class="mt-3 font-display text-3xl font-extrabold tracking-tight sm:text-4xl">Produk Tersimpan</h1>
                    <p class="mt-2 max-w-xl text-sm leading-relaxed text-indigo-100/70">
                        Simpan layanan favorit tanpa harus mencarinya ulang. Daftar ini tetap ada meskipun Anda berpindah menu.
                    </p>
                </div>
                <div class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 py-3 backdrop-blur">
                    <x-icon name="bookmark" class="h-6 w-6 text-rose-300" />
                    <div class="leading-tight">
                        <p class="font-display text-lg font-bold"><span data-saved-total>{{ $products->total() }}</span></p>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-indigo-200/60">Produk disimpan</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="container-page relative z-10 -mt-6 pb-16"
             x-data="savedProductsPage({{ $products->total() }})"
             @saved-products-changed.window="handle($event)">
        <div class="card p-4 sm:p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-500 ring-1 ring-inset ring-rose-100">
                        <x-icon name="bookmark" class="h-4.5 w-4.5" />
                    </span>
                    <div>
                        <p class="text-sm font-bold text-slate-800">Daftar simpanan Anda</p>
                        <p class="text-[11px] text-slate-500">
                            <span data-saved-total>{{ $products->total() }}</span> produk tersimpan
                            @auth
                                di akun {{ auth()->user()->nama }}.
                            @else
                                di perangkat ini.
                            @endauth
                        </p>
                    </div>
                </div>

                <a href="{{ route('catalog.index') }}" class="btn-ghost btn-sm">
                    <x-icon name="plus" class="h-4 w-4" /> Tambah produk
                </a>
            </div>

            @guest
                <div class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-amber-200 bg-amber-50/80 px-4 py-3">
                    <p class="flex items-start gap-2 text-xs leading-relaxed text-amber-800">
                        <x-icon name="info" class="mt-0.5 h-4 w-4 shrink-0 text-amber-500" />
                        <span>Produk tersimpan sementara di perangkat ini. Masuk ke akun agar daftar tetap tersimpan dan bisa dibuka dari perangkat lain.</span>
                    </p>
                    <a href="{{ route('login') }}" class="btn-primary btn-sm shrink-0">Masuk akun</a>
                </div>
            @endguest
        </div>

        <div class="mt-6">
            @if ($products->isEmpty())
                <x-empty-state icon="bookmark" title="Belum ada produk tersimpan"
                               message="Tekan ikon bookmark pada produk di katalog untuk menyimpannya ke daftar ini.">
                    <a href="{{ route('catalog.index') }}" class="btn-primary btn-sm mt-2">
                        <x-icon name="search" class="h-4 w-4" /> Jelajahi katalog
                    </a>
                </x-empty-state>
            @else
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($products as $product)
                        <div data-saved-item="{{ $product->id_produk }}" class="transition duration-200">
                            <x-product-card :product="$product" :saved="true" />
                        </div>
                    @endforeach
                </div>

                @if ($products->hasPages())
                    <div class="mt-8">
                        {{ $products->links() }}
                    </div>
                @endif
            @endif
        </div>
    </section>
@endsection
