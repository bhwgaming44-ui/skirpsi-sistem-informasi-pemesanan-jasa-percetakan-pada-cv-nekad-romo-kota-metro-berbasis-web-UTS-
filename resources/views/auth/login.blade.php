@extends('layouts.auth')

@section('title', 'Masuk Akun')

@section('content')
    <div class="mb-8">
        <span class="badge bg-brand-50 text-brand-700 ring-1 ring-inset ring-brand-100">
            <x-icon name="lock" class="h-3.5 w-3.5" /> Area Anggota
        </span>
        <h1 class="mt-4 font-display text-3xl font-extrabold tracking-tight text-slate-900">Selamat datang kembali 👋</h1>
        <p class="mt-2 text-sm leading-relaxed text-slate-500">
            Masuk untuk melanjutkan pesanan cetak, memantau produksi, dan melihat riwayat transaksi Anda.
        </p>
    </div>

    @if ($errors->any() && ! $errors->has('login'))
        <div class="mb-5 flex items-start gap-2.5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <x-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0" />
            <span>Periksa kembali data yang Anda masukkan.</span>
        </div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" class="space-y-4" x-data="{ show: false }">
        @csrf

        <div>
            <label for="login" class="label">Username atau Email</label>
            <div class="relative">
                <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                    <x-icon name="user" class="h-4.5 w-4.5" />
                </span>
                <input id="login" name="login" type="text" value="{{ old('login') }}" required autofocus
                       autocomplete="username" placeholder="contoh: raihan atau nama@email.com"
                       class="input !pl-11 @error('login') !border-rose-300 !ring-rose-100 @enderror">
            </div>
            <x-form-error name="login" />
        </div>

        <div>
            <div class="flex items-center justify-between">
                <label for="password" class="label !mb-2">Password</label>
                <span class="text-[11px] font-medium text-slate-400">Minimal 8 karakter</span>
            </div>
            <div class="relative">
                <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                    <x-icon name="lock" class="h-4.5 w-4.5" />
                </span>
                <input id="password" name="password" :type="show ? 'text' : 'password'" required
                       autocomplete="current-password" placeholder="••••••••"
                       class="input !pl-11 !pr-11 @error('password') !border-rose-300 !ring-rose-100 @enderror">
                <button type="button" @click="show = !show"
                        class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg p-1 text-slate-400 transition hover:text-brand-600"
                        :aria-label="show ? 'Sembunyikan password' : 'Tampilkan password'">
                    <x-icon name="eye" class="h-4.5 w-4.5" />
                </button>
            </div>
            <x-form-error name="password" />
        </div>

        <label class="flex items-center gap-2.5 text-sm text-slate-500">
            <input type="checkbox" name="remember" value="1"
                   class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-200">
            Ingat saya di perangkat ini
        </label>

        <button type="submit" class="btn-primary w-full !py-3.5">
            <x-icon name="logout" class="h-4.5 w-4.5 rotate-180" /> Masuk ke Dashboard
        </button>
    </form>

    <div class="relative my-7">
        <div class="absolute inset-0 flex items-center"><span class="w-full border-t border-slate-200"></span></div>
        <div class="relative flex justify-center"><span class="bg-[#f5f6fb] px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Akun demo</span></div>
    </div>

    <div class="grid gap-2 sm:grid-cols-3" x-data="{ fill(login, password) { document.getElementById('login').value = login; document.getElementById('password').value = password; } }">
        @foreach ([
            ['label' => 'Admin', 'login' => 'admin', 'password' => 'admin123', 'icon' => 'settings', 'tone' => 'bg-brand-50 text-brand-600'],
            ['label' => 'Pimpinan', 'login' => 'pimpinan', 'password' => 'pimpinan123', 'icon' => 'chart', 'tone' => 'bg-violet-50 text-violet-600'],
            ['label' => 'Pelanggan', 'login' => 'raihan', 'password' => 'pelanggan123', 'icon' => 'user', 'tone' => 'bg-emerald-50 text-emerald-600'],
        ] as $demo)
            <button type="button" @click="fill('{{ $demo['login'] }}', '{{ $demo['password'] }}')"
                    class="group flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-left transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg {{ $demo['tone'] }}">
                    <x-icon :name="$demo['icon']" class="h-4 w-4" />
                </span>
                <span class="leading-tight">
                    <span class="block text-xs font-bold text-slate-700">{{ $demo['label'] }}</span>
                    <span class="block text-[10px] text-slate-400">{{ $demo['login'] }}</span>
                </span>
            </button>
        @endforeach
    </div>

    <p class="mt-7 text-center text-sm text-slate-500">
        Belum punya akun?
        <a href="{{ route('register') }}" class="font-bold text-brand-600 transition hover:text-brand-700">Daftar sekarang</a>
    </p>
@endsection
