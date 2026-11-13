@extends('layouts.dashboard')

@section('title', 'Use Case & Flowchart')

@section('content')
    <style>
        @@media print {
            [data-print-panel] { display: block !important; break-inside: avoid; }
            .doc-hero, .doc-hero * { background: #ffffff !important; color: #0f172a !important; box-shadow: none !important; }
            .doc-hero { border: 1px solid #cbd5e1 !important; }
            .doc-card { box-shadow: none !important; break-inside: avoid; }
        }
    </style>

    <div x-data="{ tab: 'usecase' }" class="space-y-6">
        {{-- Hero --}}
        <section class="doc-hero relative overflow-hidden rounded-3xl bg-gradient-to-br from-ink-950 via-[#232154] to-brand-700 p-6 text-white shadow-[0_30px_80px_-40px_rgba(49,46,129,0.9)] sm:p-8">
            <div class="pointer-events-none absolute -right-16 -top-20 h-64 w-64 rounded-full bg-brand-500/30 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-24 left-1/3 h-64 w-64 rounded-full bg-accent-400/20 blur-3xl"></div>
            <div class="pointer-events-none absolute inset-0 opacity-[0.08]"
                 style="background-image: radial-gradient(circle at 1px 1px, #ffffff 1px, transparent 0); background-size: 22px 22px;"></div>

            <div class="relative flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-2xl">
                    <span class="badge bg-white/15 text-white backdrop-blur">
                        <x-icon name="document" class="h-3.5 w-3.5 text-accent-300" /> Dokumentasi sistem
                    </span>
                    <h2 class="mt-3 font-display text-2xl font-extrabold tracking-tight sm:text-3xl">
                        Use Case &amp; Flowchart
                    </h2>
                    <p class="mt-2 max-w-xl text-sm leading-relaxed text-indigo-100/80">
                        Gambaran interaksi aktor dengan sistem pemesanan jasa percetakan CV Nekad Romo,
                        serta alur proses pemesanan pelanggan dan penambahan produk oleh admin.
                    </p>

                    <div class="mt-5 flex flex-wrap gap-2">
                        <span class="badge bg-white/10 text-indigo-100 backdrop-blur"><x-icon name="users" class="h-3.5 w-3.5" /> 3 aktor</span>
                        <span class="badge bg-white/10 text-indigo-100 backdrop-blur"><x-icon name="clipboard" class="h-3.5 w-3.5" /> 12 use case</span>
                        <span class="badge bg-white/10 text-indigo-100 backdrop-blur"><x-icon name="chart" class="h-3.5 w-3.5" /> 2 flowchart</span>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 print:hidden">
                    <button type="button" onclick="window.print()" class="btn-white btn-sm">
                        <x-icon name="print" class="h-4 w-4" /> Cetak dokumen
                    </button>
                    <a href="{{ route('admin.dashboard') }}" class="btn-sm inline-flex items-center gap-1.5 rounded-xl bg-white/10 px-4 py-2.5 text-xs font-bold text-white ring-1 ring-inset ring-white/20 transition hover:bg-white/20">
                        <x-icon name="grid" class="h-4 w-4" /> Dashboard
                    </a>
                </div>
            </div>
        </section>

        {{-- Tab navigation --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between print:hidden">
            <div class="inline-flex w-full flex-wrap gap-1 rounded-2xl border border-slate-200 bg-white p-1 shadow-sm sm:w-auto">
                <button type="button" @click="tab = 'usecase'"
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition sm:flex-none"
                        :class="tab === 'usecase' ? 'bg-brand-600 text-white shadow-md shadow-brand-600/25' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800'">
                    <x-icon name="users" class="h-4 w-4" /> Use Case
                </button>
                <button type="button" @click="tab = 'order'"
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition sm:flex-none"
                        :class="tab === 'order' ? 'bg-brand-600 text-white shadow-md shadow-brand-600/25' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800'">
                    <x-icon name="cart" class="h-4 w-4" /> Flowchart Pemesanan
                </button>
                <button type="button" @click="tab = 'product'"
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition sm:flex-none"
                        :class="tab === 'product' ? 'bg-brand-600 text-white shadow-md shadow-brand-600/25' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800'">
                    <x-icon name="package" class="h-4 w-4" /> Flowchart Tambah Produk
                </button>
            </div>

            <p class="text-[11px] font-semibold text-slate-400">
                <x-icon name="info" class="mr-1 inline h-3.5 w-3.5" /> Diagram mengikuti aktor &amp; menu yang ada di aplikasi.
            </p>
        </div>

        {{-- ============ USE CASE ============ --}}
        <section data-print-panel x-show="tab === 'usecase'" x-cloak class="grid gap-5 xl:grid-cols-[1.7fr_1fr]">
            <div class="doc-card card overflow-hidden">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-5 py-4">
                    <div>
                        <h3 class="font-display text-base font-bold text-slate-900">Use Case Diagram</h3>
                        <p class="text-xs text-slate-400">Interaksi Pelanggan, Admin, dan Pimpinan terhadap sistem.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="badge bg-indigo-50 text-indigo-700">Pelanggan</span>
                        <span class="badge bg-emerald-50 text-emerald-700">Admin</span>
                        <span class="badge bg-amber-50 text-amber-700">Pimpinan</span>
                        <a href="{{ asset('images/dokumentasi/usecase.svg') }}" target="_blank" rel="noopener"
                           class="badge bg-slate-900 text-white transition hover:bg-slate-700 print:hidden">
                            <x-icon name="link" class="h-3.5 w-3.5" /> Buka di Chrome
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto bg-gradient-to-br from-slate-50 via-white to-indigo-50/40 p-3 sm:p-5">
                    <img src="{{ asset('images/dokumentasi/usecase.svg') }}" alt="Use Case Diagram Sistem Pemesanan Jasa Percetakan CV Nekad Romo" class="h-auto w-full min-w-[860px]" loading="lazy" decoding="async">
                </div>
            </div>

            <aside class="space-y-4">
                <div class="doc-card card p-5">
                    <h3 class="font-display text-base font-bold text-slate-900">Aktor &amp; hak akses</h3>
                    <div class="mt-3 space-y-2.5">
                        <div class="rounded-2xl border border-indigo-100 bg-indigo-50/60 p-3.5">
                            <p class="flex items-center gap-2 text-xs font-bold text-indigo-800">
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-white text-indigo-600 shadow-sm"><x-icon name="user" class="h-3.5 w-3.5" /></span>
                                Pelanggan
                            </p>
                            <p class="mt-1.5 text-[11px] leading-relaxed text-indigo-900/70">Mencari produk, menyimpan favorit, mengelola keranjang, checkout, membayar, dan melacak pesanan.</p>
                        </div>
                        <div class="rounded-2xl border border-emerald-100 bg-emerald-50/60 p-3.5">
                            <p class="flex items-center gap-2 text-xs font-bold text-emerald-800">
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-white text-emerald-600 shadow-sm"><x-icon name="shield" class="h-3.5 w-3.5" /></span>
                                Admin
                            </p>
                            <p class="mt-1.5 text-[11px] leading-relaxed text-emerald-900/70">Menambah produk beserta gambar, mengatur kategori/finishing, memverifikasi pembayaran, dan mengelola antrean produksi.</p>
                        </div>
                        <div class="rounded-2xl border border-amber-100 bg-amber-50/60 p-3.5">
                            <p class="flex items-center gap-2 text-xs font-bold text-amber-800">
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-white text-amber-600 shadow-sm"><x-icon name="chart" class="h-3.5 w-3.5" /></span>
                                Pimpinan
                            </p>
                            <p class="mt-1.5 text-[11px] leading-relaxed text-amber-900/70">Melihat laporan penjualan dan rekap performa usaha tanpa mengubah data transaksi.</p>
                        </div>
                    </div>
                </div>

                <div class="doc-card card p-5">
                    <h3 class="font-display text-base font-bold text-slate-900">Relasi «include»</h3>
                    <p class="mt-1.5 text-[11px] leading-relaxed text-slate-500">
                        Use case <strong>Checkout pesanan</strong> selalu memicu <strong>Pembayaran online</strong>.
                        Transaksi akan tersimpan sebagai pesanan dengan status menunggu pembayaran sampai
                        gateway mengonfirmasi status lunas.
                    </p>
                    <div class="mt-3 rounded-xl bg-slate-50 px-3.5 py-3 text-[11px] font-semibold text-slate-600">
                        <span class="text-brand-600">Alur kunci:</span> katalog → keranjang → checkout → Midtrans → produksi.
                    </div>
                </div>
            </aside>
        </section>

        {{-- ============ FLOWCHART PEMESANAN ============ --}}
        <section data-print-panel x-show="tab === 'order'" x-cloak class="space-y-5">
            <div class="doc-card card overflow-hidden">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-5 py-4">
                    <div>
                        <h3 class="font-display text-base font-bold text-slate-900">Flowchart Alur Pemesanan Pelanggan</h3>
                        <p class="text-xs text-slate-400">Dari memilih produk sampai pesanan selesai diproduksi.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="badge bg-brand-50 text-brand-700"><x-icon name="cart" class="h-3.5 w-3.5" /> Pelanggan</span>
                        <a href="{{ asset('images/dokumentasi/flowchart-pemesanan.svg') }}" target="_blank" rel="noopener"
                           class="badge bg-slate-900 text-white transition hover:bg-slate-700 print:hidden">
                            <x-icon name="link" class="h-3.5 w-3.5" /> Buka di Chrome
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto bg-gradient-to-br from-slate-50 via-white to-emerald-50/40 p-3 sm:p-5">
                    <img src="{{ asset('images/dokumentasi/flowchart-pemesanan.svg') }}" alt="Flowchart Alur Pemesanan Pelanggan CV Nekad Romo" class="h-auto w-full min-w-[980px]" loading="lazy" decoding="async">
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <div class="doc-card card p-4">
                    <p class="flex items-center gap-2 text-xs font-bold text-slate-800"><span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-50 text-brand-600"><x-icon name="cart" class="h-3.5 w-3.5" /></span> Keranjang otomatis</p>
                    <p class="mt-2 text-[11px] leading-relaxed text-slate-500">Item keranjang tersimpan ke database setiap kali ditambah/dihapus, sehingga bisa dilanjutkan setelah login kembali.</p>
                </div>
                <div class="doc-card card p-4">
                    <p class="flex items-center gap-2 text-xs font-bold text-slate-800"><span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"><x-icon name="wallet" class="h-3.5 w-3.5" /></span> Pembayaran Midtrans</p>
                    <p class="mt-2 text-[11px] leading-relaxed text-slate-500">Status pembayaran disinkronkan melalui webhook. Pesanan tetap menunggu sampai transaksi dinyatakan lunas.</p>
                </div>
                <div class="doc-card card p-4">
                    <p class="flex items-center gap-2 text-xs font-bold text-slate-800"><span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-50 text-amber-600"><x-icon name="printer" class="h-3.5 w-3.5" /></span> Antrean produksi</p>
                    <p class="mt-2 text-[11px] leading-relaxed text-slate-500">Setelah lunas, admin memindahkan pesanan ke antrean produksi hingga status siap diambil atau dikirim.</p>
                </div>
            </div>
        </section>

        {{-- ============ FLOWCHART TAMBAH PRODUK ============ --}}
        <section data-print-panel x-show="tab === 'product'" x-cloak class="space-y-5">
            <div class="doc-card card overflow-hidden">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-5 py-4">
                    <div>
                        <h3 class="font-display text-base font-bold text-slate-900">Flowchart Tambah Produk &amp; Gambar</h3>
                        <p class="text-xs text-slate-400">Alur admin menambahkan produk baru beserta gambar utama dan galeri foto.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="badge bg-emerald-50 text-emerald-700"><x-icon name="package" class="h-3.5 w-3.5" /> Admin</span>
                        <a href="{{ asset('images/dokumentasi/flowchart-tambah-produk.svg') }}" target="_blank" rel="noopener"
                           class="badge bg-slate-900 text-white transition hover:bg-slate-700 print:hidden">
                            <x-icon name="link" class="h-3.5 w-3.5" /> Buka di Chrome
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto bg-gradient-to-br from-slate-50 via-white to-emerald-50/40 p-3 sm:p-5">
                    <img src="{{ asset('images/dokumentasi/flowchart-tambah-produk.svg') }}" alt="Flowchart Tambah Produk dan Gambar CV Nekad Romo" class="h-auto w-full min-w-[980px]" loading="lazy" decoding="async">
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <div class="doc-card card p-4">
                    <p class="flex items-center gap-2 text-xs font-bold text-slate-800"><span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-50 text-brand-600"><x-icon name="image" class="h-3.5 w-3.5" /></span> Gambar utama</p>
                    <p class="mt-2 text-[11px] leading-relaxed text-slate-500">Satu gambar utama dipakai di kartu katalog dan halaman detail. Bisa diganti atau dihapus dari form.</p>
                </div>
                <div class="doc-card card p-4">
                    <p class="flex items-center gap-2 text-xs font-bold text-slate-800"><span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"><x-icon name="layers" class="h-3.5 w-3.5" /></span> Galeri foto</p>
                    <p class="mt-2 text-[11px] leading-relaxed text-slate-500">Maksimal 6 file per unggah. Setiap foto bisa dihapus, dan pengunjung dapat memilih thumbnail di katalog.</p>
                </div>
                <div class="doc-card card p-4">
                    <p class="flex items-center gap-2 text-xs font-bold text-slate-800"><span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-50 text-amber-600"><x-icon name="shield" class="h-3.5 w-3.5" /></span> Validasi file</p>
                    <p class="mt-2 text-[11px] leading-relaxed text-slate-500">Format JPG, PNG, atau WEBP dengan ukuran maksimal 4 MB. Nama file dibuat unik otomatis agar tidak bentrok.</p>
                </div>
            </div>
        </section>

        <p class="text-center text-[11px] text-slate-400 print:hidden">
            Dokumen ini dihasilkan otomatis dari modul yang aktif di aplikasi · CV Nekad Romo Kota Metro
        </p>
    </div>
@endsection
