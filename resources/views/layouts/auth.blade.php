<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Masuk') · {{ $storeSetting->nama_perusahaan ?? 'CV Nekad Romo' }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo.svg') }}">

    @foreach (['success', 'error', 'warning', 'info'] as $flashKey)
        @if (session($flashKey))
            <meta name="flash-{{ $flashKey }}" content="{{ session($flashKey) }}">
        @endif
    @endforeach

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f5f6fb]">

    <div class="grid min-h-screen lg:grid-cols-[1.05fr_1fr]">

        <aside class="relative hidden overflow-hidden bg-ink-950 p-10 text-white lg:flex lg:flex-col lg:justify-between">
            <img src="{{ asset('images/products/hero.jpg') }}" alt="" class="absolute inset-0 h-full w-full object-cover opacity-25">
            <div class="absolute inset-0 bg-gradient-to-br from-ink-950 via-ink-950/85 to-brand-900/70"></div>
            <div class="absolute -left-20 top-1/4 h-72 w-72 rounded-full bg-brand-500/30 blur-3xl"></div>
            <div class="absolute -right-16 bottom-10 h-64 w-64 rounded-full bg-accent-500/20 blur-3xl"></div>

            <div class="relative">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                    <img src="{{ asset('images/logo.svg') }}" alt="Logo" class="h-12 w-12 rounded-2xl shadow-2xl shadow-brand-900/50">
                    <span class="leading-tight">
                        <span class="block font-display text-lg font-extrabold">Nekad Romo<span class="text-accent-400">.</span>Print</span>
                        <span class="block text-[10px] font-semibold uppercase tracking-[0.2em] text-indigo-300/80">Kota Metro</span>
                    </span>
                </a>
            </div>

            <div class="relative max-w-md">
                <span class="badge bg-white/10 text-white ring-1 ring-white/15">
                    <x-icon name="sparkles" class="h-3.5 w-3.5 text-amber-300" /> Percetakan digital modern
                </span>
                <h2 class="mt-5 font-display text-3xl font-extrabold leading-tight">
                    Cetak lebih cepat,<br>
                    <span class="bg-gradient-to-r from-accent-300 via-brand-300 to-fuchsia-300 bg-clip-text text-transparent">pembayaran otomatis.</span>
                </h2>
                <p class="mt-4 text-sm leading-relaxed text-indigo-100/75">
                    Kelola pesanan cetak Anda dalam satu dashboard: pilih bahan, hitung harga otomatis,
                    bayar via Midtrans, dan pantau progres produksi sampai siap diambil.
                </p>

                <div class="mt-8 grid gap-3">
                    @foreach ([
                        ['icon' => 'calculator', 'title' => 'Harga transparan', 'text' => 'Estimasi biaya dihitung otomatis sesuai ukuran & finishing.'],
                        ['icon' => 'shield', 'title' => 'Pembayaran aman', 'text' => 'QRIS, e-wallet, dan virtual account terverifikasi otomatis.'],
                        ['icon' => 'truck', 'title' => 'Lacak produksi', 'text' => 'Pantau setiap tahap dari antrean hingga siap diambil.'],
                    ] as $feature)
                        <div class="flex items-start gap-3 rounded-2xl border border-white/10 bg-white/5 p-3.5 backdrop-blur">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500/80 to-accent-500/70 text-white">
                                <x-icon :name="$feature['icon'] === 'calculator' ? 'wallet' : $feature['icon']" class="h-4.5 w-4.5" />
                            </span>
                            <div>
                                <p class="text-sm font-bold text-white">{{ $feature['title'] }}</p>
                                <p class="text-xs leading-relaxed text-indigo-100/60">{{ $feature['text'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="relative flex items-center gap-2 text-[11px] text-indigo-200/50">
                <x-icon name="map-pin" class="h-3.5 w-3.5" />
                {{ $storeSetting->alamat ?? 'Jl. Jendral Sudirman No. 45, Kota Metro, Lampung' }}
            </div>
        </aside>

        <main class="flex items-center justify-center px-5 py-10 sm:px-8">
            <div class="w-full max-w-md animate-slide-up">
                <div class="mb-8 flex items-center justify-between lg:hidden">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                        <img src="{{ asset('images/logo.svg') }}" alt="Logo" class="h-10 w-10 rounded-xl">
                        <span class="font-display text-base font-extrabold text-slate-900">Nekad Romo<span class="text-brand-600">.</span>Print</span>
                    </a>
                    <a href="{{ route('home') }}" class="text-xs font-semibold text-brand-600">Beranda</a>
                </div>

                @yield('content')

                <p class="mt-8 text-center text-[11px] text-slate-400">
                    &copy; {{ date('Y') }} {{ $storeSetting->nama_perusahaan ?? 'CV Nekad Romo Kota Metro' }}.
                    Sistem Informasi Pemesanan Jasa Percetakan.
                </p>
            </div>
        </main>
    </div>

    @stack('scripts')
</body>
</html>
