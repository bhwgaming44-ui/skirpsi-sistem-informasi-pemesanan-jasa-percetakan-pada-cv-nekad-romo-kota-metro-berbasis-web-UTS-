@extends('layouts.app')

@section('title', 'Cetak Online Cepat & Terpercaya')
@section('description', 'Pesan cetak banner, brosur, kartu nama, undangan, kaos, mug, dan paper bag di CV Nekad Romo Kota Metro. Harga transparan, pembayaran otomatis, produksi bisa dilacak.')

@section('content')

    {{-- ================= HERO ================= --}}
    <section class="relative overflow-hidden bg-mesh">
        <div class="bg-grid pointer-events-none absolute inset-0 opacity-60"></div>
        <div class="blob -left-24 top-10 h-72 w-72 bg-brand-300/40"></div>
        <div class="blob right-0 top-40 h-80 w-80 bg-accent-400/20" style="animation-delay:-6s"></div>

        <div class="container-page relative grid items-center gap-12 py-14 lg:grid-cols-[1.05fr_1fr] lg:py-20">
            <div class="animate-slide-up">
                <span class="inline-flex items-center gap-2 rounded-full border border-brand-200/70 bg-white/80 px-3.5 py-1.5 text-[11px] font-bold text-brand-700 shadow-sm backdrop-blur">
                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-brand-500 text-white">
                        <x-icon name="printer" class="h-3 w-3" />
                    </span>
                    Percetakan digital Kota Metro sejak 2014
                </span>

                <h1 class="mt-5 font-display text-4xl font-extrabold leading-[1.08] tracking-tight text-slate-900 sm:text-5xl lg:text-[3.4rem]">
                    Ide cetak jadi nyata,<br>
                    <span class="gradient-text">tanpa antre & tanpa ribet.</span>
                </h1>

                <p class="mt-5 max-w-xl text-base leading-relaxed text-slate-500">
                    Pilih bahan, atur ukuran, dan lihat estimasi harga secara otomatis. Bayar lewat QRIS,
                    e-wallet, atau virtual account — pesanan langsung masuk antrean produksi.
                </p>

                <div class="mt-8 flex flex-wrap items-center gap-3">
                    <a href="{{ route('catalog.index') }}" class="btn-primary !px-6 !py-3.5 shadow-xl shadow-brand-500/25">
                        <x-icon name="cart" class="h-5 w-5" /> Mulai Pesan Cetak
                    </a>
                    <a href="{{ route('tracking.index') }}" class="btn-white !px-6 !py-3.5">
                        <x-icon name="search" class="h-5 w-5" /> Lacak Pesanan
                    </a>
                </div>

                <div class="mt-9 grid max-w-lg grid-cols-3 gap-3">
                    @foreach ([
                        ['value' => $stats['pelanggan'], 'label' => 'Pelanggan aktif', 'suffix' => '+'],
                        ['value' => $stats['pesanan'], 'label' => 'Pesanan lunas', 'suffix' => '+'],
                        ['value' => $stats['produk'], 'label' => 'Layanan cetak', 'suffix' => ''],
                    ] as $stat)
                        <div class="rounded-2xl border border-white/70 bg-white/70 px-3.5 py-3 shadow-sm backdrop-blur">
                            <p class="font-display text-xl font-extrabold text-slate-900">
                                <span data-counter="{{ $stat['value'] }}">{{ $stat['value'] }}</span>{{ $stat['suffix'] }}
                            </p>
                            <p class="mt-0.5 text-[11px] font-semibold text-slate-500">{{ $stat['label'] }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-7 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs font-semibold text-slate-500">
                    <span class="flex items-center gap-1.5"><x-icon name="check-circle" class="h-4 w-4 text-emerald-500" /> Harga transparan</span>
                    <span class="flex items-center gap-1.5"><x-icon name="shield" class="h-4 w-4 text-brand-500" /> Pembayaran otomatis</span>
                    <span class="flex items-center gap-1.5"><x-icon name="truck" class="h-4 w-4 text-accent-500" /> Bisa kirim kurir</span>
                </div>
            </div>

            <div class="relative mx-auto w-full max-w-lg">
                <div class="animate-float relative overflow-hidden rounded-[2rem] border border-white/80 bg-white p-2.5 shadow-[0_40px_90px_-40px_rgba(67,56,202,0.55)]">
                    <img src="{{ asset('images/products/workshop.jpg') }}" alt="Workshop percetakan CV Nekad Romo"
                         class="h-[22rem] w-full rounded-[1.6rem] object-cover sm:h-[26rem]">
                    <div class="absolute inset-x-2.5 bottom-2.5 rounded-b-[1.6rem] bg-gradient-to-t from-ink-950/85 via-ink-950/20 to-transparent p-5 pt-16">
                        <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-white/70">Workshop kami</p>
                        <p class="font-display text-lg font-bold text-white">Presisi di setiap cetakan</p>
                    </div>
                </div>

                <div class="absolute -left-3 top-10 animate-float rounded-2xl border border-white bg-white/95 px-4 py-3 shadow-xl shadow-slate-900/10 backdrop-blur sm:-left-6" style="animation-delay:-1.5s">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                            <x-icon name="check-circle" class="h-4.5 w-4.5" />
                        </span>
                        <div class="leading-tight">
                            <p class="text-[10px] font-semibold text-slate-400">Pembayaran</p>
                            <p class="text-sm font-bold text-slate-800">Terverifikasi otomatis</p>
                        </div>
                    </div>
                </div>

                <div class="absolute -right-2 bottom-24 animate-float rounded-2xl border border-white bg-white/95 px-4 py-3 shadow-xl shadow-slate-900/10 backdrop-blur sm:-right-5" style="animation-delay:-3s">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                            <x-icon name="wallet" class="h-4.5 w-4.5" />
                        </span>
                        <div class="leading-tight">
                            <p class="text-[10px] font-semibold text-slate-400">Estimasi harga</p>
                            <p class="text-sm font-bold text-slate-800">Hitung otomatis</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= VALUE / WHY US ================= --}}
    <section class="container-page -mt-6 relative z-10">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['icon' => 'clock', 'title' => 'Proses cepat', 'text' => 'Estimasi produksi jelas sejak checkout, mulai 1 hari kerja.', 'tone' => 'from-brand-500 to-indigo-500'],
                ['icon' => 'layers', 'title' => 'Bahan lengkap', 'text' => 'Flexi, art paper, vinyl, combed, kraft — semua tersedia.', 'tone' => 'from-sky-500 to-cyan-500'],
                ['icon' => 'palette', 'title' => 'Bantuan desain', 'text' => 'Belum punya desain? Tim kreatif kami siap membantu.', 'tone' => 'from-violet-500 to-fuchsia-500'],
                ['icon' => 'shield', 'title' => 'Aman & terpantau', 'text' => 'Riwayat status tersimpan rapi dan bisa dilacak kapan saja.', 'tone' => 'from-emerald-500 to-teal-500'],
            ] as $item)
                <div class="card group p-5 reveal transition hover:-translate-y-1 hover:shadow-[var(--shadow-soft)]">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br {{ $item['tone'] }} text-white shadow-lg">
                        <x-icon :name="$item['icon']" class="h-5 w-5" />
                    </span>
                    <h3 class="mt-4 text-base font-bold text-slate-900">{{ $item['title'] }}</h3>
                    <p class="mt-1.5 text-sm leading-relaxed text-slate-500">{{ $item['text'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ================= CATEGORIES ================= --}}
    <section class="container-page py-16 lg:py-20">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div class="reveal max-w-xl">
                <span class="badge bg-brand-50 text-brand-700 ring-1 ring-inset ring-brand-100">Kategori layanan</span>
                <h2 class="mt-3 font-display text-3xl font-extrabold tracking-tight text-slate-900">Semua kebutuhan cetak, satu tempat</h2>
                <p class="mt-2 text-sm leading-relaxed text-slate-500">
                    Dari spanduk outdoor sampai merchandise custom — pilih kategori yang sesuai kebutuhan Anda.
                </p>
            </div>
            <a href="{{ route('catalog.index') }}" class="btn-white reveal">
                Lihat semua produk <x-icon name="arrow-right" class="h-4 w-4" />
            </a>
        </div>

        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($categories as $category)
                <a href="{{ route('catalog.index', ['kategori' => $category->id_kategori]) }}"
                   class="card-hover group relative overflow-hidden p-5 reveal">
                    <div class="pointer-events-none absolute -right-6 -top-6 h-24 w-24 rounded-full bg-brand-100/60 blur-2xl transition group-hover:bg-brand-200/70"></div>
                    <span class="relative flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-500 to-indigo-600 text-white shadow-lg shadow-brand-500/25">
                        <x-icon :name="$category->ikon" class="h-6 w-6" />
                    </span>
                    <h3 class="relative mt-4 text-base font-bold leading-snug text-slate-900 group-hover:text-brand-700">{{ $category->nama_kategori }}</h3>
                    <p class="relative mt-1.5 line-clamp-2 text-xs leading-relaxed text-slate-500">{{ $category->deskripsi }}</p>
                    <div class="relative mt-4 flex items-center justify-between">
                        <span class="badge bg-slate-100 text-slate-600">{{ $category->produk_count }} produk</span>
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-50 text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white">
                            <x-icon name="arrow-right" class="h-4 w-4" />
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    </section>

    {{-- ================= FEATURED PRODUCTS ================= --}}
    <section class="relative overflow-hidden bg-white py-16 lg:py-20">
        <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-brand-200 to-transparent"></div>
        <div class="container-page">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div class="reveal max-w-xl">
                    <span class="badge bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-100">
                        <x-icon name="star" class="h-3.5 w-3.5" /> Paling diminati
                    </span>
                    <h2 class="mt-3 font-display text-3xl font-extrabold tracking-tight text-slate-900">Produk favorit pelanggan</h2>
                    <p class="mt-2 text-sm leading-relaxed text-slate-500">
                        Layanan cetak yang paling sering dipesan, lengkap dengan pilihan bahan dan finishing.
                    </p>
                </div>
                <a href="{{ route('catalog.index') }}" class="btn-ghost reveal">
                    Jelajahi katalog <x-icon name="arrow-right" class="h-4 w-4" />
                </a>
            </div>

            <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($featuredProducts as $product)
                    <x-product-card :product="$product" :saved="in_array($product->id_produk, $savedProductIds, true)" />
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================= HOW IT WORKS ================= --}}
    <section class="container-page py-16 lg:py-20">
        <div class="reveal mx-auto max-w-2xl text-center">
            <span class="badge bg-sky-50 text-sky-700 ring-1 ring-inset ring-sky-100">Alur pemesanan</span>
            <h2 class="mt-3 font-display text-3xl font-extrabold tracking-tight text-slate-900">Empat langkah, pesanan Anda jalan</h2>
            <p class="mt-2 text-sm leading-relaxed text-slate-500">
                Tanpa perlu datang ke toko. Semua proses bisa diselesaikan dari mana saja.
            </p>
        </div>

        <div class="relative mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <div class="pointer-events-none absolute left-[12%] right-[12%] top-8 hidden border-t-2 border-dashed border-brand-200 lg:block"></div>

            @foreach ([
                ['icon' => 'search', 'step' => '01', 'title' => 'Pilih layanan', 'text' => 'Tentukan produk cetak, bahan, ukuran, dan finishing yang diinginkan.'],
                ['icon' => 'wallet', 'step' => '02', 'title' => 'Cek harga', 'text' => 'Sistem menghitung estimasi biaya secara otomatis dan transparan.'],
                ['icon' => 'credit-card', 'step' => '03', 'title' => 'Bayar online', 'text' => 'Selesaikan pembayaran via QRIS, e-wallet, atau virtual account.'],
                ['icon' => 'truck', 'step' => '04', 'title' => 'Lacak & terima', 'text' => 'Pantau progres produksi hingga siap diambil atau dikirim.'],
            ] as $step)
                <div class="card relative p-5 text-center reveal">
                    <span class="absolute right-4 top-4 font-display text-3xl font-extrabold text-slate-100">{{ $step['step'] }}</span>
                    <span class="relative mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-500 to-accent-500 text-white shadow-lg shadow-brand-500/25">
                        <x-icon :name="$step['icon']" class="h-6 w-6" />
                    </span>
                    <h3 class="relative mt-4 text-base font-bold text-slate-900">{{ $step['title'] }}</h3>
                    <p class="relative mt-1.5 text-sm leading-relaxed text-slate-500">{{ $step['text'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ================= TESTIMONIALS ================= --}}
    <section class="relative overflow-hidden bg-ink-950 py-16 text-white lg:py-20">
        <div class="pointer-events-none absolute -left-24 top-0 h-80 w-80 rounded-full bg-brand-600/25 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-24 bottom-0 h-80 w-80 rounded-full bg-accent-500/10 blur-3xl"></div>

        <div class="container-page relative">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div class="reveal max-w-xl">
                    <span class="badge bg-white/10 text-white ring-1 ring-white/15">
                        <x-icon name="star" class="h-3.5 w-3.5 text-amber-300" /> Rating {{ $stats['rating'] }}/5
                    </span>
                    <h2 class="mt-3 font-display text-3xl font-extrabold tracking-tight">Dipercaya pelaku usaha Kota Metro</h2>
                    <p class="mt-2 text-sm leading-relaxed text-indigo-100/60">
                        Kualitas cetak, ketepatan waktu, dan pelayanan yang membuat pelanggan kembali.
                    </p>
                </div>
                <div class="flex items-center gap-1 text-amber-300">
                    @for ($i = 0; $i < 5; $i++)
                        <x-icon name="star" class="h-5 w-5 fill-current" />
                    @endfor
                </div>
            </div>

            <div class="mt-8 grid gap-4 md:grid-cols-3">
                @foreach ([
                    ['nama' => 'Rina Kartika', 'usaha' => 'Kedai Kopi Senja', 'isi' => 'Banner dan stiker vinyl-nya rapi, warnanya tajam. Prosesnya cepat dan bisa dipantau dari HP.', 'awal' => 'R'],
                    ['nama' => 'Dedi Saputra', 'usaha' => 'Komunitas Gowes Metro', 'isi' => 'Pesan kaos DTF untuk 40 anggota. Ukuran konsisten dan sablonnya tidak pecah setelah dicuci.', 'awal' => 'D'],
                    ['nama' => 'Ayu Lestari', 'usaha' => 'Butik Ayu', 'isi' => 'Paper bag dan kartu nama custom hasilnya elegan. Adminnya responsif membantu pilih bahan.', 'awal' => 'A'],
                ] as $testimonial)
                    <figure class="rounded-2xl border border-white/10 bg-white/5 p-5 backdrop-blur transition hover:border-white/20 hover:bg-white/[0.08] reveal">
                        <div class="flex items-center gap-1 text-amber-300">
                            @for ($i = 0; $i < 5; $i++)
                                <x-icon name="star" class="h-3.5 w-3.5 fill-current" />
                            @endfor
                        </div>
                        <blockquote class="mt-3 text-sm leading-relaxed text-indigo-50/85">"{{ $testimonial['isi'] }}"</blockquote>
                        <figcaption class="mt-4 flex items-center gap-2.5 border-t border-white/10 pt-4">
                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-brand-500 to-accent-500 text-xs font-bold text-white">
                                {{ $testimonial['awal'] }}
                            </span>
                            <span class="leading-tight">
                                <span class="block text-sm font-bold text-white">{{ $testimonial['nama'] }}</span>
                                <span class="block text-[11px] text-indigo-200/60">{{ $testimonial['usaha'] }}</span>
                            </span>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================= CTA ================= --}}
    <section class="container-page py-16 lg:py-20">
        <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-brand-600 via-brand-500 to-indigo-600 p-8 text-white shadow-[0_40px_80px_-40px_rgba(67,56,202,0.7)] sm:p-12">
            <div class="pointer-events-none absolute -right-10 -top-10 h-56 w-56 rounded-full bg-white/15 blur-2xl"></div>
            <div class="pointer-events-none absolute -bottom-16 left-1/3 h-56 w-56 rounded-full bg-accent-400/30 blur-3xl"></div>

            <div class="relative flex flex-col items-start justify-between gap-6 lg:flex-row lg:items-center">
                <div class="max-w-xl">
                    <h2 class="font-display text-3xl font-extrabold leading-tight">Siap cetak kebutuhan Anda hari ini?</h2>
                    <p class="mt-2 text-sm leading-relaxed text-indigo-50/85">
                        Buat pesanan sekarang, pilih desain sendiri atau gunakan jasa desain kami.
                        Butuh konsultasi? Tim kami siap membantu via WhatsApp.
                    </p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('catalog.index') }}" class="btn !bg-white !text-brand-700 shadow-xl hover:-translate-y-0.5">
                        <x-icon name="cart" class="h-4.5 w-4.5" /> Pesan Sekarang
                    </a>
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $store->no_telepon ?? '6281278901234') }}" target="_blank" rel="noopener"
                       class="btn border border-white/30 bg-white/10 !text-white backdrop-blur transition hover:bg-white/20">
                        <x-icon name="whatsapp" class="h-4.5 w-4.5" /> Tanya Admin
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Animated welcome popup (shown once per session) --}}
    <div data-welcome="home" data-welcome-title="Selamat datang di Nekad Romo Print 👋" class="hidden">
        <p class="text-indigo-100/85">Sekarang pesan cetak bisa dilakukan <b class="text-white">kapan saja, dari mana saja</b>. Berikut kemudahan yang Anda dapatkan:</p>
        <div class="mt-4 grid gap-2 text-left">
            @foreach ([
                'Harga dihitung otomatis sesuai ukuran, bahan & finishing.',
                'Pembayaran QRIS / e-wallet / virtual account terverifikasi otomatis.',
                'Status produksi dapat dilacak real-time lewat kode pesanan.',
            ] as $benefit)
                <div class="flex items-start gap-2 rounded-xl bg-white/10 px-3 py-2.5 text-xs text-indigo-50">
                    <x-icon name="check-circle" class="mt-0.5 h-4 w-4 shrink-0 text-emerald-300" />
                    <span>{{ $benefit }}</span>
                </div>
            @endforeach
        </div>
        <a href="{{ route('catalog.index') }}" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white px-4 py-3 text-sm font-bold text-brand-700 transition hover:bg-indigo-50">
            Mulai Jelajahi Katalog <x-icon name="arrow-right" class="h-4 w-4" />
        </a>
    </div>
@endsection
