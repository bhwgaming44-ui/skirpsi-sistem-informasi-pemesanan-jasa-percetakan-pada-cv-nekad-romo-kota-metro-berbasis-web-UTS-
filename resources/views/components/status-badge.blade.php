@props(['status' => 'Menunggu Pembayaran', 'dot' => true])

@php
    $map = [
        'Menunggu Pembayaran' => ['badge-amber', 'clock'],
        'Pesanan Masuk (Antrean)' => ['badge-sky', 'clipboard'],
        'Sedang Dicetak' => ['badge-violet', 'printer'],
        'Selesai Cetak' => ['badge-indigo', 'brush'],
        'Siap Diambil/Dikirim' => ['badge-teal', 'truck'],
        'Selesai' => ['badge-emerald', 'check-circle'],
        'Batal' => ['badge-rose', 'close'],
    ];
    [$class, $icon] = $map[$status] ?? ['badge-slate', 'info'];
@endphp

<span {{ $attributes->merge(['class' => $class]) }}>
    @if ($dot)
        <span class="h-1.5 w-1.5 rounded-full bg-current opacity-70"></span>
    @endif
    {{ $status }}
</span>
