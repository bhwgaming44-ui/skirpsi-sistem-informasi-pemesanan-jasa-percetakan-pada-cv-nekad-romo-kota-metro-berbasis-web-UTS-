@props(['icon' => 'archive', 'title' => 'Belum ada data', 'message' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center gap-3 rounded-2xl border border-dashed border-slate-300 bg-slate-50/60 px-6 py-14 text-center']) }}>
    <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white text-brand-500 shadow-sm ring-1 ring-slate-200">
        <x-icon :name="$icon" class="h-7 w-7" />
    </span>
    <h3 class="font-display text-base font-bold text-slate-800">{{ $title }}</h3>
    @if ($message)
        <p class="max-w-sm text-sm leading-relaxed text-slate-500">{{ $message }}</p>
    @endif
    {{ $slot }}
</div>
