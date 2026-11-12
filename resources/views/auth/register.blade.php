@extends('layouts.auth')

@section('title', 'Daftar Akun')

@section('content')
    <div class="mb-7">
        <span class="badge bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-100">
            <x-icon name="sparkles" class="h-3.5 w-3.5" /> Gratis & tanpa biaya
        </span>
        <h1 class="mt-4 font-display text-3xl font-extrabold tracking-tight text-slate-900">Buat akun pelanggan</h1>
        <p class="mt-2 text-sm leading-relaxed text-slate-500">
            Daftar sekali untuk memesan cetak online, menyimpan riwayat pesanan, dan melacak produksi.
        </p>
    </div>

    <form method="POST" action="{{ route('register.store') }}" class="space-y-4" x-data="{ show: false }">
        @csrf

        <div>
            <label for="nama" class="label">Nama Lengkap</label>
            <input id="nama" name="nama" type="text" value="{{ old('nama') }}" required autofocus
                   autocomplete="name" placeholder="Nama sesuai identitas"
                   class="input @error('nama') !border-rose-300 !ring-rose-100 @enderror">
            <x-form-error name="nama" />
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="username" class="label">Username</label>
                <div class="relative">
                    <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-bold">@</span>
                    <input id="username" name="username" type="text" value="{{ old('username') }}" required
                           autocomplete="username" placeholder="raihan_metro"
                           class="input !pl-9 @error('username') !border-rose-300 !ring-rose-100 @enderror">
                </div>
                <x-form-error name="username" />
            </div>

            <div>
                <label for="no_hp" class="label">Nomor HP / WhatsApp</label>
                <input id="no_hp" name="no_hp" type="text" value="{{ old('no_hp') }}" required
                       autocomplete="tel" placeholder="08xxxxxxxxxx"
                       class="input @error('no_hp') !border-rose-300 !ring-rose-100 @enderror">
                <x-form-error name="no_hp" />
            </div>
        </div>

        <div>
            <label for="email" class="label">Email</label>
            <div class="relative">
                <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                    <x-icon name="mail" class="h-4.5 w-4.5" />
                </span>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required
                       autocomplete="email" placeholder="nama@email.com"
                       class="input !pl-11 @error('email') !border-rose-300 !ring-rose-100 @enderror">
            </div>
            <x-form-error name="email" />
        </div>

        <div>
            <label for="alamat" class="label">Alamat <span class="font-medium normal-case tracking-normal text-slate-400">(opsional)</span></label>
            <textarea id="alamat" name="alamat" rows="2" autocomplete="street-address"
                      placeholder="Alamat lengkap untuk pengiriman"
                      class="textarea @error('alamat') !border-rose-300 !ring-rose-100 @enderror">{{ old('alamat') }}</textarea>
            <x-form-error name="alamat" />
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="password" class="label">Password</label>
                <div class="relative">
                    <input id="password" name="password" :type="show ? 'text' : 'password'" required
                           autocomplete="new-password" placeholder="Minimal 8 karakter"
                           class="input !pr-10 @error('password') !border-rose-300 !ring-rose-100 @enderror">
                    <button type="button" @click="show = !show"
                            class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg p-1 text-slate-400 transition hover:text-brand-600">
                        <x-icon name="eye" class="h-4 w-4" />
                    </button>
                </div>
                <x-form-error name="password" />
            </div>

            <div>
                <label for="password_confirmation" class="label">Ulangi Password</label>
                <input id="password_confirmation" name="password_confirmation" :type="show ? 'text' : 'password'" required
                       autocomplete="new-password" placeholder="Sama dengan password"
                       class="input">
            </div>
        </div>

        <label class="flex items-start gap-2.5 text-sm leading-relaxed text-slate-500">
            <input type="checkbox" required
                   class="mt-0.5 h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-200">
            <span>Saya setuju dengan syarat layanan dan kebijakan privasi CV Nekad Romo.</span>
        </label>

        <button type="submit" class="btn-primary w-full !py-3.5">
            <x-icon name="user" class="h-4.5 w-4.5" /> Daftar & Mulai Pesan
        </button>
    </form>

    <p class="mt-7 text-center text-sm text-slate-500">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="font-bold text-brand-600 transition hover:text-brand-700">Masuk di sini</a>
    </p>
@endsection
