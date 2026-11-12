@props([
    'label' => 'Metrik',
    'value' => 0,
    'icon' => 'chart',
    'format' => 'number',
    'trend' => null,
    'hint' => null,
    'accent' => 'brand',
])

@php
    $accents = [
        'brand' => 'from-brand-500 to-indigo-500 shadow-brand-500/30',
        'emerald' => 'from-emerald-500 to-teal-500 shadow-emerald-500/30',
        'amber' => 'from-amber-500 to-orange-500 shadow-amber-500/30',
        'sky' => 'from-sky-500 to-cyan-500 shadow-sky-500/30',
        'violet' => 'from-violet-500 to-fuchsia-500 shadow-violet-500/30',
        'rose' => 'from-rose-500 to-pink-500 shadow-rose-500/30',
    ];
    $gradient = $accents[$accent] ?? $accents['brand'];
    $isNumeric = is_numeric($value);
@endphp

<div class="card group relative overflow-hidden p-5 transition-all duration-500 hover:-translate-y-1 hover:shadow-[var(--shadow-soft)]">
    <div class="pointer-events-none absolute -right-8 -top-10 h-28 w-28 rounded-full bg-gradient-to-br {{ $gradient }} opacity-[0.07] blur-2xl transition group-hover:opacity-20"></div>

    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="truncate text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ $label }}</p>

            <p class="mt-2 font-display text-2xl font-bold tracking-tight text-slate-900">
                @if ($isNumeric)
                    <span data-counter="{{ $value }}" data-format="{{ $format }}">0</span>
                @else
                    {{ $value }}
                @endif
            </p>

            <div class="mt-2 flex flex-wrap items-center gap-2">
                @if ($trend !== null)
                    @php $naik = (float) $trend >= 0; @endphp
                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-bold {{ $naik ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                        <x-icon :name="$naik ? 'trending-up' : 'trending-down'" class="h-3.5 w-3.5" />
                        {{ $naik ? '+' : '' }}{{ (int) $trend }}%
                    </span>
                    <span class="text-[11px] text-slate-400">vs periode lalu</span>
                @elseif ($hint)
                    <span class="text-[11px] text-slate-400">{{ $hint }}</span>
                @endif
            </div>
        </div>

        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br {{ $gradient }} text-white shadow-lg">
            <x-icon :name="$icon" class="h-5 w-5" />
        </span>
    </div>
</div>
