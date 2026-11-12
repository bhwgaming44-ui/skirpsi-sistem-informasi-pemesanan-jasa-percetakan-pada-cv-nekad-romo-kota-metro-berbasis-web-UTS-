@props(['name'])

@error($name)
    <p {{ $attributes->merge(['class' => 'mt-1.5 flex items-center gap-1 text-xs font-semibold text-rose-600']) }}>
        <x-icon name="alert" class="h-3.5 w-3.5" />
        {{ $message }}
    </p>
@enderror
