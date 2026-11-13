@extends('layouts.dashboard')

@section('title', 'Profil Saya')

@section('content')
    <div class="space-y-5">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="font-display text-xl font-extrabold text-slate-900">Profil Saya</h2>
                <p class="mt-0.5 text-sm text-slate-500">Perbarui data kontak dan keamanan akun Anda.</p>
            </div>
            <a href="{{ route('customer.dashboard') }}" class="btn-white btn-sm">
                <x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke dashboard
            </a>
        </div>

        <div class="grid gap-5 xl:grid-cols-[1.6fr_1fr]">
            <div class="space-y-5">
                {{-- Profile photo --}}
                <div class="card overflow-hidden p-5">
                    @include('partials.profile-photo-form', ['user' => $user])
                </div>

                {{-- Profile data --}}
                <form method="POST" action="{{ route('customer.profile.update') }}" class="card overflow-hidden">
                    @csrf
                    @method('PUT')

                    <div class="flex items-center gap-3 border-b border-slate-100 p-5">
                        <x-user-avatar :user="$user" size="h-11 w-11" text="text-sm" />
                        <div class="min-w-0">
                            <h3 class="font-display text-base font-bold text-slate-900">Data pribadi</h3>
                            <p class="text-xs text-slate-400">Terdaftar sejak {{ $user->created_at?->translatedFormat('d F Y') ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="grid gap-4 p-5 sm:grid-cols-2">
                        <div>
                            <label for="nama" class="label">Nama Lengkap</label>
                            <input id="nama" name="nama" type="text" value="{{ old('nama', $user->nama) }}" required
                                   autocomplete="name" class="input @error('nama') !border-rose-300 !ring-rose-100 @enderror">
                            <x-form-error name="nama" />
                        </div>

                        <div>
                            <label for="username" class="label">Username</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">@</span>
                                <input id="username" name="username" type="text" value="{{ old('username', $user->username) }}" required
                                       autocomplete="username" class="input !pl-9 @error('username') !border-rose-300 !ring-rose-100 @enderror">
                            </div>
                            <x-form-error name="username" />
                        </div>

                        <div>
                            <label for="email" class="label">Email</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                                    <x-icon name="mail" class="h-4.5 w-4.5" />
                                </span>
                                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required
                                       autocomplete="email" class="input !pl-11 @error('email') !border-rose-300 !ring-rose-100 @enderror">
                            </div>
                            <x-form-error name="email" />
                        </div>

                        <div>
                            <label for="no_hp" class="label">Nomor HP / WhatsApp</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                                    <x-icon name="phone" class="h-4.5 w-4.5" />
                                </span>
                                <input id="no_hp" name="no_hp" type="text" value="{{ old('no_hp', $user->no_hp) }}" required
                                       autocomplete="tel" class="input !pl-11 @error('no_hp') !border-rose-300 !ring-rose-100 @enderror">
                            </div>
                            <x-form-error name="no_hp" />
                        </div>

                        <div class="sm:col-span-2">
                            <label for="alamat" class="label">Alamat</label>
                            <textarea id="alamat" name="alamat" rows="3" autocomplete="street-address"
                                      placeholder="Alamat lengkap untuk pengiriman pesanan"
                                      class="textarea @error('alamat') !border-rose-300 !ring-rose-100 @enderror">{{ old('alamat', $user->alamat) }}</textarea>
                            <x-form-error name="alamat" />
                        </div>
                    </div>

                    <div class="flex justify-end border-t border-slate-100 bg-slate-50/60 px-5 py-4">
                        <button type="submit" class="btn-primary">
                            <x-icon name="check" class="h-4.5 w-4.5" /> Simpan Perubahan
                        </button>
                    </div>
                </form>

                {{-- Password --}}
                <form method="POST" action="{{ route('customer.profile.update') }}" class="card overflow-hidden">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="nama" value="{{ $user->nama }}">
                    <input type="hidden" name="username" value="{{ $user->username }}">
                    <input type="hidden" name="email" value="{{ $user->email }}">
                    <input type="hidden" name="no_hp" value="{{ $user->no_hp }}">
                    <input type="hidden" name="alamat" value="{{ $user->alamat }}">

                    <div class="flex items-center gap-3 border-b border-slate-100 p-5">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100 text-slate-600">
                            <x-icon name="lock" class="h-5 w-5" />
                        </span>
                        <div>
                            <h3 class="font-display text-base font-bold text-slate-900">Keamanan akun</h3>
                            <p class="text-xs text-slate-400">Kosongkan jika tidak ingin mengganti password.</p>
                        </div>
                    </div>

                    <div class="grid gap-4 p-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="password_lama" class="label">Password Lama</label>
                            <input id="password_lama" name="password_lama" type="password" autocomplete="current-password"
                                   placeholder="Masukkan password saat ini"
                                   class="input @error('password_lama') !border-rose-300 !ring-rose-100 @enderror">
                            <x-form-error name="password_lama" />
                        </div>
                        <div>
                            <label for="password" class="label">Password Baru</label>
                            <input id="password" name="password" type="password" autocomplete="new-password"
                                   placeholder="Minimal 8 karakter"
                                   class="input @error('password') !border-rose-300 !ring-rose-100 @enderror">
                            <x-form-error name="password" />
                        </div>
                        <div>
                            <label for="password_confirmation" class="label">Ulangi Password Baru</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                                   placeholder="Sama dengan password baru" class="input">
                        </div>
                    </div>

                    <div class="flex justify-end border-t border-slate-100 bg-slate-50/60 px-5 py-4">
                        <button type="submit" class="btn-dark">
                            <x-icon name="shield" class="h-4.5 w-4.5" /> Perbarui Password
                        </button>
                    </div>
                </form>
            </div>

            {{-- Sidebar --}}
            <aside class="space-y-4">
                <div class="card overflow-hidden">
                    <div class="bg-gradient-to-br from-ink-900 to-brand-900 p-5 text-white">
                        <div class="flex items-center gap-3">
                            <x-user-avatar :user="$user" size="h-14 w-14" text="text-lg" />
                            <div class="min-w-0">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-indigo-200/60">Akun pelanggan</p>
                                <p class="mt-0.5 truncate font-display text-lg font-bold">{{ $user->nama }}</p>
                                <p class="truncate text-xs text-indigo-200/70">{{ $user->email }}</p>
                            </div>
                        </div>
                        <span class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-white/10 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider backdrop-blur">
                            <x-icon name="user" class="h-3 w-3" /> {{ $user->role }}
                        </span>
                    </div>

                    <div class="divide-y divide-slate-100">
                        <div class="flex items-center gap-2.5 px-5 py-3.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600">
                                <x-icon name="phone" class="h-4 w-4" />
                            </span>
                            <div class="min-w-0 leading-tight">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Nomor HP</p>
                                <p class="truncate text-xs font-semibold text-slate-800">{{ $user->no_hp }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2.5 px-5 py-3.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600">
                                <x-icon name="map-pin" class="h-4 w-4" />
                            </span>
                            <div class="min-w-0 leading-tight">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Alamat</p>
                                <p class="line-clamp-2 text-xs font-semibold text-slate-800">{{ $user->alamat ?: 'Belum diisi' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-brand-100 bg-brand-50/60 p-5">
                    <p class="flex items-center gap-1.5 text-xs font-bold text-brand-800">
                        <x-icon name="info" class="h-4 w-4" /> Kenapa melengkapi profil?
                    </p>
                    <ul class="mt-2 space-y-1.5 text-[11px] leading-relaxed text-brand-700/80">
                        <li class="flex gap-1.5"><x-icon name="check" class="mt-0.5 h-3 w-3 shrink-0" /> Alamat otomatis terisi saat checkout.</li>
                        <li class="flex gap-1.5"><x-icon name="check" class="mt-0.5 h-3 w-3 shrink-0" /> Admin mudah menghubungi terkait pesanan.</li>
                        <li class="flex gap-1.5"><x-icon name="check" class="mt-0.5 h-3 w-3 shrink-0" /> Invoice menampilkan data yang benar.</li>
                    </ul>
                </div>

                <a href="{{ route('customer.orders.index') }}" class="btn-white w-full">
                    <x-icon name="receipt" class="h-4.5 w-4.5" /> Lihat riwayat pesanan
                </a>
            </aside>
        </div>
    </div>
@endsection
