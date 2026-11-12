@extends('layouts.dashboard')

@section('title', 'Pembayaran Midtrans')

@section('content')
    @php
        $channelGroups = collect($channels)->groupBy('group');
        $activeMode = $active?->modeLabel() ?? 'Belum aktif';
    @endphp

    <div class="space-y-5">
        {{-- Header --}}
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="font-display text-xl font-extrabold text-slate-900">Pembayaran Midtrans</h2>
                <p class="mt-0.5 text-sm text-slate-500">
                    Kelola kunci sandbox &amp; production, kanal pembayaran, dan status koneksi payment gateway.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="badge {{ $active?->isConfigured() ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                    <x-icon name="{{ $active?->isConfigured() ? 'check-circle' : 'alert' }}" class="h-3.5 w-3.5" />
                    Gateway {{ $active?->isConfigured() ? 'siap' : 'belum siap' }}
                </span>
                <span class="badge bg-slate-100 text-slate-600">
                    <x-icon name="credit-card" class="h-3.5 w-3.5" /> Mode {{ $activeMode }}
                </span>
            </div>
        </div>

        {{-- Statistik --}}
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="card flex items-center gap-3 p-4">
                <span class="stat-icon"><x-icon name="check-circle" class="h-5 w-5" /></span>
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Transaksi lunas</p>
                    <p class="font-display text-xl font-extrabold text-slate-900">{{ number_format($stats['gateway_paid'], 0, ',', '.') }}</p>
                </div>
            </div>
            <div class="card flex items-center gap-3 p-4">
                <span class="stat-icon"><x-icon name="clock" class="h-5 w-5" /></span>
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Menunggu pembayaran</p>
                    <p class="font-display text-xl font-extrabold text-slate-900">{{ number_format($stats['gateway_pending'], 0, ',', '.') }}</p>
                </div>
            </div>
            <div class="card flex items-center gap-3 p-4">
                <span class="stat-icon"><x-icon name="wallet" class="h-5 w-5" /></span>
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Pendapatan via gateway</p>
                    <p class="font-display text-xl font-extrabold text-slate-900">Rp {{ number_format($stats['gateway_revenue'], 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        @if ($active?->is_production)
            <div class="flex items-start gap-2.5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3">
                <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0 text-rose-600" />
                <p class="text-[11px] leading-relaxed text-rose-700">
                    Mode <b>production</b> sedang aktif. Setiap pembayaran pelanggan akan memakai uang sungguhan.
                    Pastikan URL webhook production sudah terdaftar di dashboard Midtrans.
                </p>
            </div>
        @elseif ($active !== null && ! $active->isConfigured())
            <div class="flex items-start gap-2.5 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3">
                <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0 text-amber-600" />
                <p class="text-[11px] leading-relaxed text-amber-700">
                    Mode aktif belum memiliki kunci yang cocok. Pembayaran pelanggan otomatis memakai mode simulasi
                    sampai kunci diperbaiki.
                </p>
            </div>
        @endif

        {{-- Kartu mode --}}
        <div class="grid gap-5 xl:grid-cols-2">
            @foreach ($settings as $setting)
                @php
                    $configured = $setting->isConfigured();
                    $isActive = $setting->is_active;
                    $selectedChannels = $setting->paymentChannels();
                @endphp

                <div class="card overflow-hidden {{ $isActive ? 'ring-2 ring-brand-400/70' : '' }}">
                    <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-100 p-5">
                        <div class="flex items-start gap-3">
                            <span class="flex h-11 w-11 items-center justify-center rounded-2xl {{ $setting->is_production ? 'bg-rose-50 text-rose-600' : 'bg-sky-50 text-sky-600' }}">
                                <x-icon name="{{ $setting->is_production ? 'shield' : 'credit-card' }}" class="h-5 w-5" />
                            </span>
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-display text-base font-bold text-slate-900">Mode {{ $setting->modeLabel() }}</h3>
                                    @if ($isActive)
                                        <span class="badge bg-emerald-50 text-emerald-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Aktif
                                        </span>
                                    @endif
                                </div>
                                <p class="mt-0.5 text-[11px] text-slate-400">{{ $setting->displayName() }} · {{ $configured ? 'Kunci valid' : 'Kunci belum valid' }}</p>
                            </div>
                        </div>

                        <div class="text-right">
                            <span class="badge {{ $setting->isConnectionVerified() ? 'bg-emerald-50 text-emerald-700' : ($setting->last_test_status === 'failed' ? 'bg-rose-50 text-rose-700' : 'bg-slate-100 text-slate-500') }}">
                                {{ $setting->isConnectionVerified() ? 'Terkoneksi' : ($setting->last_test_status === 'failed' ? 'Gagal tes' : 'Belum dites') }}
                            </span>
                            @if ($setting->last_tested_at)
                                <p class="mt-1 text-[10px] text-slate-400">{{ $setting->last_tested_at->diffForHumans() }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-3 p-5">
                        <div class="grid gap-2 text-[11px] sm:grid-cols-2">
                            <div class="rounded-xl bg-slate-50 px-3 py-2">
                                <p class="font-bold uppercase tracking-wider text-slate-400">Server Key</p>
                                <p class="mt-0.5 truncate font-mono text-slate-700">{{ $setting->maskedServerKey() }}</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 px-3 py-2">
                                <p class="font-bold uppercase tracking-wider text-slate-400">Client Key</p>
                                <p class="mt-0.5 truncate font-mono text-slate-700">{{ $setting->maskedClientKey() }}</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 px-3 py-2">
                                <p class="font-bold uppercase tracking-wider text-slate-400">Signature Secret</p>
                                <p class="mt-0.5 truncate font-mono text-slate-700">{{ $setting->maskedSignatureKey() }}</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 px-3 py-2">
                                <p class="font-bold uppercase tracking-wider text-slate-400">Metode aktif</p>
                                <p class="mt-0.5 font-semibold text-slate-700">{{ count($selectedChannels) }} kanal · {{ $setting->expiry_hours }} jam</p>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            @if (! $isActive)
                                <form method="POST" action="{{ route('admin.payments.activate', $setting) }}">
                                    @csrf
                                    <button type="submit" class="btn-primary btn-sm" @disabled(! $configured)>
                                        <x-icon name="check-circle" class="h-4 w-4" /> Aktifkan mode {{ $setting->modeLabel() }}
                                    </button>
                                </form>
                            @endif

                            <form method="POST" action="{{ route('admin.payments.test', $setting) }}">
                                @csrf
                                <button type="submit" class="btn-white btn-sm" @disabled(! $configured)>
                                    <x-icon name="refresh" class="h-4 w-4" /> Tes koneksi
                                </button>
                            </form>

                            <a href="{{ $setting->webhookUrl() }}" target="_blank" class="btn-ghost btn-sm">
                                <x-icon name="document" class="h-4 w-4" /> Webhook
                            </a>
                        </div>

                        <details class="group rounded-2xl border border-slate-200 bg-slate-50/60">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-2 px-4 py-3 text-xs font-bold text-slate-700">
                                <span class="flex items-center gap-2"><x-icon name="settings" class="h-4 w-4 text-brand-500" /> Ubah kunci &amp; kanal pembayaran</span>
                                <x-icon name="chevron-down" class="h-4 w-4 text-slate-400 transition group-open:rotate-180" />
                            </summary>

                            <form method="POST" action="{{ route('admin.payments.update', $setting) }}" class="space-y-4 border-t border-slate-200 p-4">
                                @csrf
                                @method('PUT')

                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div class="sm:col-span-2">
                                        <label for="merchant_name_{{ $setting->id_konfigurasi }}" class="label">Nama Merchant / QRIS</label>
                                        <input id="merchant_name_{{ $setting->id_konfigurasi }}" name="merchant_name" type="text" required maxlength="100"
                                               value="{{ old('merchant_name', $setting->merchant_name ?: 'Nekad Romo') }}" class="input">
                                        <p class="mt-1 text-[10px] text-slate-400">Ditampilkan pada item Snap. Nama pada QRIS mengikuti Merchant Name akun Midtrans.</p>
                                    </div>

                                    <div>
                                        <label for="server_key_{{ $setting->id_konfigurasi }}" class="label">Server Key</label>
                                        <input id="server_key_{{ $setting->id_konfigurasi }}" name="server_key" type="text" maxlength="255" autocomplete="off"
                                               placeholder="{{ $configured ? 'Tersimpan — kosongkan jika tidak diubah' : 'SB-Mid-server-xxxx' }}"
                                               class="input font-mono !text-xs">
                                    </div>

                                    <div>
                                        <label for="client_key_{{ $setting->id_konfigurasi }}" class="label">Client Key</label>
                                        <input id="client_key_{{ $setting->id_konfigurasi }}" name="client_key" type="text" maxlength="255" autocomplete="off"
                                               placeholder="{{ $configured ? 'Tersimpan — kosongkan jika tidak diubah' : 'SB-Mid-client-xxxx' }}"
                                               class="input font-mono !text-xs">
                                    </div>

                                    <div>
                                        <label for="signature_key_{{ $setting->id_konfigurasi }}" class="label">Signature Secret Key</label>
                                        <input id="signature_key_{{ $setting->id_konfigurasi }}" name="signature_key" type="text" maxlength="255" autocomplete="off"
                                               placeholder="{{ $setting->hasSignatureKey() ? 'Tersimpan — kosongkan jika tidak diubah' : 'Opsional' }}"
                                               class="input font-mono !text-xs">
                                        <p class="mt-1 text-[10px] text-slate-400">Disimpan sebagai referensi. Signature webhook tetap dihitung dari Server Key.</p>
                                    </div>

                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label for="merchant_id_{{ $setting->id_konfigurasi }}" class="label">Merchant ID</label>
                                            <input id="merchant_id_{{ $setting->id_konfigurasi }}" name="merchant_id" type="text" maxlength="50"
                                                   value="{{ old('merchant_id', $setting->merchant_id) }}" placeholder="Opsional" class="input !text-xs">
                                        </div>
                                        <div>
                                            <label for="expiry_hours_{{ $setting->id_konfigurasi }}" class="label">Batas Bayar</label>
                                            <input id="expiry_hours_{{ $setting->id_konfigurasi }}" name="expiry_hours" type="number" min="1" max="168" required
                                                   value="{{ old('expiry_hours', $setting->expiry_hours ?: 24) }}" class="input !text-xs">
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <p class="label">Kanal pembayaran</p>
                                    <div class="space-y-3">
                                        @foreach ($channelGroups as $group => $items)
                                            <div>
                                                <p class="mb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $group }}</p>
                                                <div class="grid gap-2 sm:grid-cols-2">
                                                    @foreach ($items as $code => $channel)
                                                        <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 transition hover:border-brand-300">
                                                            <input type="checkbox" name="enabled_payments[]" value="{{ $code }}"
                                                                   @checked(in_array($code, old('enabled_payments', $selectedChannels), true))
                                                                   class="h-3.5 w-3.5 rounded border-slate-300 text-brand-600 focus:ring-brand-200">
                                                            <span class="min-w-0">
                                                                <span class="block truncate text-[11px] font-bold text-slate-700">{{ $channel['label'] }}</span>
                                                                <span class="block truncate text-[10px] text-slate-400">{{ $channel['note'] }}</span>
                                                            </span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="flex justify-end border-t border-slate-200 pt-3">
                                    <button type="submit" class="btn-primary btn-sm">
                                        <x-icon name="check-circle" class="h-4 w-4" /> Simpan
                                    </button>
                                </div>
                            </form>
                        </details>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="grid gap-5 xl:grid-cols-[1.5fr_1fr]">
            {{-- Webhook & QRIS --}}
            <div class="space-y-5">
                <div class="card p-5 sm:p-6">
                    <h3 class="font-display text-base font-bold text-slate-900">URL Webhook / Payment Notification</h3>
                    <p class="mt-0.5 text-xs text-slate-400">
                        Daftarkan URL ini pada Midtrans Dashboard → Settings → Payment Notification URL (sandbox dan production terpisah).
                    </p>
                    <div class="mt-3 flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-3">
                        <code class="min-w-0 flex-1 truncate font-mono text-[11px] text-emerald-300">{{ route('payments.webhook') }}</code>
                        <button type="button" data-copy="{{ route('payments.webhook') }}"
                                class="rounded-lg bg-white/10 p-1.5 text-slate-300 transition hover:bg-white/20 hover:text-white" title="Salin URL">
                            <x-icon name="clipboard" class="h-3.5 w-3.5" />
                        </button>
                    </div>
                    <p class="mt-2 text-[11px] leading-relaxed text-slate-400">
                        Webhook diverifikasi dengan signature SHA-512
                        (<span class="font-mono">order_id + status_code + gross_amount + Server Key</span>).
                    </p>
                </div>

                <div class="card p-5 sm:p-6">
                    <div class="flex items-start gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-brand-600">
                            <x-icon name="qr" class="h-5 w-5" />
                        </span>
                        <div>
                            <h3 class="font-display text-base font-bold text-slate-900">Nama pada QRIS: Nekad Romo</h3>
                            <p class="mt-1 text-[11px] leading-relaxed text-slate-500">
                                Aplikasi mengirim nama toko <b>Nekad Romo</b> pada item Snap dan halaman pembayaran.
                                Nama yang tercetak di dalam QRIS mengikuti <b>Merchant Name</b> akun Midtrans
                                (<b>Nekad Romo.Print Kota Metro</b>). Ubah di Midtrans Dashboard → Settings → Merchant Profile
                                jika ingin tampilan QRIS berbeda.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Transaksi terakhir --}}
            <div class="card overflow-hidden">
                <div class="border-b border-slate-100 p-5">
                    <h3 class="font-display text-base font-bold text-slate-900">Transaksi gateway terakhir</h3>
                    <p class="mt-0.5 text-xs text-slate-400">Enam transaksi terbaru dari Midtrans.</p>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse ($recentTransactions as $transaction)
                        <div class="flex items-center justify-between gap-3 px-4 py-3">
                            <div class="min-w-0">
                                <p class="truncate text-xs font-bold text-slate-800">{{ $transaction->pesanan?->kode_pesanan ?? $transaction->order_id_gateway }}</p>
                                <p class="text-[10px] uppercase tracking-wide text-slate-400">
                                    {{ $transaction->payment_type ?? 'qris' }} · {{ $transaction->updated_at?->diffForHumans() }}
                                </p>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <span class="text-xs font-bold text-slate-700">Rp {{ number_format((int) $transaction->gross_amount, 0, ',', '.') }}</span>
                                @php
                                    $gatewayLabel = match ($transaction->transaction_status) {
                                        'settlement', 'capture' => 'Lunas',
                                        'pending' => 'Menunggu',
                                        'expire' => 'Kadaluarsa',
                                        'deny', 'failure' => 'Gagal',
                                        'cancel' => 'Dibatalkan',
                                        'refund', 'partial_refund' => 'Dikembalikan',
                                        'chargeback', 'partial_chargeback' => 'Chargeback',
                                        default => ucfirst((string) $transaction->transaction_status),
                                    };
                                    $gatewayClass = match ($transaction->transaction_status) {
                                        'settlement', 'capture' => 'badge-emerald',
                                        'pending' => 'badge-amber',
                                        'expire' => 'badge-slate',
                                        'refund', 'partial_refund', 'chargeback', 'partial_chargeback' => 'badge-rose',
                                        default => 'badge-rose',
                                    };
                                @endphp
                                <span class="badge {{ $gatewayClass }}">{{ $gatewayLabel }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="px-5 py-8 text-center text-xs text-slate-400">Belum ada transaksi gateway.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-brand-100 bg-brand-50/60 p-5">
            <p class="flex items-center gap-1.5 text-xs font-bold text-brand-800">
                <x-icon name="lock" class="h-4 w-4" /> Catatan keamanan
            </p>
            <ul class="mt-2 grid gap-1.5 text-[11px] leading-relaxed text-brand-700/80 sm:grid-cols-3">
                <li class="flex gap-1.5"><x-icon name="check" class="mt-0.5 h-3 w-3 shrink-0" /> Server Key tidak pernah dikirim ke browser.</li>
                <li class="flex gap-1.5"><x-icon name="check" class="mt-0.5 h-3 w-3 shrink-0" /> Kunci disimpan di database dan disamarkan di layar.</li>
                <li class="flex gap-1.5"><x-icon name="check" class="mt-0.5 h-3 w-3 shrink-0" /> Status pesanan disinkronkan otomatis &amp; manual.</li>
            </ul>
        </div>
    </div>
@endsection
