<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $order->kode_pesanan }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo.svg') }}">

    @vite(['resources/css/app.css'])

    <style>
        @page {
            size: A4;
            margin: 12mm;
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

        .watermark {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Sora Variable', sans-serif;
            font-size: 7rem;
            font-weight: 800;
            letter-spacing: 0.15em;
            color: rgba(16, 185, 129, 0.08);
            transform: rotate(-22deg);
            pointer-events: none;
            user-select: none;
        }
    </style>
</head>
<body class="min-h-screen bg-slate-100 py-6 print:bg-white print:py-0">
    @php
        $store = \App\Models\StoreSetting::current();
        $paid = $order->isPaid();
        $trx = $order->transaksiTerakhir;
        $satuanLabel = fn (?string $satuan) => match ($satuan) {
            'meter' => 'm²',
            'lembar' => 'lembar',
            'pcs' => 'pcs',
            'rim' => 'rim',
            default => $satuan,
        };
    @endphp

    <div class="mx-auto w-full max-w-4xl px-4 print:max-w-none print:px-0">
        {{-- Toolbar --}}
        <div class="no-print mb-4 flex flex-wrap items-center justify-between gap-2">
            <a href="{{ $backUrl }}" class="btn-white btn-sm">
                <x-icon name="arrow-left" class="h-4 w-4" /> Kembali
            </a>
            <div class="flex gap-2">
                <a href="{{ route('tracking.show', $order->kode_pesanan) }}" class="btn-white btn-sm">
                    <x-icon name="search" class="h-4 w-4" /> Lacak pesanan
                </a>
                <button type="button" onclick="window.print()" class="btn-primary btn-sm">
                    <x-icon name="print" class="h-4 w-4" /> Cetak Invoice
                </button>
            </div>
        </div>

        {{-- Invoice --}}
        <div class="relative overflow-hidden rounded-2xl bg-white p-8 shadow-lg print:rounded-none print:p-0 print:shadow-none">
            @if ($paid)
                <div class="watermark">LUNAS</div>
            @endif

            {{-- Header --}}
            <header class="relative flex flex-wrap items-start justify-between gap-6 border-b-2 border-slate-900 pb-5">
                <div class="flex items-start gap-3">
                    <img src="{{ asset('images/logo.svg') }}" alt="Logo" class="h-14 w-14 rounded-xl object-contain">
                    <div>
                        <h1 class="font-display text-xl font-extrabold uppercase tracking-tight text-slate-900">{{ $store->nama_perusahaan }}</h1>
                        <p class="mt-0.5 max-w-xs text-[11px] leading-relaxed text-slate-500">{{ $store->alamat }}</p>
                        <p class="mt-0.5 text-[11px] text-slate-500">{{ $store->no_telepon }} · {{ $store->email_kontak ?: '-' }}</p>
                    </div>
                </div>

                <div class="text-right">
                    <p class="font-display text-2xl font-extrabold uppercase tracking-tight text-slate-900">Invoice</p>
                    <p class="mt-0.5 font-mono text-sm font-bold text-brand-600">{{ $order->kode_pesanan }}</p>
                    <p class="mt-1 text-[11px] text-slate-500">{{ $order->tgl_pesan->translatedFormat('d F Y, H:i') }} WIB</p>

                    <div class="mt-2 flex justify-end">
                        @if ($paid)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-[10px] font-bold text-emerald-700 ring-1 ring-inset ring-emerald-200">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> LUNAS
                            </span>
                        @elseif ($order->isCancelled())
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-3 py-1 text-[10px] font-bold text-rose-700 ring-1 ring-inset ring-rose-200">
                                <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span> DIBATALKAN
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-[10px] font-bold text-amber-700 ring-1 ring-inset ring-amber-200">
                                <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span> {{ strtoupper($order->status_pembayaran) }}
                            </span>
                        @endif
                    </div>
                </div>
            </header>

            {{-- Parties --}}
            <section class="relative mt-5 grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                    <p class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Ditagihkan kepada</p>
                    <p class="mt-1 text-sm font-bold text-slate-900">{{ $order->user->nama }}</p>
                    <p class="text-[11px] text-slate-500">{{ $order->user->no_hp }}</p>
                    <p class="text-[11px] text-slate-500">{{ $order->user->email }}</p>
                    @if ($order->user->alamat)
                        <p class="mt-1 text-[11px] leading-relaxed text-slate-500">{{ $order->user->alamat }}</p>
                    @endif
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-4">
                    <p class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Detail pengambilan</p>
                    <p class="mt-1 text-sm font-bold text-slate-900">{{ $order->metode_pengambilan }}</p>
                    <p class="text-[11px] leading-relaxed text-slate-500">{{ $order->alamat_pengiriman ?: $store->alamat }}</p>
                    <div class="mt-2 flex flex-wrap gap-3 text-[11px]">
                        <span class="text-slate-500">Estimasi: <b class="text-slate-800">{{ $order->estimasi_selesai?->translatedFormat('d F Y') ?? '-' }}</b></span>
                    </div>
                    @if ($trx)
                        <p class="mt-1 text-[11px] text-slate-500">
                            Pembayaran: <b class="uppercase text-slate-800">{{ $trx->payment_type ?? '-' }}</b>
                            @if ($trx->va_number)
                                · VA <b class="font-mono text-slate-800">{{ $trx->va_number }}</b>
                            @endif
                        </p>
                        @if ($trx->payment_time)
                            <p class="text-[11px] text-slate-500">Waktu bayar: {{ $trx->payment_time->translatedFormat('d F Y, H:i') }} WIB</p>
                        @endif
                    @endif
                </div>
            </section>

            {{-- Items --}}
            <section class="relative mt-6">
                <table class="w-full border-collapse text-left text-[11px]">
                    <thead>
                        <tr class="bg-ink-900 text-white">
                            <th class="px-3 py-2.5 text-center font-bold">No</th>
                            <th class="px-3 py-2.5 font-bold">Deskripsi Item</th>
                            <th class="px-3 py-2.5 font-bold">Spesifikasi</th>
                            <th class="px-3 py-2.5 text-center font-bold">Qty</th>
                            <th class="px-3 py-2.5 text-right font-bold">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->detail as $detail)
                            <tr class="border-b border-slate-200 align-top">
                                <td class="px-3 py-3 text-center text-slate-500">{{ $loop->iteration }}</td>
                                <td class="px-3 py-3">
                                    <p class="font-bold text-slate-900">{{ $detail->nama_produk_snapshot }}</p>
                                    <p class="mt-0.5 text-[10px] text-slate-500">{{ $detail->nama_bahan_snapshot }}</p>
                                    <p class="text-[10px] text-slate-400">Desain: {{ $detail->tipe_desain === 'Jasa Desain Nekad Romo' ? 'Jasa desain' : 'Sendiri' }}</p>
                                </td>
                                <td class="px-3 py-3 text-[10px] leading-relaxed text-slate-500">
                                    @if ($detail->satuan_snapshot === 'meter')
                                        <p>Ukuran: {{ $detail->ukuranTeks() }}</p>
                                    @endif
                                    @if ($detail->finishing->isNotEmpty())
                                        <p>Finishing: {{ $detail->finishing->pluck('nama_finishing_snapshot')->join(', ') }}</p>
                                    @endif
                                    @if ($detail->catatan_desain)
                                        <p class="italic">Catatan: {{ \Illuminate\Support\Str::limit($detail->catatan_desain, 90) }}</p>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-center text-slate-700">
                                    {{ $detail->jumlah }} {{ $satuanLabel($detail->satuan_snapshot) }}
                                </td>
                                <td class="px-3 py-3 text-right font-bold text-slate-900">
                                    Rp {{ number_format((int) $detail->subtotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>

            {{-- Totals --}}
            <section class="relative mt-4 flex flex-col gap-4 sm:flex-row sm:justify-between">
                <div class="max-w-sm">
                    @if ($order->catatan)
                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-3">
                            <p class="text-[9px] font-bold uppercase tracking-wider text-amber-600">Catatan pesanan</p>
                            <p class="mt-0.5 text-[11px] leading-relaxed text-amber-700">{{ $order->catatan }}</p>
                        </div>
                    @endif

                    <div class="mt-3 text-[10px] leading-relaxed text-slate-500">
                        <p class="font-bold text-slate-700">Ketentuan:</p>
                        <ol class="mt-1 list-inside list-decimal space-y-0.5">
                            <li>Invoice ini sah sebagai bukti pemesanan yang telah terverifikasi sistem.</li>
                            <li>Produksi dimulai setelah pembayaran lunas dikonfirmasi.</li>
                            <li>Simpan invoice ini untuk pengambilan pesanan.</li>
                        </ol>
                    </div>
                </div>

                <div class="w-full sm:w-64">
                    <div class="space-y-1.5 border-t-2 border-slate-900 pt-3 text-[11px]">
                        <div class="flex justify-between gap-2 text-slate-500">
                            <span>Total produk</span>
                            <span class="font-semibold text-slate-800">Rp {{ number_format((int) $order->total_produk, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between gap-2 text-slate-500">
                            <span>Ongkos kirim</span>
                            <span class="font-semibold text-slate-800">{{ (float) $order->ongkir > 0 ? 'Rp '.number_format((int) $order->ongkir, 0, ',', '.') : 'Gratis' }}</span>
                        </div>
                        <div class="mt-2 flex items-end justify-between gap-2 rounded-lg bg-slate-900 px-3 py-2.5 text-white">
                            <span class="text-[10px] font-bold uppercase tracking-wider">Grand Total</span>
                            <span class="font-display text-lg font-extrabold">Rp {{ number_format((int) $order->grand_total, 0, ',', '.') }}</span>
                        </div>
                        <p class="text-right text-[9px] text-slate-400">Sudah termasuk pajak sesuai ketentuan.</p>
                    </div>
                </div>
            </section>

            {{-- Signature --}}
            <section class="relative mt-8 flex justify-between gap-8 print-break">
                <div class="text-[10px] text-slate-500">
                    <p class="font-bold text-slate-700">Terima kasih atas kepercayaan Anda.</p>
                    <p class="mt-1 max-w-xs leading-relaxed">
                        Jika ada pertanyaan mengenai invoice ini, hubungi kami di
                        <b class="text-slate-700">{{ $store->no_telepon }}</b>.
                    </p>
                </div>

                <div class="w-48 text-center">
                    <p class="text-[10px] text-slate-500">Kota Metro, {{ $order->tgl_pesan->translatedFormat('d F Y') }}</p>
                    <p class="mt-0.5 text-[10px] font-semibold text-slate-700">Hormat kami,</p>
                    <div class="h-14"></div>
                    <p class="border-t border-slate-400 pt-1 text-[11px] font-bold text-slate-900">{{ $store->nama_perusahaan }}</p>
                    <p class="text-[9px] text-slate-400">Admin / Kasir</p>
                </div>
            </section>

            <footer class="relative mt-6 border-t border-slate-200 pt-3 text-center text-[9px] text-slate-400">
                Invoice dibuat otomatis oleh Sistem Informasi Pemesanan Jasa Percetakan · {{ $store->nama_perusahaan }}
            </footer>
        </div>
    </div>

    @if (request()->boolean('auto'))
        <script>window.addEventListener('load', () => window.print());</script>
    @endif
</body>
</html>
