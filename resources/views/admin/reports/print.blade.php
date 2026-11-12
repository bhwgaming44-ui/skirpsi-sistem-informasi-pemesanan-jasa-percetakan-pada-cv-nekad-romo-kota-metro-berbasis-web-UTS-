<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Penjualan {{ $from->format('d-m-Y') }} s/d {{ $to->format('d-m-Y') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo.svg') }}">

    @vite(['resources/css/app.css'])

    <style>
        @page {
            size: A4;
            margin: 14mm 12mm;
        }

        @media print {
            body {
                background: #fff !important;
            }

            .no-print {
                display: none !important;
            }

            .print-break {
                page-break-inside: avoid;
            }

            thead {
                display: table-header-group;
            }

            tr {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body class="min-h-screen bg-slate-100 py-6 print:bg-white print:py-0">
    @php
        $store = \App\Models\StoreSetting::current();
    @endphp

    <div class="mx-auto w-full max-w-4xl px-4 print:max-w-none print:px-0">
        {{-- Toolbar --}}
        <div class="no-print mb-4 flex flex-wrap items-center justify-between gap-2">
            <a href="{{ url()->previous() }}" class="btn-white btn-sm">
                <x-icon name="arrow-left" class="h-4 w-4" /> Kembali
            </a>
            <div class="flex gap-2">
                <a href="{{ route('reports.export', request()->only(['dari', 'sampai'])) }}" class="btn-white btn-sm">
                    <x-icon name="download" class="h-4 w-4" /> Export CSV
                </a>
                <button type="button" onclick="window.print()" class="btn-primary btn-sm">
                    <x-icon name="print" class="h-4 w-4" /> Cetak Sekarang
                </button>
            </div>
        </div>

        <div class="rounded-2xl bg-white p-8 shadow-lg print:rounded-none print:p-0 print:shadow-none">
            {{-- Letterhead --}}
            <header class="flex items-start justify-between gap-6 border-b-2 border-slate-900 pb-5">
                <div class="flex items-start gap-3">
                    <img src="{{ asset('images/logo.svg') }}" alt="Logo" class="h-14 w-14 rounded-xl object-contain">
                    <div>
                        <h1 class="font-display text-xl font-extrabold uppercase tracking-tight text-slate-900">{{ $store->nama_perusahaan }}</h1>
                        <p class="mt-0.5 max-w-sm text-[11px] leading-relaxed text-slate-500">{{ $store->alamat }}</p>
                        <p class="mt-0.5 text-[11px] text-slate-500">
                            {{ $store->no_telepon }} &nbsp;·&nbsp; {{ $store->email_kontak ?: '-' }}
                        </p>
                    </div>
                </div>

                <div class="text-right">
                    <p class="font-display text-sm font-extrabold uppercase tracking-wider text-slate-900">Laporan Penjualan</p>
                    <p class="mt-1 text-[11px] text-slate-500">Periode</p>
                    <p class="text-xs font-bold text-slate-800">{{ $from->translatedFormat('d F Y') }} — {{ $to->translatedFormat('d F Y') }}</p>
                    <p class="mt-1 text-[10px] text-slate-400">Dicetak {{ now()->translatedFormat('d F Y, H:i') }} WIB</p>
                </div>
            </header>

            {{-- Summary --}}
            <section class="mt-6 print-break">
                <h2 class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Ringkasan</h2>
                <div class="mt-2 grid grid-cols-4 gap-2">
                    @foreach ([
                        ['label' => 'Pendapatan', 'value' => 'Rp '.number_format($metrics['revenue'], 0, ',', '.')],
                        ['label' => 'Transaksi', 'value' => number_format($metrics['orders'], 0, ',', '.').' pesanan'],
                        ['label' => 'Pelanggan', 'value' => number_format($metrics['customers'], 0, ',', '.').' orang'],
                        ['label' => 'Rata-rata', 'value' => 'Rp '.number_format($metrics['average'], 0, ',', '.')],
                    ] as $item)
                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5">
                            <p class="text-[9px] font-bold uppercase tracking-wider text-slate-400">{{ $item['label'] }}</p>
                            <p class="mt-0.5 text-sm font-extrabold text-slate-900">{{ $item['value'] }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Top products --}}
            @if ($topProducts->isNotEmpty())
                <section class="mt-6 print-break">
                    <h2 class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Produk Terlaris</h2>
                    <table class="mt-2 w-full border-collapse text-left text-[11px]">
                        <thead>
                            <tr class="bg-slate-100">
                                <th class="border border-slate-300 px-2.5 py-2 text-center font-bold">#</th>
                                <th class="border border-slate-300 px-2.5 py-2 font-bold">Produk</th>
                                <th class="border border-slate-300 px-2.5 py-2 text-center font-bold">Qty</th>
                                <th class="border border-slate-300 px-2.5 py-2 text-right font-bold">Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($topProducts as $index => $product)
                                <tr>
                                    <td class="border border-slate-300 px-2.5 py-1.5 text-center">{{ $index + 1 }}</td>
                                    <td class="border border-slate-300 px-2.5 py-1.5">{{ $product->nama_produk_snapshot }}</td>
                                    <td class="border border-slate-300 px-2.5 py-1.5 text-center">{{ (int) $product->qty }}</td>
                                    <td class="border border-slate-300 px-2.5 py-1.5 text-right">Rp {{ number_format((int) $product->pendapatan, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </section>
            @endif

            {{-- Orders --}}
            <section class="mt-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Rincian Transaksi Lunas</h2>
                    <span class="text-[10px] text-slate-400">{{ $orders->count() }} transaksi</span>
                </div>

                <table class="mt-2 w-full border-collapse text-left text-[10px]">
                    <thead>
                        <tr class="bg-slate-100">
                            <th class="border border-slate-300 px-2 py-1.5 font-bold">No</th>
                            <th class="border border-slate-300 px-2 py-1.5 font-bold">Kode</th>
                            <th class="border border-slate-300 px-2 py-1.5 font-bold">Tanggal</th>
                            <th class="border border-slate-300 px-2 py-1.5 font-bold">Pelanggan</th>
                            <th class="border border-slate-300 px-2 py-1.5 font-bold">Metode</th>
                            <th class="border border-slate-300 px-2 py-1.5 text-right font-bold">Produk</th>
                            <th class="border border-slate-300 px-2 py-1.5 text-right font-bold">Ongkir</th>
                            <th class="border border-slate-300 px-2 py-1.5 text-right font-bold">Grand Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr>
                                <td class="border border-slate-300 px-2 py-1.5 text-center">{{ $loop->iteration }}</td>
                                <td class="border border-slate-300 px-2 py-1.5 font-mono text-[9px] font-bold">{{ $order->kode_pesanan }}</td>
                                <td class="border border-slate-300 px-2 py-1.5">{{ $order->tgl_pesan->format('d/m/Y') }}</td>
                                <td class="border border-slate-300 px-2 py-1.5">{{ $order->user->nama }}</td>
                                <td class="border border-slate-300 px-2 py-1.5">{{ $order->metode_pengambilan === 'Ambil di Toko' ? 'Toko' : 'Kurir' }}</td>
                                <td class="border border-slate-300 px-2 py-1.5 text-right">{{ number_format((int) $order->total_produk, 0, ',', '.') }}</td>
                                <td class="border border-slate-300 px-2 py-1.5 text-right">{{ number_format((int) $order->ongkir, 0, ',', '.') }}</td>
                                <td class="border border-slate-300 px-2 py-1.5 text-right font-bold">{{ number_format((int) $order->grand_total, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="border border-slate-300 px-2 py-6 text-center text-slate-400">
                                    Tidak ada transaksi lunas pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-50">
                            <td colspan="7" class="border border-slate-300 px-2 py-2 text-right font-bold uppercase tracking-wider">Total</td>
                            <td class="border border-slate-300 px-2 py-2 text-right font-extrabold">
                                Rp {{ number_format((int) $orders->sum(fn ($order) => (int) $order->grand_total), 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
                @if ($orders->count() >= 500)
                    <p class="mt-1 text-[9px] italic text-slate-400">* Ditampilkan maksimal 500 transaksi pertama. Gunakan export CSV untuk data lengkap.</p>
                @endif
            </section>

            {{-- Signature --}}
            <section class="mt-10 flex justify-end print-break">
                <div class="w-56 text-center">
                    <p class="text-[11px] text-slate-500">Kota Metro, {{ now()->translatedFormat('d F Y') }}</p>
                    <p class="mt-0.5 text-[11px] font-semibold text-slate-700">Pimpinan {{ $store->nama_perusahaan }}</p>
                    <div class="h-16"></div>
                    <p class="border-t border-slate-400 pt-1 text-xs font-bold text-slate-900">&nbsp;</p>
                    <p class="text-[10px] text-slate-400">Nama &amp; tanda tangan</p>
                </div>
            </section>

            <footer class="mt-8 border-t border-slate-200 pt-3 text-center text-[9px] text-slate-400">
                Laporan ini dihasilkan otomatis oleh Sistem Informasi Pemesanan Jasa Percetakan {{ $store->nama_perusahaan }}.
            </footer>
        </div>
    </div>
</body>
</html>
