<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#6d5df6">
    <meta name="robots" content="noindex">
    <title>Halaman Tidak Ditemukan &middot; {{ \App\Models\StoreSetting::current()->nama_perusahaan ?? 'Nekad Romo Print' }}</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo.svg') }}">

    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen items-center justify-center bg-[#f5f6fb] px-4 py-10 antialiased">
    <main class="w-full max-w-xl">
        <div class="relative overflow-hidden rounded-3xl border border-white/70 bg-white p-8 text-center shadow-[0_30px_80px_-40px_rgba(67,56,202,0.45)] sm:p-12">
            <div class="pointer-events-none absolute -right-16 -top-16 h-48 w-48 rounded-full bg-brand-500/10 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-20 -left-16 h-48 w-48 rounded-full bg-accent-500/10 blur-3xl"></div>

            <div class="relative">
                <img src="{{ asset('images/logo.svg') }}" alt="Logo" class="mx-auto h-14 w-14 rounded-2xl shadow-lg shadow-brand-500/25">

                <p class="mt-6 font-display text-6xl font-extrabold tracking-tight text-brand-600 sm:text-7xl">404</p>

                <h1 class="mt-2 font-display text-xl font-extrabold text-slate-900 sm:text-2xl">
                    Halaman tidak ditemukan
                </h1>
                <p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-slate-500">
                    Maaf, halaman yang Anda cari sudah dipindahkan, dihapus, atau tautannya salah.
                    Silakan kembali ke beranda atau telusuri katalog layanan cetak kami.
                </p>

                <div class="mt-7 flex flex-col justify-center gap-2 sm:flex-row">
                    <a href="{{ route('home') }}" class="btn-primary">
                        <x-icon name="home" class="h-4.5 w-4.5" /> Kembali ke Beranda
                    </a>
                    <a href="{{ route('catalog.index') }}" class="btn-white">
                        <x-icon name="package" class="h-4.5 w-4.5" /> Lihat Katalog
                    </a>
                </div>

                <p class="mt-6 text-[11px] text-slate-400">
                    Butuh bantuan?
                    @php($store = \App\Models\StoreSetting::current())
                    @if ($store?->no_telepon)
                        Hubungi kami di
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $store->no_telepon) }}" class="font-bold text-brand-600 transition hover:text-brand-700">
                            {{ $store->no_telepon }}
                        </a>.
                    @else
                        Hubungi admin toko melalui halaman kontak.
                    @endif
                </p>
            </div>
        </div>

        <p class="mt-5 text-center text-[11px] text-slate-400">
            &copy; {{ now()->year }} {{ \App\Models\StoreSetting::current()->nama_perusahaan ?? 'CV Nekad Romo' }} &middot; Kota Metro
        </p>
    </main>
</body>
</html>
