@props(['status' => 'Menunggu Pembayaran'])

@php
    $map = [
        'Lunas' => 'badge-emerald',
        'Menunggu Pembayaran' => 'badge-amber',
        'Kadaluarsa' => 'badge-slate',
        'Gagal' => 'badge-rose',
        'Dibatalkan' => 'badge-rose',
        'Dikembalikan' => 'badge-violet',
        'Chargeback' => 'badge-rose',
    ];
    $class = $map[$status] ?? 'badge-slate';
@endphp

<span {{ $attributes->merge(['class' => $class]) }}>
    <span class="h-1.5 w-1.5 rounded-full bg-current opacity-70"></span>
    {{ $status }}
</span>
