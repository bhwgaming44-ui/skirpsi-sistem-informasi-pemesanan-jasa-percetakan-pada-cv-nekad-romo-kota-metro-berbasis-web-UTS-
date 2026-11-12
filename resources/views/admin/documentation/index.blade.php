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
                    <div class="flex flex-wrap gap-1.5">
                        <span class="badge bg-indigo-50 text-indigo-700">Pelanggan</span>
                        <span class="badge bg-emerald-50 text-emerald-700">Admin</span>
                        <span class="badge bg-amber-50 text-amber-700">Pimpinan</span>
                    </div>
                </div>

                <div class="overflow-x-auto bg-gradient-to-br from-slate-50 via-white to-indigo-50/40 p-3 sm:p-5">
                    <svg viewBox="0 0 1000 640" role="img" aria-labelledby="usecase-title" class="h-auto w-full min-w-[860px]">
                        <defs>
                            <linearGradient id="ucBoundary" x1="0" y1="0" x2="1" y2="1">
                                <stop offset="0%" stop-color="#eef2ff" />
                                <stop offset="100%" stop-color="#f8fafc" />
                            </linearGradient>
                            <filter id="ucShadow" x="-20%" y="-20%" width="140%" height="140%">
                                <feDropShadow dx="0" dy="8" stdDeviation="10" flood-color="#312e81" flood-opacity="0.10" />
                            </filter>
                            <marker id="ucArrow" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto">
                                <path d="M0,0 L8,4 L0,8 Z" fill="#818cf8" />
                            </marker>
                        </defs>

                        <rect x="250" y="40" width="500" height="560" rx="28" fill="url(#ucBoundary)" stroke="#c7d2fe" stroke-width="1.5" filter="url(#ucShadow)" />
                        <text id="usecase-title" x="500" y="74" text-anchor="middle" fill="#334155" font-size="15" font-weight="700">Sistem Pemesanan Jasa Percetakan</text>
                        <text x="500" y="94" text-anchor="middle" fill="#94a3b8" font-size="11" font-weight="600">CV Nekad Romo Kota Metro</text>

                        {{-- Pelanggan --}}
                        <g stroke="#6366f1" stroke-width="2" fill="none" stroke-linecap="round">
                            <circle cx="100" cy="235" r="18" fill="#eef2ff" />
                            <path d="M100 253v47M76 268h48M100 300l-18 40M100 300l18 40" />
                        </g>
                        <text x="100" y="364" text-anchor="middle" fill="#312e81" font-size="13" font-weight="700">Pelanggan</text>
                        <text x="100" y="380" text-anchor="middle" fill="#818cf8" font-size="10" font-weight="600">Customer</text>

                        {{-- Admin --}}
                        <g stroke="#10b981" stroke-width="2" fill="none" stroke-linecap="round">
                            <circle cx="900" cy="235" r="18" fill="#ecfdf5" />
                            <path d="M900 253v47M876 268h48M900 300l-18 40M900 300l18 40" />
                        </g>
                        <text x="900" y="364" text-anchor="middle" fill="#065f46" font-size="13" font-weight="700">Admin</text>
                        <text x="900" y="380" text-anchor="middle" fill="#34d399" font-size="10" font-weight="600">Pengelola</text>

                        {{-- Pimpinan --}}
                        <g stroke="#f59e0b" stroke-width="2" fill="none" stroke-linecap="round">
                            <circle cx="900" cy="470" r="18" fill="#fff7ed" />
                            <path d="M900 488v47M876 503h48M900 535l-18 40M900 535l18 40" />
                        </g>
                        <text x="900" y="599" text-anchor="middle" fill="#92400e" font-size="13" font-weight="700">Pimpinan</text>

                        {{-- Lines: Pelanggan --}}
                        <g stroke="#6366f1" stroke-width="1.2" stroke-opacity="0.45">
                            <line x1="122" y1="255" x2="275" y2="140" />
                            <line x1="122" y1="255" x2="275" y2="215" />
                            <line x1="122" y1="255" x2="275" y2="290" />
                            <line x1="122" y1="255" x2="275" y2="365" />
                            <line x1="122" y1="255" x2="275" y2="440" />
                            <line x1="122" y1="255" x2="275" y2="515" />
                        </g>

                        {{-- Lines: Admin --}}
                        <g stroke="#10b981" stroke-width="1.2" stroke-opacity="0.45">
                            <line x1="878" y1="255" x2="725" y2="140" />
                            <line x1="878" y1="255" x2="725" y2="215" />
                            <line x1="878" y1="255" x2="725" y2="290" />
                            <line x1="878" y1="255" x2="725" y2="365" />
                            <line x1="878" y1="255" x2="725" y2="440" />
                        </g>

                        {{-- Line: Pimpinan --}}
                        <line x1="876" y1="503" x2="725" y2="515" stroke="#f59e0b" stroke-width="1.2" stroke-opacity="0.5" />

                        {{-- Use case: Pelanggan --}}
                        <g>
                            <ellipse cx="380" cy="140" rx="105" ry="27" fill="#eef2ff" stroke="#6366f1" stroke-width="1.5" />
                            <text x="380" y="135" text-anchor="middle" fill="#312e81" font-size="12" font-weight="600">
                                <tspan x="380">Melihat &amp; mencari</tspan>
                                <tspan x="380" dy="15">produk</tspan>
                            </text>

                            <ellipse cx="380" cy="215" rx="105" ry="27" fill="#eef2ff" stroke="#6366f1" stroke-width="1.5" />
                            <text x="380" y="219" text-anchor="middle" fill="#312e81" font-size="12" font-weight="600">Menyimpan produk favorit</text>

                            <ellipse cx="380" cy="290" rx="105" ry="27" fill="#eef2ff" stroke="#6366f1" stroke-width="1.5" />
                            <text x="380" y="294" text-anchor="middle" fill="#312e81" font-size="12" font-weight="600">Mengelola keranjang</text>

                            <ellipse cx="380" cy="365" rx="105" ry="27" fill="#eef2ff" stroke="#6366f1" stroke-width="1.5" />
                            <text x="380" y="369" text-anchor="middle" fill="#312e81" font-size="12" font-weight="600">Checkout pesanan</text>

                            <ellipse cx="380" cy="440" rx="105" ry="27" fill="#eef2ff" stroke="#6366f1" stroke-width="1.5" />
                            <text x="380" y="444" text-anchor="middle" fill="#312e81" font-size="12" font-weight="600">Pembayaran online</text>

                            <ellipse cx="380" cy="515" rx="105" ry="27" fill="#eef2ff" stroke="#6366f1" stroke-width="1.5" />
                            <text x="380" y="519" text-anchor="middle" fill="#312e81" font-size="12" font-weight="600">Melacak pesanan</text>
                        </g>

                        {{-- Include relation --}}
                        <line x1="380" y1="392" x2="380" y2="412" stroke="#818cf8" stroke-width="1.4" stroke-dasharray="4 3" marker-end="url(#ucArrow)" />
                        <text x="432" y="407" fill="#6366f1" font-size="9.5" font-weight="700">«include»</text>

                        {{-- Use case: Admin --}}
                        <g>
                            <ellipse cx="620" cy="140" rx="105" ry="27" fill="#ecfdf5" stroke="#10b981" stroke-width="1.5" />
                            <text x="620" y="135" text-anchor="middle" fill="#065f46" font-size="12" font-weight="600">
                                <tspan x="620">Kelola produk</tspan>
                                <tspan x="620" dy="15">&amp; gambar</tspan>
                            </text>

                            <ellipse cx="620" cy="215" rx="105" ry="27" fill="#ecfdf5" stroke="#10b981" stroke-width="1.5" />
                            <text x="620" y="210" text-anchor="middle" fill="#065f46" font-size="12" font-weight="600">
                                <tspan x="620">Kelola kategori</tspan>
                                <tspan x="620" dy="15">&amp; finishing</tspan>
                            </text>

                            <ellipse cx="620" cy="290" rx="105" ry="27" fill="#ecfdf5" stroke="#10b981" stroke-width="1.5" />
                            <text x="620" y="294" text-anchor="middle" fill="#065f46" font-size="12" font-weight="600">Verifikasi pembayaran</text>

                            <ellipse cx="620" cy="365" rx="105" ry="27" fill="#ecfdf5" stroke="#10b981" stroke-width="1.5" />
                            <text x="620" y="369" text-anchor="middle" fill="#065f46" font-size="12" font-weight="600">Proses produksi</text>

                            <ellipse cx="620" cy="440" rx="105" ry="27" fill="#ecfdf5" stroke="#10b981" stroke-width="1.5" />
                            <text x="620" y="444" text-anchor="middle" fill="#065f46" font-size="12" font-weight="600">Kelola pesanan</text>

                            <ellipse cx="620" cy="515" rx="105" ry="27" fill="#fff7ed" stroke="#f59e0b" stroke-width="1.5" />
                            <text x="620" y="510" text-anchor="middle" fill="#92400e" font-size="12" font-weight="600">
                                <tspan x="620">Melihat laporan</tspan>
                                <tspan x="620" dy="15">penjualan</tspan>
                            </text>
                        </g>
                    </svg>
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
                    <span class="badge bg-brand-50 text-brand-700"><x-icon name="cart" class="h-3.5 w-3.5" /> Pelanggan</span>
                </div>

                <div class="overflow-x-auto bg-gradient-to-br from-slate-50 via-white to-emerald-50/40 p-3 sm:p-5">
                    <svg viewBox="0 0 1120 700" role="img" aria-labelledby="order-flow-title" class="h-auto w-full min-w-[980px]">
                        <defs>
                            <filter id="orderShadow" x="-20%" y="-20%" width="140%" height="140%">
                                <feDropShadow dx="0" dy="5" stdDeviation="6" flood-color="#1e293b" flood-opacity="0.08" />
                            </filter>
                            <marker id="orderArrow" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto">
                                <path d="M0,0 L8,4 L0,8 Z" fill="#94a3b8" />
                            </marker>
                        </defs>

                        <text id="order-flow-title" x="25" y="64" fill="#94a3b8" font-size="10" font-weight="700" letter-spacing="1.5">TAHAP 1 · PELANGGAN MEMILIH PRODUK</text>
                        <text x="25" y="264" fill="#94a3b8" font-size="10" font-weight="700" letter-spacing="1.5">TAHAP 2 · CHECKOUT &amp; PEMBAYARAN</text>
                        <text x="25" y="464" fill="#94a3b8" font-size="10" font-weight="700" letter-spacing="1.5">TAHAP 3 · PRODUKSI &amp; PENYELESAIAN</text>

                        {{-- Row 1 --}}
                        <g filter="url(#orderShadow)">
                            <rect x="25" y="80" width="170" height="68" rx="34" fill="#ecfdf5" stroke="#10b981" stroke-width="1.5" />
                            <rect x="255" y="80" width="170" height="68" rx="16" fill="#ffffff" stroke="#c7d2fe" stroke-width="1.5" />
                            <rect x="485" y="80" width="170" height="68" rx="16" fill="#ffffff" stroke="#c7d2fe" stroke-width="1.5" />
                            <rect x="715" y="80" width="170" height="68" rx="16" fill="#ffffff" stroke="#c7d2fe" stroke-width="1.5" />
                            <rect x="945" y="80" width="170" height="68" rx="16" fill="#eef2ff" stroke="#6366f1" stroke-width="1.5" />
                        </g>
                        <text x="110" y="119" text-anchor="middle" fill="#065f46" font-size="12.5" font-weight="700">Mulai</text>
                        <text x="340" y="119" text-anchor="middle" fill="#1e293b" font-size="12.5" font-weight="600">Pilih produk</text>
                        <text x="570" y="112" text-anchor="middle" fill="#1e293b" font-size="12.5" font-weight="600">
                            <tspan x="570">Atur bahan,</tspan><tspan x="570" dy="16">ukuran &amp; jumlah</tspan>
                        </text>
                        <text x="800" y="112" text-anchor="middle" fill="#1e293b" font-size="12.5" font-weight="600">
                            <tspan x="800">Tambah ke</tspan><tspan x="800" dy="16">keranjang</tspan>
                        </text>
                        <text x="1030" y="112" text-anchor="middle" fill="#312e81" font-size="12.5" font-weight="700">
                            <tspan x="1030">Checkout</tspan><tspan x="1030" dy="16">pesanan</tspan>
                        </text>

                        {{-- Row 2 --}}
                        <g filter="url(#orderShadow)">
                            <rect x="945" y="280" width="170" height="68" rx="16" fill="#ffffff" stroke="#c7d2fe" stroke-width="1.5" />
                            <rect x="715" y="280" width="170" height="68" rx="16" fill="#ffffff" stroke="#c7d2fe" stroke-width="1.5" />
                            <rect x="485" y="280" width="170" height="68" rx="16" fill="#ffffff" stroke="#c7d2fe" stroke-width="1.5" />
                            <rect x="255" y="280" width="170" height="68" rx="16" fill="#ffffff" stroke="#c7d2fe" stroke-width="1.5" />
                            <path d="M110 270 L200 314 L110 358 L20 314 Z" fill="#fff7ed" stroke="#f59e0b" stroke-width="1.5" />
                        </g>
                        <text x="1030" y="312" text-anchor="middle" fill="#1e293b" font-size="12" font-weight="600">
                            <tspan x="1030">Isi data</tspan><tspan x="1030" dy="15">pengiriman &amp; catatan</tspan>
                        </text>
                        <text x="800" y="319" text-anchor="middle" fill="#1e293b" font-size="12.5" font-weight="600">Buat pesanan</text>
                        <text x="570" y="312" text-anchor="middle" fill="#1e293b" font-size="12" font-weight="600">
                            <tspan x="570">Pilih metode</tspan><tspan x="570" dy="15">pembayaran</tspan>
                        </text>
                        <text x="340" y="312" text-anchor="middle" fill="#1e293b" font-size="12" font-weight="600">
                            <tspan x="340">Bayar via</tspan><tspan x="340" dy="15">Midtrans</tspan>
                        </text>
                        <text x="110" y="309" text-anchor="middle" fill="#92400e" font-size="11.5" font-weight="700">
                            <tspan x="110">Pembayaran</tspan><tspan x="110" dy="14">valid?</tspan>
                        </text>

                        {{-- Row 3 --}}
                        <g filter="url(#orderShadow)">
                            <rect x="25" y="480" width="170" height="68" rx="16" fill="#ecfdf5" stroke="#10b981" stroke-width="1.5" />
                            <rect x="255" y="480" width="170" height="68" rx="16" fill="#ffffff" stroke="#c7d2fe" stroke-width="1.5" />
                            <rect x="485" y="480" width="170" height="68" rx="16" fill="#ffffff" stroke="#c7d2fe" stroke-width="1.5" />
                            <rect x="715" y="480" width="170" height="68" rx="16" fill="#ffffff" stroke="#c7d2fe" stroke-width="1.5" />
                            <rect x="945" y="480" width="170" height="68" rx="34" fill="#ecfdf5" stroke="#10b981" stroke-width="1.5" />
                        </g>
                        <text x="110" y="519" text-anchor="middle" fill="#065f46" font-size="12.5" font-weight="700">Status Lunas</text>
                        <text x="340" y="519" text-anchor="middle" fill="#1e293b" font-size="12.5" font-weight="600">Produksi cetak</text>
                        <text x="570" y="519" text-anchor="middle" fill="#1e293b" font-size="12.5" font-weight="600">Selesai cetak</text>
                        <text x="800" y="512" text-anchor="middle" fill="#1e293b" font-size="12" font-weight="600">
                            <tspan x="800">Siap diambil /</tspan><tspan x="800" dy="15">dikirim</tspan>
                        </text>
                        <text x="1030" y="519" text-anchor="middle" fill="#065f46" font-size="12.5" font-weight="700">Selesai</text>

                        {{-- Arrows Row 1 --}}
                        <g stroke="#94a3b8" stroke-width="1.6" fill="none" marker-end="url(#orderArrow)">
                            <line x1="195" y1="114" x2="247" y2="114" />
                            <line x1="425" y1="114" x2="477" y2="114" />
                            <line x1="655" y1="114" x2="707" y2="114" />
                            <line x1="885" y1="114" x2="937" y2="114" />
                            <line x1="1030" y1="148" x2="1030" y2="272" />
                        </g>

                        {{-- Arrows Row 2 --}}
                        <g stroke="#94a3b8" stroke-width="1.6" fill="none" marker-end="url(#orderArrow)">
                            <line x1="945" y1="314" x2="893" y2="314" />
                            <line x1="715" y1="314" x2="663" y2="314" />
                            <line x1="485" y1="314" x2="433" y2="314" />
                            <line x1="255" y1="314" x2="208" y2="314" />
                        </g>

                        {{-- Arrows Row 3 --}}
                        <g stroke="#94a3b8" stroke-width="1.6" fill="none" marker-end="url(#orderArrow)">
                            <line x1="110" y1="358" x2="110" y2="472" />
                            <line x1="195" y1="514" x2="247" y2="514" />
                            <line x1="425" y1="514" x2="477" y2="514" />
                            <line x1="655" y1="514" x2="707" y2="514" />
                            <line x1="885" y1="514" x2="937" y2="514" />
                        </g>

                        {{-- Retry payment --}}
                        <path d="M170 345 Q 255 388 338 350" fill="none" stroke="#f59e0b" stroke-width="1.4" stroke-dasharray="5 4" marker-end="url(#orderArrow)" />
                        <text x="244" y="382" text-anchor="middle" fill="#b45309" font-size="10" font-weight="700">Tidak / ulangi</text>
                        <text x="122" y="405" fill="#059669" font-size="10.5" font-weight="700">Ya</text>
                    </svg>
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
                    <span class="badge bg-emerald-50 text-emerald-700"><x-icon name="package" class="h-3.5 w-3.5" /> Admin</span>
                </div>

                <div class="overflow-x-auto bg-gradient-to-br from-slate-50 via-white to-emerald-50/40 p-3 sm:p-5">
                    <svg viewBox="0 0 1120 700" role="img" aria-labelledby="product-flow-title" class="h-auto w-full min-w-[980px]">
                        <defs>
                            <filter id="productShadow" x="-20%" y="-20%" width="140%" height="140%">
                                <feDropShadow dx="0" dy="5" stdDeviation="6" flood-color="#1e293b" flood-opacity="0.08" />
                            </filter>
                            <marker id="productArrow" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto">
                                <path d="M0,0 L8,4 L0,8 Z" fill="#94a3b8" />
                            </marker>
                            <marker id="productArrowDanger" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto">
                                <path d="M0,0 L8,4 L0,8 Z" fill="#f59e0b" />
                            </marker>
                        </defs>

                        <text id="product-flow-title" x="25" y="64" fill="#94a3b8" font-size="10" font-weight="700" letter-spacing="1.5">TAHAP 1 · PERSIAPAN DATA PRODUK</text>
                        <text x="25" y="264" fill="#94a3b8" font-size="10" font-weight="700" letter-spacing="1.5">TAHAP 2 · VALIDASI &amp; UNGGAH GAMBAR</text>
                        <text x="25" y="464" fill="#94a3b8" font-size="10" font-weight="700" letter-spacing="1.5">TAHAP 3 · PUBLIKASI</text>

                        {{-- Row 1 --}}
                        <g filter="url(#productShadow)">
                            <rect x="25" y="80" width="170" height="68" rx="34" fill="#ecfdf5" stroke="#10b981" stroke-width="1.5" />
                            <rect x="255" y="80" width="170" height="68" rx="16" fill="#ffffff" stroke="#c7d2fe" stroke-width="1.5" />
                            <rect x="485" y="80" width="170" height="68" rx="16" fill="#ffffff" stroke="#c7d2fe" stroke-width="1.5" />
                            <rect x="715" y="80" width="170" height="68" rx="16" fill="#ffffff" stroke="#c7d2fe" stroke-width="1.5" />
                            <rect x="945" y="80" width="170" height="68" rx="16" fill="#eef2ff" stroke="#6366f1" stroke-width="1.5" />
                        </g>
                        <text x="110" y="119" text-anchor="middle" fill="#065f46" font-size="12.5" font-weight="700">Mulai</text>
                        <text x="340" y="112" text-anchor="middle" fill="#1e293b" font-size="12" font-weight="600">
                            <tspan x="340">Login sebagai</tspan><tspan x="340" dy="15">admin</tspan>
                        </text>
                        <text x="570" y="112" text-anchor="middle" fill="#1e293b" font-size="12" font-weight="600">
                            <tspan x="570">Buka menu</tspan><tspan x="570" dy="15">Master Produk</tspan>
                        </text>
                        <text x="800" y="112" text-anchor="middle" fill="#1e293b" font-size="12" font-weight="600">
                            <tspan x="800">Klik tombol</tspan><tspan x="800" dy="15">Tambah Produk</tspan>
                        </text>
                        <text x="1030" y="112" text-anchor="middle" fill="#312e81" font-size="12" font-weight="700">
                            <tspan x="1030">Isi informasi</tspan><tspan x="1030" dy="15">produk</tspan>
                        </text>

                        {{-- Row 2 --}}
                        <g filter="url(#productShadow)">
                            <rect x="945" y="280" width="170" height="68" rx="16" fill="#ffffff" stroke="#c7d2fe" stroke-width="1.5" />
                            <rect x="715" y="280" width="170" height="68" rx="16" fill="#ffffff" stroke="#c7d2fe" stroke-width="1.5" />
                            <rect x="485" y="280" width="170" height="68" rx="16" fill="#ffffff" stroke="#c7d2fe" stroke-width="1.5" />
                            <path d="M340 270 L430 314 L340 358 L250 314 Z" fill="#fff7ed" stroke="#f59e0b" stroke-width="1.5" />
                            <rect x="25" y="280" width="170" height="68" rx="16" fill="#ecfdf5" stroke="#10b981" stroke-width="1.5" />
                        </g>
                        <text x="1030" y="312" text-anchor="middle" fill="#1e293b" font-size="12" font-weight="600">
                            <tspan x="1030">Upload gambar</tspan><tspan x="1030" dy="15">utama + galeri</tspan>
                        </text>
                        <text x="800" y="312" text-anchor="middle" fill="#1e293b" font-size="12" font-weight="600">
                            <tspan x="800">Tambah bahan</tspan><tspan x="800" dy="15">&amp; harga</tspan>
                        </text>
                        <text x="570" y="319" text-anchor="middle" fill="#1e293b" font-size="12.5" font-weight="600">Pilih finishing</text>
                        <text x="340" y="309" text-anchor="middle" fill="#92400e" font-size="11.5" font-weight="700">
                            <tspan x="340">Form &amp; gambar</tspan><tspan x="340" dy="14">valid?</tspan>
                        </text>
                        <text x="110" y="309" text-anchor="middle" fill="#065f46" font-size="11.5" font-weight="700">
                            <tspan x="110">Produk disimpan</tspan><tspan x="110" dy="14">di database</tspan>
                        </text>

                        {{-- Row 3 --}}
                        <g filter="url(#productShadow)">
                            <rect x="25" y="480" width="170" height="68" rx="16" fill="#eef2ff" stroke="#6366f1" stroke-width="1.5" />
                            <rect x="255" y="480" width="170" height="68" rx="34" fill="#ecfdf5" stroke="#10b981" stroke-width="1.5" />
                        </g>
                        <text x="110" y="512" text-anchor="middle" fill="#312e81" font-size="12" font-weight="600">
                            <tspan x="110">Tampil di</tspan><tspan x="110" dy="15">katalog publik</tspan>
                        </text>
                        <text x="340" y="519" text-anchor="middle" fill="#065f46" font-size="12.5" font-weight="700">Selesai</text>

                        {{-- Arrows Row 1 --}}
                        <g stroke="#94a3b8" stroke-width="1.6" fill="none" marker-end="url(#productArrow)">
                            <line x1="195" y1="114" x2="247" y2="114" />
                            <line x1="425" y1="114" x2="477" y2="114" />
                            <line x1="655" y1="114" x2="707" y2="114" />
                            <line x1="885" y1="114" x2="937" y2="114" />
                            <line x1="1030" y1="148" x2="1030" y2="272" />
                        </g>

                        {{-- Arrows Row 2 --}}
                        <g stroke="#94a3b8" stroke-width="1.6" fill="none" marker-end="url(#productArrow)">
                            <line x1="945" y1="314" x2="893" y2="314" />
                            <line x1="715" y1="314" x2="663" y2="314" />
                            <line x1="485" y1="314" x2="438" y2="314" />
                            <line x1="250" y1="314" x2="203" y2="314" />
                        </g>

                        {{-- Arrows Row 3 --}}
                        <g stroke="#94a3b8" stroke-width="1.6" fill="none" marker-end="url(#productArrow)">
                            <line x1="110" y1="348" x2="110" y2="472" />
                            <line x1="195" y1="514" x2="247" y2="514" />
                        </g>

                        {{-- Invalid loop back to form --}}
                        <path d="M340 270 V 36 H 1030 V 76" fill="none" stroke="#f59e0b" stroke-width="1.4" stroke-dasharray="5 4" marker-end="url(#productArrowDanger)" />
                        <text x="660" y="26" text-anchor="middle" fill="#b45309" font-size="10" font-weight="700">Tidak — tampilkan pesan error &amp; kembalikan ke form</text>
                        <text x="218" y="300" fill="#059669" font-size="10.5" font-weight="700">Ya</text>
                    </svg>
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
