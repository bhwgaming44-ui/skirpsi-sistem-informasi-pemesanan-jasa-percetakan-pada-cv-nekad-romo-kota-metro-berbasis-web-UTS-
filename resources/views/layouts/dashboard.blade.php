<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#15152b">
    <title>@yield('title', 'Dashboard') · {{ $storeSetting->nama_perusahaan ?? 'CV Nekad Romo' }}</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo.svg') }}">

    @foreach (['success', 'error', 'warning', 'info'] as $flashKey)
        @if (session($flashKey))
            <meta name="flash-{{ $flashKey }}" content="{{ session($flashKey) }}">
        @endif
    @endforeach

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f5f6fb] print:bg-white" x-data="{ sidebarOpen: false }">

    @php
        $role = auth()->user()->role;

        $groups = match ($role) {
            'admin' => [
                'Menu Utama' => [
                    ['label' => 'Dashboard', 'icon' => 'grid', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard'],
                    ['label' => 'Pesanan', 'icon' => 'receipt', 'route' => 'admin.orders.index', 'active' => 'admin.orders.*'],
                    ['label' => 'Antrean Produksi', 'icon' => 'printer', 'route' => 'admin.production.board', 'active' => 'admin.production.*', 'badge' => $sidebarQueueCount ?? 0],
                ],
                'Master Data' => [
                    ['label' => 'Produk', 'icon' => 'package', 'route' => 'admin.products.index', 'active' => 'admin.products.*'],
                    ['label' => 'Kategori', 'icon' => 'layers', 'route' => 'admin.categories.index', 'active' => 'admin.categories.*'],
                    ['label' => 'Finishing', 'icon' => 'scissors', 'route' => 'admin.finishings.index', 'active' => 'admin.finishings.*'],
                    ['label' => 'Pelanggan', 'icon' => 'users', 'route' => 'admin.customers.index', 'active' => 'admin.customers.*'],
                ],
                'Analisis & Sistem' => [
                    ['label' => 'Use Case & Flowchart', 'icon' => 'document', 'route' => 'admin.documentation', 'active' => 'admin.documentation'],
                    ['label' => 'Laporan Penjualan', 'icon' => 'chart', 'route' => 'reports.index', 'active' => 'reports.*'],
                    ['label' => 'Pembayaran', 'icon' => 'credit-card', 'route' => 'admin.payments.index', 'active' => 'admin.payments.*'],
                    ['label' => 'Pengaturan', 'icon' => 'settings', 'route' => 'admin.settings.index', 'active' => 'admin.settings.*'],
                ],
            ],
            'pimpinan' => [
                'Menu Utama' => [
                    ['label' => 'Dashboard Pimpinan', 'icon' => 'grid', 'route' => 'pimpinan.dashboard', 'active' => 'pimpinan.dashboard'],
                    ['label' => 'Laporan Penjualan', 'icon' => 'chart', 'route' => 'reports.index', 'active' => 'reports.*'],
                ],
            ],
            default => [
                'Menu Utama' => [
                    ['label' => 'Dashboard', 'icon' => 'grid', 'route' => 'customer.dashboard', 'active' => 'customer.dashboard'],
                    ['label' => 'Pesanan Saya', 'icon' => 'receipt', 'route' => 'customer.orders.index', 'active' => 'customer.orders.*'],
                    ['label' => 'Profil Saya', 'icon' => 'user', 'route' => 'customer.profile.edit', 'active' => 'customer.profile.*'],
                ],
                'Layanan' => [
                    ['label' => 'Katalog Cetak', 'icon' => 'tag', 'route' => 'catalog.index', 'active' => 'catalog.*'],
                    ['label' => 'Produk Tersimpan', 'icon' => 'bookmark', 'route' => 'saved.index', 'active' => 'saved.*', 'badge' => $savedCount ?? 0],
                    ['label' => 'Lacak Pesanan', 'icon' => 'search', 'route' => 'tracking.index', 'active' => 'tracking.*'],
                    ['label' => 'Keranjang', 'icon' => 'cart', 'route' => 'cart.index', 'active' => 'cart.*'],
                ],
            ],
        };

        $roleLabel = match ($role) {
            'admin' => 'Administrator',
            'pimpinan' => 'Pimpinan',
            default => 'Pelanggan',
        };
    @endphp

    {{-- Mobile overlay --}}
    <div x-cloak x-show="sidebarOpen" @click="sidebarOpen = false"
         class="fixed inset-0 z-40 bg-ink-950/50 backdrop-blur-sm lg:hidden print:hidden"></div>

    <aside class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col overflow-hidden bg-gradient-to-b from-ink-950 via-ink-900 to-[#1b1a45] text-white transition-transform duration-300 lg:translate-x-0 print:hidden"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">

        <div class="pointer-events-none absolute -left-16 top-10 h-48 w-48 rounded-full bg-brand-600/25 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-16 bottom-10 h-48 w-48 rounded-full bg-accent-500/10 blur-3xl"></div>

        <div class="relative flex h-20 shrink-0 items-center justify-between gap-3 border-b border-white/5 px-5">
            <a href="{{ auth()->user()->dashboardRoute() }}" class="flex items-center gap-2.5">
                <img src="{{ asset('images/logo.svg') }}" alt="Logo" class="h-10 w-10 rounded-xl">
                <span class="leading-tight">
                    <span class="block font-display text-base font-extrabold tracking-tight">Nekad Romo<span class="text-accent-400">.</span>Print</span>
                    <span class="block text-[10px] font-semibold uppercase tracking-[0.16em] text-indigo-300/70">{{ $roleLabel }} Panel</span>
                </span>
            </a>
            <button type="button" @click="sidebarOpen = false" class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/5 text-white/70 lg:hidden">
                <x-icon name="close" class="h-5 w-5" />
            </button>
        </div>

        <nav class="relative flex-1 space-y-6 overflow-y-auto px-4 py-6">
            @foreach ($groups as $groupLabel => $items)
                <div>
                    <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-[0.18em] text-indigo-300/45">{{ $groupLabel }}</p>
                    <div class="space-y-1">
                        @foreach ($items as $item)
                            @php $isActive = request()->routeIs($item['active']); @endphp
                            <a href="{{ route($item['route']) }}"
                               class="sidebar-link {{ $isActive ? 'sidebar-link-active' : '' }}">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/5 text-indigo-200 transition group-hover:bg-white/10 group-hover:text-white {{ $isActive ? '!bg-white/15 text-white' : '' }}">
                                    <x-icon :name="$item['icon']" class="h-4 w-4" />
                                </span>
                                <span class="flex-1 truncate">{{ $item['label'] }}</span>
                                @if (! empty($item['badge']))
                                    <span @if ($item['route'] === 'saved.index') data-saved-badge @endif
                                          class="flex h-5 min-w-5 items-center justify-center rounded-full bg-gradient-to-br from-amber-400 to-orange-500 px-1.5 text-[10px] font-bold text-amber-950">
                                        {{ $item['badge'] }}
                                    </span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </nav>

        <div class="relative shrink-0 border-t border-white/5 p-4">
            <div class="rounded-2xl bg-white/5 p-3 backdrop-blur">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-accent-500 text-sm font-bold text-white">
                        {{ strtoupper(substr(auth()->user()->nama, 0, 1)) }}
                    </span>
                    <div class="min-w-0 flex-1 leading-tight">
                        <p class="truncate text-xs font-bold text-white">{{ auth()->user()->nama }}</p>
                        <p class="truncate text-[10px] font-medium text-indigo-200/60">{{ auth()->user()->email }}</p>
                    </div>
                </div>

                <div class="mt-3 grid grid-cols-2 gap-2">
                    <a href="{{ route('home') }}" class="flex items-center justify-center gap-1 rounded-lg bg-white/5 px-2 py-1.5 text-[10px] font-semibold text-indigo-100/80 transition hover:bg-white/10 hover:text-white">
                        <x-icon name="home" class="h-3.5 w-3.5" /> Situs
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center justify-center gap-1 rounded-lg bg-rose-500/15 px-2 py-1.5 text-[10px] font-semibold text-rose-300 transition hover:bg-rose-500/25 hover:text-rose-100">
                            <x-icon name="logout" class="h-3.5 w-3.5" /> Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    <div class="flex min-h-screen flex-col lg:pl-72 print:pl-0">
        <header class="sticky top-0 z-30 border-b border-slate-200/80 bg-white/85 backdrop-blur-xl print:hidden">
            <div class="flex h-16 items-center justify-between gap-3 px-4 sm:px-6 lg:px-8">
                <div class="flex min-w-0 items-center gap-3">
                    <button type="button" @click="sidebarOpen = true"
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 lg:hidden">
                        <x-icon name="menu" class="h-5 w-5" />
                    </button>
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-brand-500">{{ $roleLabel }} Area</p>
                        <h1 class="truncate font-display text-lg font-bold leading-tight text-slate-900">@yield('title', 'Dashboard')</h1>
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:gap-3">
                    <div x-data="clock" x-init="init()" class="hidden items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-1.5 md:flex">
                        <span class="relative flex h-2 w-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                        </span>
                        <div class="leading-tight">
                            <p class="font-mono text-xs font-bold text-slate-800" x-text="time">--:--:--</p>
                            <p class="text-[9px] font-medium text-slate-400" x-text="date">Memuat...</p>
                        </div>
                    </div>

                    @if ($role === 'admin')
                        <a href="{{ route('admin.orders.index', ['status' => 'Pesanan Masuk (Antrean)']) }}"
                           data-admin-notify="{{ route('admin.notifications') }}"
                           data-count="{{ $newOrderCount ?? 0 }}"
                           data-production-url="{{ route('admin.production.board') }}"
                           class="relative flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:border-brand-200 hover:text-brand-700"
                           title="Notifikasi pesanan baru">
                            <x-icon name="bell" class="h-5 w-5" />
                            @if (($newOrderCount ?? 0) > 0)
                                <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white shadow">
                                    {{ $newOrderCount }}
                                </span>
                            @endif
                        </a>
                    @endif

                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                        <button type="button" @click="open = !open"
                                class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white py-1.5 pl-1.5 pr-2.5 text-left transition hover:border-brand-200">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-brand-500 to-accent-500 text-xs font-bold text-white">
                                {{ strtoupper(substr(auth()->user()->nama, 0, 1)) }}
                            </span>
                            <span class="hidden leading-tight sm:block">
                                <span class="block max-w-32 truncate text-xs font-bold text-slate-800">{{ auth()->user()->nama }}</span>
                                <span class="block text-[10px] font-semibold text-slate-400">{{ $roleLabel }}</span>
                            </span>
                            <x-icon name="chevron-down" class="h-4 w-4 text-slate-400" />
                        </button>

                        <div x-cloak x-show="open" x-transition.origin.top.right
                             class="absolute right-0 mt-2 w-52 overflow-hidden rounded-2xl border border-slate-200 bg-white p-1.5 shadow-xl shadow-slate-900/10">
                            <a href="{{ route('home') }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-brand-50 hover:text-brand-700">
                                <x-icon name="home" class="h-4 w-4" /> Halaman Situs
                            </a>
                            @if ($role === 'pelanggan')
                                <a href="{{ route('customer.profile.edit') }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-brand-50 hover:text-brand-700">
                                    <x-icon name="user" class="h-4 w-4" /> Profil Saya
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
                </div>
            </div>
        </header>

        <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
            @yield('content')
        </main>

        <footer class="mt-auto border-t border-slate-200/80 px-4 py-4 text-center text-[11px] text-slate-400 sm:px-6 lg:px-8">
            &copy; {{ date('Y') }} {{ $storeSetting->nama_perusahaan ?? 'CV Nekad Romo Kota Metro' }} · Sistem Informasi Pemesanan Jasa Percetakan
        </footer>
    </div>

    @stack('scripts')
</body>
</html>
