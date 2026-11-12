@extends('layouts.dashboard')

@section('title', 'Antrean Produksi')

@section('content')
    @php
        $columnStyles = [
            \App\Models\Order::STATUS_ANTREAN => [
                'label' => 'Antrean Masuk',
                'icon' => 'clipboard',
                'head' => 'from-sky-500 to-cyan-500',
                'soft' => 'bg-sky-50',
                'text' => 'text-sky-700',
            ],
            \App\Models\Order::STATUS_CETAK => [
                'label' => 'Sedang Dicetak',
                'icon' => 'printer',
                'head' => 'from-violet-500 to-fuchsia-500',
                'soft' => 'bg-violet-50',
                'text' => 'text-violet-700',
            ],
            \App\Models\Order::STATUS_SELESAI_CETAK => [
                'label' => 'Selesai Cetak',
                'icon' => 'brush',
                'head' => 'from-indigo-500 to-blue-500',
                'soft' => 'bg-indigo-50',
                'text' => 'text-indigo-700',
            ],
            \App\Models\Order::STATUS_SIAP => [
                'label' => 'Siap Diambil',
                'icon' => 'truck',
                'head' => 'from-teal-500 to-emerald-500',
                'soft' => 'bg-teal-50',
                'text' => 'text-teal-700',
            ],
        ];

        $totalQueue = collect($columns)->flatten()->count();
    @endphp

    <div class="space-y-5">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="font-display text-xl font-extrabold text-slate-900">Board Antrean Produksi</h2>
                <p class="mt-0.5 text-sm text-slate-500">
                    {{ $totalQueue }} pesanan aktif di workshop. Klik panah untuk memindahkan ke tahap berikutnya.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" onclick="window.location.reload()" class="btn-white btn-sm">
                    <x-icon name="refresh" class="h-4 w-4" /> Segarkan
                </button>
                <a href="{{ route('admin.orders.index') }}" class="btn-primary btn-sm">
                    <x-icon name="receipt" class="h-4 w-4" /> Semua pesanan
                </a>
            </div>
        </div>

        @if ($totalQueue === 0)
            <x-empty-state icon="printer" title="Antrean produksi kosong"
                           message="Semua pesanan sudah selesai diproses. Pesanan baru akan muncul otomatis setelah pembayaran terverifikasi.">
                <a href="{{ route('admin.orders.index') }}" class="btn-white btn-sm mt-1">
                    <x-icon name="receipt" class="h-4 w-4" /> Lihat pesanan
                </a>
            </x-empty-state>
        @else
            <div class="grid gap-4 lg:grid-cols-2 xl:grid-cols-4">
                @foreach ($columns as $status => $orders)
                    @php $style = $columnStyles[$status]; @endphp

                    <section class="flex flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-[var(--shadow-card)]">
                        <header class="relative overflow-hidden bg-gradient-to-r {{ $style['head'] }} px-4 py-3.5 text-white">
                            <div class="pointer-events-none absolute -right-6 -top-6 h-20 w-20 rounded-full bg-white/20 blur-2xl"></div>
                            <div class="relative flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/20 backdrop-blur">
                                        <x-icon :name="$style['icon']" class="h-4 w-4" />
                                    </span>
                                    <div class="leading-tight">
                                        <p class="text-xs font-bold">{{ $style['label'] }}</p>
                                        <p class="text-[10px] text-white/70">{{ count($orders) }} pesanan</p>
                                    </div>
                                </div>
                                <span class="flex h-7 min-w-7 items-center justify-center rounded-full bg-white/20 px-1.5 text-xs font-extrabold backdrop-blur">
                                    {{ count($orders) }}
                                </span>
                            </div>
                        </header>

                        <div class="flex-1 space-y-2.5 bg-slate-50/40 p-3">
                            @forelse ($orders as $order)
                                <article class="group rounded-xl border border-slate-200 bg-white p-3 shadow-sm transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <p class="font-mono text-[11px] font-bold text-brand-600">{{ $order->kode_pesanan }}</p>
                                            <p class="mt-0.5 truncate text-xs font-bold text-slate-800">{{ $order->user->nama }}</p>
                                        </div>
                                        <span class="badge {{ $style['soft'] }} {{ $style['text'] }}">{{ $order->detail_count }} item</span>
                                    </div>

                                    <div class="mt-2 space-y-1 text-[10px] text-slate-500">
                                        <p class="flex items-center gap-1.5">
                                            <x-icon name="calendar" class="h-3 w-3 text-slate-400" />
                                            Masuk {{ $order->tgl_pesan->translatedFormat('d M Y') }}
                                        </p>
                                        <p class="flex items-center gap-1.5">
                                            <x-icon name="clock" class="h-3 w-3 text-slate-400" />
                                            Target {{ $order->estimasi_selesai?->translatedFormat('d M Y') ?? '-' }}
                                        </p>
                                        <p class="flex items-center gap-1.5">
                                            <x-icon name="truck" class="h-3 w-3 text-slate-400" />
                                            {{ $order->metode_pengambilan }}
                                        </p>
                                    </div>

                                    <div class="mt-2.5 flex items-center justify-between gap-2 border-t border-dashed border-slate-200 pt-2.5">
                                        <div>
                                            <p class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Total</p>
                                            <p class="text-xs font-extrabold text-slate-800">Rp {{ number_format((int) $order->grand_total, 0, ',', '.') }}</p>
                                        </div>

                                        <div class="flex items-center gap-1">
                                            <a href="{{ route('admin.orders.show', $order) }}"
                                               class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700" title="Detail pesanan">
                                                <x-icon name="eye" class="h-4 w-4" />
                                            </a>

                                            @if ($order->nextStatus())
                                                <form method="POST" action="{{ route('admin.orders.advance', $order) }}"
                                                      data-confirm="Pesanan {{ $order->kode_pesanan }} akan dipindahkan ke tahap berikutnya."
                                                      data-confirm-button="Ya, lanjutkan" data-confirm-icon="question"
                                                      data-confirm-title="Lanjutkan produksi?">
                                                    @csrf
                                                    <button type="submit"
                                                            class="inline-flex items-center gap-1 rounded-lg bg-gradient-to-r {{ $style['head'] }} px-2.5 py-1.5 text-[10px] font-bold text-white shadow-sm transition hover:opacity-90"
                                                            title="Tahap berikutnya">
                                                        Next <x-icon name="arrow-right" class="h-3 w-3" />
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </article>
                            @empty
                                <div class="flex flex-col items-center justify-center gap-1.5 rounded-xl border border-dashed border-slate-300 bg-white/60 px-3 py-8 text-center">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-400">
                                        <x-icon name="archive" class="h-5 w-5" />
                                    </span>
                                    <p class="text-[11px] font-semibold text-slate-400">Tidak ada pesanan</p>
                                </div>
                            @endforelse
                        </div>
                    </section>
                @endforeach
            </div>
        @endif
    </div>
@endsection
