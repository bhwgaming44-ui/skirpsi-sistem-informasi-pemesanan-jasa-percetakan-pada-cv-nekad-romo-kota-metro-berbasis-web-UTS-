@extends('layouts.auth')

@section('title', 'Masuk Akun')

@section('content')
    <div class="mb-6">
        <span class="badge bg-brand-50 text-brand-700 ring-1 ring-inset ring-brand-100">
            <x-icon name="lock" class="h-3.5 w-3.5" /> Area Anggota
        </span>
        <h1 class="mt-4 font-display text-3xl font-extrabold tracking-tight text-slate-900">Selamat datang kembali 👋</h1>
        <p class="mt-2 text-sm leading-relaxed text-slate-500">
            Masuk untuk melanjutkan pesanan cetak, memantau produksi, atau berpindah peran akun untuk pengujian.
        </p>
    </div>

    @auth
        <div class="mb-6 rounded-2xl border-2 border-brand-500 bg-brand-50/70 p-4 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-600 font-bold text-white shadow">
                        {{ strtoupper(substr(auth()->user()->nama, 0, 1)) }}
                    </span>
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-brand-600">Perangkat Ini Sedang Aktif</p>
                        <p class="truncate text-sm font-bold text-slate-800">{{ auth()->user()->nama }}</p>
                        <p class="text-[11px] text-slate-500">Peran: <span class="font-semibold capitalize text-brand-700">{{ auth()->user()->role }}</span> ({{ auth()->user()->username }})</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ auth()->user()->dashboardRoute() }}" class="btn-primary btn-sm !py-2">
                        Buka Dashboard
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn-white btn-sm !py-2 !text-rose-600 hover:!bg-rose-50" title="Keluar dari akun ini">
                            <x-icon name="logout" class="h-4 w-4" /> Keluar
                        </button>
                    </form>
                </div>
            </div>
            <p class="mt-3 border-t border-brand-200/60 pt-2.5 text-xs text-slate-600">
                💡 Ingin berganti akun untuk pengujian ke dosen? Gunakan tombol <b>1-Klik Masuk</b> di bawah atau masukkan akun lain. Sistem akan otomatis beralih akun tanpa mengunci perangkat ini.
            </p>
        </div>
    @endauth

    {{-- Pilihan Beralih Cepat Antar Akun (Testing Dosen & Sidang) --}}
    <div class="mb-6">
        <div class="mb-3 flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                <x-icon name="shield" class="h-4 w-4 text-brand-600" /> Beralih Akun 1-Klik (Khusus Demo &amp; Sidang)
            </span>
            <span class="text-[11px] font-medium text-slate-400">Pilih role langsung masuk</span>
        </div>

        <div class="grid gap-2.5 sm:grid-cols-3" x-data="{ fill(login, password) { document.getElementById('login').value = login; document.getElementById('password').value = password; } }">
            @foreach ([
                ['label' => 'Admin (Kasir/Staff)', 'role' => 'admin', 'login' => 'admin', 'password' => 'admin123', 'desc' => 'Kelola pesanan, katalog & produksi', 'icon' => 'settings', 'tone' => 'bg-brand-50 text-brand-600 border-brand-200'],
                ['label' => 'Pimpinan (Owner)', 'role' => 'pimpinan', 'login' => 'pimpinan', 'password' => 'pimpinan123', 'desc' => 'Laporan penjualan & omzet cetak', 'icon' => 'chart', 'tone' => 'bg-violet-50 text-violet-600 border-violet-200'],
                ['label' => 'Pelanggan (Pemesan)', 'role' => 'pelanggan', 'login' => 'raihan', 'password' => 'pelanggan123', 'desc' => 'Pesan produk, upload & bayar', 'icon' => 'user', 'tone' => 'bg-emerald-50 text-emerald-600 border-emerald-200'],
            ] as $demo)
                <div class="flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-3 shadow-sm transition hover:shadow-md">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg {{ $demo['tone'] }}">
                                <x-icon :name="$demo['icon']" class="h-4 w-4" />
                            </span>
                            <span class="text-xs font-bold text-slate-800">{{ $demo['label'] }}</span>
                        </div>
                        <p class="mt-1 text-[10px] leading-tight text-slate-400">{{ $demo['desc'] }}</p>
                        <p class="mt-1 font-mono text-[10px] text-slate-500">user: <span class="font-bold text-slate-700">{{ $demo['login'] }}</span></p>
                    </div>
                    <div class="mt-3 flex gap-1.5 border-t border-slate-100 pt-2">
                        <form method="POST" action="{{ route('switch-account') }}" class="flex-1">
                            @csrf
                            <input type="hidden" name="role" value="{{ $demo['role'] }}">
                            <button type="submit" class="btn btn-sm w-full !bg-brand-600 !py-1.5 !text-[11px] !text-white shadow-sm hover:!bg-brand-700"
                                    title="Langsung masuk sebagai {{ $demo['label'] }}">
                                ⚡ Masuk 1-Klik
                            </button>
                        </form>
                        <button type="button" @click="fill('{{ $demo['login'] }}', '{{ $demo['password'] }}')"
                                class="btn-white btn-sm !px-2.5 !py-1.5 !text-[11px] text-slate-500"
                                title="Salin ke form login manual">
                            Isi Form
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="relative my-6">
        <div class="absolute inset-0 flex items-center"><span class="w-full border-t border-slate-200"></span></div>
        <div class="relative flex justify-center"><span class="bg-[#f5f6fb] px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Atau masuk manual</span></div>
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
                       autocomplete="username" placeholder="contoh: admin, pimpinan, atau raihan"
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

    <p class="mt-7 text-center text-sm text-slate-500">
        Belum punya akun?
        <a href="{{ route('register') }}" class="font-bold text-brand-600 transition hover:text-brand-700">Daftar sekarang</a>
    </p>
@endsection
