<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#6d5df6">
    <meta name="description" content="@yield('description', 'CV Nekad Romo Kota Metro — percetakan digital untuk banner, brosur, merchandise, dan kebutuhan branding usaha Anda. Pesan online, bayar otomatis, lacak produksi real-time.')">

    <title>@yield('title', 'Cetak Online') · {{ $storeSetting->nama_perusahaan ?? 'CV Nekad Romo' }}</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo.svg') }}">

    @foreach (['success', 'error', 'warning', 'info'] as $flashKey)
        @if (session($flashKey))
            <meta name="flash-{{ $flashKey }}" content="{{ session($flashKey) }}">
        @endif
    @endforeach

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f5f6fb]" x-data="{ mobileNav: false }">

    {{-- Top info bar --}}
    <div class="hidden bg-ink-950 text-white/70 lg:block">
        <div class="container-page flex items-center justify-between py-2 text-[11px] font-medium">
            <p class="flex items-center gap-2">
                <x-icon name="map-pin" class="h-3.5 w-3.5 text-accent-400" />
                {{ $storeSetting->alamat ?? 'Kota Metro, Lampung' }}
            </p>
            <div class="flex items-center gap-5">
                <span class="flex items-center gap-1.5">
                    <x-icon name="clock" class="h-3.5 w-3.5 text-accent-400" />
                    {{ $storeSetting->jam_operasional ?? 'Senin - Sabtu, 08.00 - 20.00 WIB' }}
                </span>
                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $storeSetting->no_telepon ?? '') }}" class="flex items-center gap-1.5 transition hover:text-white">
                    <x-icon name="phone" class="h-3.5 w-3.5 text-accent-400" />
                    {{ $storeSetting->no_telepon ?? '0812-7890-1234' }}
                </a>
            </div>
        </div>
    </div>

    <header class="sticky top-0 z-40 border-b border-white/60 bg-white/85 backdrop-blur-xl">
        <div class="container-page flex h-16 items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2.5">
                <img src="{{ asset('images/logo.svg') }}" alt="Logo Nekad Romo Print" class="h-10 w-10 rounded-xl shadow-lg shadow-brand-500/25">
                <span class="leading-tight">
                    <span class="block font-display text-base font-extrabold tracking-tight text-slate-900">Nekad Romo<span class="text-brand-600">.</span>Print</span>
                    <span class="block text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-400">Kota Metro</span>
                </span>
            </a>

            <nav class="hidden items-center gap-1 rounded-full bg-slate-100/80 p-1 lg:flex">
                <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'nav-link-active' : '' }}">Beranda</a>
                <a href="{{ route('catalog.index') }}" class="nav-link {{ request()->routeIs('catalog.*') ? 'nav-link-active' : '' }}">Katalog</a>
                <a href="{{ route('tracking.index') }}" class="nav-link {{ request()->routeIs('tracking.*') ? 'nav-link-active' : '' }}">Lacak Pesanan</a>
                @auth
                    <a href="{{ route('customer.dashboard') }}" class="nav-link {{ request()->routeIs('customer.*') ? 'nav-link-active' : '' }}">Akun Saya</a>
                @endauth
            </nav>

            <div class="flex items-center gap-2">
                <a href="{{ route('saved.index') }}" title="Produk tersimpan"
                   class="relative flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-200 hover:text-brand-700">
                    <x-icon name="bookmark" class="h-5 w-5" />
                    <span data-saved-badge
                          class="absolute -right-1.5 -top-1.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-gradient-to-br from-rose-500 to-pink-600 px-1 text-[10px] font-bold text-white shadow-md {{ ($savedCount ?? 0) > 0 ? '' : 'hidden' }}">
                        {{ $savedCount ?? 0 }}
                    </span>
                </a>

                <a href="{{ route('cart.index') }}" class="relative flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-200 hover:text-brand-700">
                    <x-icon name="cart" class="h-5 w-5" />
                    @if (($cartCount ?? 0) > 0)
                        <span class="absolute -right-1.5 -top-1.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-gradient-to-br from-brand-500 to-indigo-600 px-1 text-[10px] font-bold text-white shadow-md">
                            {{ $cartCount }}
                        </span>
                    @endif
                </a>

                @auth
                    <div class="relative hidden sm:block" x-data="{ open: false }" @click.outside="open = false">
                        <button type="button" @click="open = !open"
                                class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white py-1.5 pl-1.5 pr-3 text-left shadow-sm transition hover:border-brand-200">
                            <x-user-avatar :user="auth()->user()" size="h-8 w-8" />
                            <span class="hidden leading-tight md:block">
                                <span class="block max-w-28 truncate text-xs font-bold text-slate-800">{{ auth()->user()->nama }}</span>
                                <span class="block text-[10px] font-semibold uppercase tracking-wide text-brand-500">{{ auth()->user()->role }}</span>
                            </span>
                            <x-icon name="chevron-down" class="h-4 w-4 text-slate-400" />
                        </button>

                        <div x-cloak x-show="open" x-transition.origin.top.right
                             class="absolute right-0 mt-2 w-56 overflow-hidden rounded-2xl border border-slate-200 bg-white p-1.5 shadow-xl shadow-slate-900/10">
                            <button type="button" @click="open = false; $dispatch('open-profile-photo')"
                                    class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-left text-sm font-medium text-slate-600 transition hover:bg-brand-50 hover:text-brand-700">
                                <x-icon name="camera" class="h-4 w-4" /> Ubah Foto Profil
                            </button>
                            <a href="{{ auth()->user()->dashboardRoute() }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-brand-50 hover:text-brand-700">
                                <x-icon name="grid" class="h-4 w-4" /> Dashboard
                            </a>
                            @if (auth()->user()->isPelanggan())
                                <a href="{{ route('customer.orders.index') }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-brand-50 hover:text-brand-700">
                                    <x-icon name="receipt" class="h-4 w-4" /> Pesanan Saya
                                </a>
                                <a href="{{ route('customer.profile.edit') }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-brand-50 hover:text-brand-700">
                                    <x-icon name="user" class="h-4 w-4" /> Profil
                                </a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}" class="mt-1 border-t border-slate-100 pt-1">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-sm font-medium text-rose-600 transition hover:bg-rose-50">
                                    <x-icon name="logout" class="h-4 w-4" /> Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn-white btn-sm hidden sm:inline-flex">Masuk</a>
                    <a href="{{ route('register') }}" class="btn-primary btn-sm hidden md:inline-flex">Daftar</a>
                @endauth

                <button type="button" @click="mobileNav = !mobileNav"
                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 lg:hidden"
                        aria-label="Buka menu">
                    <x-icon name="menu" class="h-5 w-5" x-show="!mobileNav" />
                    <x-icon name="close" class="h-5 w-5" x-show="mobileNav" x-cloak />
                </button>
            </div>
        </div>

        {{-- Mobile navigation --}}
        <div x-cloak x-show="mobileNav" x-transition
             class="border-t border-slate-100 bg-white px-4 pb-5 pt-3 lg:hidden">
            <nav class="grid gap-1">
                <a href="{{ route('home') }}" class="rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Beranda</a>
                <a href="{{ route('catalog.index') }}" class="rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Katalog Produk</a>
                <a href="{{ route('saved.index') }}" class="rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Produk Tersimpan (<span data-saved-total>{{ $savedCount ?? 0 }}</span>)
                </a>
                <a href="{{ route('tracking.index') }}" class="rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Lacak Pesanan</a>
                <a href="{{ route('cart.index') }}" class="rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Keranjang ({{ $cartCount ?? 0 }})</a>
                @auth
                    <a href="{{ auth()->user()->dashboardRoute() }}" class="rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Dashboard</a>
                    <button type="button" @click="mobileNav = false; $dispatch('open-profile-photo')"
                            class="rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-slate-700 hover:bg-slate-50">Ubah Foto Profil</button>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-rose-600 hover:bg-rose-50">Keluar</button>
                    </form>
                @else
                    <div class="mt-2 grid grid-cols-2 gap-2">
                        <a href="{{ route('login') }}" class="btn-white w-full">Masuk</a>
                        <a href="{{ route('register') }}" class="btn-primary w-full">Daftar</a>
                    </div>
                @endauth
            </nav>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="relative mt-24 overflow-hidden bg-ink-950 text-slate-400">
        <div class="pointer-events-none absolute -left-24 top-0 h-72 w-72 rounded-full bg-brand-600/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-20 bottom-0 h-72 w-72 rounded-full bg-accent-500/10 blur-3xl"></div>

        <div class="container-page relative grid gap-10 py-14 md:grid-cols-2 lg:grid-cols-4">
            <div class="lg:col-span-1">
                <div class="flex items-center gap-2.5">
                    <img src="{{ asset('images/logo.svg') }}" alt="Logo Nekad Romo Print" class="h-11 w-11 rounded-xl">
                    <span class="leading-tight">
                        <span class="block font-display text-lg font-extrabold text-white">Nekad Romo<span class="text-accent-400">.</span>Print</span>
                        <span class="block text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-500">Kota Metro</span>
                    </span>
                </div>
                <p class="mt-4 text-sm leading-relaxed">
                    {{ $storeSetting->deskripsi ?? 'Percetakan digital terpercaya di Kota Metro untuk kebutuhan cetak dan branding usaha Anda.' }}
                </p>
                <div class="mt-5 flex items-center gap-2">
                    <a href="https://instagram.com/{{ ltrim($storeSetting->instagram ?? 'nekadromo.print', '@') }}" target="_blank" rel="noopener"
                       class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/5 text-slate-300 transition hover:bg-brand-600 hover:text-white">
                        <x-icon name="instagram" class="h-4 w-4" />
                    </a>
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $storeSetting->no_telepon ?? '6281278901234') }}" target="_blank" rel="noopener"
                       class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/5 text-slate-300 transition hover:bg-emerald-600 hover:text-white">
                        <x-icon name="whatsapp" class="h-4 w-4" />
                    </a>
                    <a href="mailto:{{ $storeSetting->email_kontak ?? 'info@nekadromo.com' }}"
                       class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/5 text-slate-300 transition hover:bg-brand-600 hover:text-white">
                        <x-icon name="mail" class="h-4 w-4" />
                    </a>
                </div>
            </div>

            <div>
                <h4 class="font-display text-sm font-bold uppercase tracking-wider text-white">Navigasi</h4>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="{{ route('home') }}" class="transition hover:text-white">Beranda</a></li>
                    <li><a href="{{ route('catalog.index') }}" class="transition hover:text-white">Katalog Produk</a></li>
                    <li><a href="{{ route('tracking.index') }}" class="transition hover:text-white">Lacak Pesanan</a></li>
                    <li><a href="{{ route('cart.index') }}" class="transition hover:text-white">Keranjang</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-display text-sm font-bold uppercase tracking-wider text-white">Layanan</h4>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="{{ route('catalog.index', ['kategori' => 1]) }}" class="transition hover:text-white">Digital Printing</a></li>
                    <li><a href="{{ route('catalog.index', ['kategori' => 2]) }}" class="transition hover:text-white">Cetak Promosi</a></li>
                    <li><a href="{{ route('catalog.index', ['kategori' => 3]) }}" class="transition hover:text-white">Merchandise Custom</a></li>
                    <li><a href="{{ route('catalog.index', ['kategori' => 4]) }}" class="transition hover:text-white">Dokumen & Kemasan</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-display text-sm font-bold uppercase tracking-wider text-white">Hubungi Kami</h4>
                <ul class="mt-4 space-y-3 text-sm">
                    <li class="flex gap-2.5">
                        <x-icon name="map-pin" class="mt-0.5 h-4 w-4 shrink-0 text-accent-400" />
                        <span>{{ $storeSetting->alamat ?? 'Jl. Jendral Sudirman No. 45, Kota Metro' }}</span>
                    </li>
                    <li class="flex gap-2.5">
                        <x-icon name="phone" class="mt-0.5 h-4 w-4 shrink-0 text-accent-400" />
                        <span>{{ $storeSetting->no_telepon ?? '0812-7890-1234' }}</span>
                    </li>
                    <li class="flex gap-2.5">
                        <x-icon name="clock" class="mt-0.5 h-4 w-4 shrink-0 text-accent-400" />
                        <span>{{ $storeSetting->jam_operasional ?? 'Senin - Sabtu, 08.00 - 20.00 WIB' }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="relative border-t border-white/5">
            <div class="container-page flex flex-col items-center justify-between gap-2 py-5 text-xs sm:flex-row">
                <p>&copy; {{ date('Y') }} {{ $storeSetting->nama_perusahaan ?? 'CV Nekad Romo Kota Metro' }}. Seluruh hak cipta dilindungi.</p>
                <p class="flex items-center gap-1.5">
                    <x-icon name="shield" class="h-3.5 w-3.5 text-emerald-400" />
                    Pembayaran aman & terverifikasi otomatis
                </p>
            </div>
        </div>
    </footer>

    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $storeSetting->no_telepon ?? '6281278901234') }}" target="_blank" rel="noopener"
       class="fixed bottom-5 right-5 z-30 flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-500 text-white shadow-xl shadow-emerald-500/30 transition hover:-translate-y-1 hover:bg-emerald-600"
       aria-label="Chat WhatsApp">
        <x-icon name="whatsapp" class="h-6 w-6" />
    </a>

    @auth
        <x-profile-photo-modal :user="auth()->user()" />
    @endauth

    @stack('scripts')
</body>
</html>
