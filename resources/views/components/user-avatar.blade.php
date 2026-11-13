@props(['user', 'size' => 'h-8 w-8', 'text' => 'text-xs'])

@if ($user->fotoProfilUrl())
    <img src="{{ $user->fotoProfilUrl() }}" alt="Foto profil {{ $user->nama }}"
         class="{{ $size }} shrink-0 rounded-lg object-cover ring-1 ring-black/5" loading="lazy">
@else
    <span class="{{ $size }} flex shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-brand-500 to-accent-500 {{ $text }} font-bold text-white">
        {{ strtoupper(substr($user->nama, 0, 1)) }}
    </span>
@endif
