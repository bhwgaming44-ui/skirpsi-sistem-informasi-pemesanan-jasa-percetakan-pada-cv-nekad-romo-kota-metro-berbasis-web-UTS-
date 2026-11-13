@extends('layouts.dashboard')

@section('title', 'Pengguna & Password')

@section('content')
    @php
        $roleStyles = [
            'admin' => 'bg-brand-50 text-brand-700',
            'pimpinan' => 'bg-amber-50 text-amber-700',
            'pelanggan' => 'bg-slate-100 text-slate-600',
        ];
    @endphp

    <div class="space-y-5">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="font-display text-xl font-extrabold text-slate-900">Pengguna & Password</h2>
                <p class="mt-0.5 text-sm text-slate-500">Lihat semua akun, role, kontak, dan kredensial login.</p>
            </div>
            <span class="badge bg-brand-50 text-brand-700">
                <x-icon name="users" class="h-3.5 w-3.5" /> {{ $users->total() }} pengguna
            </span>
        </div>

        <div class="rounded-2xl border border-amber-200 bg-amber-50/70 p-4">
            <div class="flex gap-2.5">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-700">
                    <x-icon name="shield" class="h-4 w-4" />
                </span>
                <div>
                    <p class="text-xs font-bold text-amber-900">Kredensial hanya untuk admin</p>
                    <p class="mt-0.5 text-[11px] leading-relaxed text-amber-800/80">
                        Password disimpan terenkripsi dan hanya ditampilkan setelah tombol lihat ditekan.
                        Password yang dibuat sebelum fitur ini aktif tidak dapat dipulihkan karena sudah di-hash —
                        gunakan tombol <span class="font-semibold">Buat Password Baru</span> pada akun tersebut.
                    </p>
                </div>
            </div>
        </div>

        @if (session('generated_password'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4" x-data="{ copied: false }">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-start gap-2.5">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
                            <x-icon name="key" class="h-4 w-4" />
                        </span>
                        <div>
                            <p class="text-xs font-bold text-emerald-900">
                                Password baru untuk {{ session('generated_username') }}
                            </p>
                            <p class="mt-1 font-mono text-lg font-bold tracking-wider text-emerald-800">{{ session('generated_password') }}</p>
                            <p class="mt-0.5 text-[10px] text-emerald-700/70">Salin sekarang dan berikan kepada pemilik akun.</p>
                        </div>
                    </div>
                    <button type="button"
                            @click="navigator.clipboard.writeText('{{ session('generated_password') }}'); copied = true; setTimeout(() => copied = false, 1500)"
                            class="btn-white btn-sm">
                        <x-icon name="copy" class="h-4 w-4" />
                        <span x-text="copied ? 'Tersalin!' : 'Salin'">Salin</span>
                    </button>
                </div>
            </div>
        @endif

        <form method="GET" action="{{ route('admin.users.index') }}" class="card flex flex-col gap-3 p-4 sm:flex-row sm:items-center">
            <div class="relative flex-1">
                <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                    <x-icon name="search" class="h-4.5 w-4.5" />
                </span>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama, username, email, atau no. HP..." class="input !pl-10">
            </div>
            <select name="role" class="input sm:w-44">
                <option value="">Semua role</option>
                @foreach ($roles as $value => $label)
                    <option value="{{ $value }}" @selected(request('role') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary btn-sm !px-4">Cari</button>
                @if (request()->filled('q') || request()->filled('role'))
                    <a href="{{ route('admin.users.index') }}" class="btn-white btn-sm">Reset</a>
                @endif
            </div>
        </form>

        <div class="card overflow-hidden">
            @if ($users->isEmpty())
                <div class="p-5">
                    <x-empty-state icon="users" title="Pengguna tidak ditemukan"
                                   message="Belum ada akun yang cocok dengan pencarian atau filter role Anda." />
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="table-modern min-w-[980px]">
                        <thead>
                            <tr>
                                <th>Pengguna</th>
                                <th>Role</th>
                                <th>Kontak</th>
                                <th>Terdaftar</th>
                                <th>Password</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-2.5">
                                            <x-user-avatar :user="$user" size="h-9 w-9" />
                                            <div class="min-w-0 leading-tight">
                                                <p class="truncate text-xs font-bold text-slate-800">{{ $user->nama }}</p>
                                                <p class="truncate text-[10px] text-slate-400">@ {{ $user->username }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $roleStyles[$user->role] ?? 'bg-slate-100 text-slate-600' }}">
                                            {{ $roles[$user->role] ?? $user->role }}
                                        </span>
                                    </td>
                                    <td>
                                        <p class="text-[11px] font-semibold text-slate-700">{{ $user->no_hp }}</p>
                                        <p class="text-[10px] text-slate-400">{{ $user->email }}</p>
                                    </td>
                                    <td class="text-[11px] text-slate-500">
                                        {{ $user->created_at?->translatedFormat('d M Y') ?? '—' }}
                                    </td>
                                    <td>
                                        @if ($user->hasStoredPassword())
                                            <div x-data="{
                                                    shown: false,
                                                    loading: false,
                                                    copied: false,
                                                    value: '',
                                                    error: '',
                                                    async reveal() {
                                                        if (this.shown) {
                                                            this.shown = false;
                                                            this.value = '';
                                                            return;
                                                        }
                                                        this.loading = true;
                                                        this.error = '';
                                                        try {
                                                            const response = await fetch('{{ route('admin.users.password.reveal', $user) }}', {
                                                                method: 'POST',
                                                                headers: {
                                                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                                                    'Accept': 'application/json',
                                                                },
                                                            });
                                                            const data = await response.json();
                                                            if (!response.ok) {
                                                                this.error = data.message || 'Gagal mengambil password.';
                                                                return;
                                                            }
                                                            this.value = data.password;
                                                            this.shown = true;
                                                        } catch (exception) {
                                                            this.error = 'Gagal mengambil password.';
                                                        } finally {
                                                            this.loading = false;
                                                        }
                                                    },
                                                }">
                                                <div class="flex items-center gap-1.5">
                                                    <code x-show="shown" x-text="value"
                                                          class="max-w-44 truncate rounded-lg bg-slate-900 px-2 py-1 font-mono text-[11px] font-bold text-emerald-300"></code>
                                                    <span x-show="!shown" class="badge bg-emerald-50 text-emerald-700">
                                                        <x-icon name="key" class="h-3 w-3" /> Tersimpan
                                                    </span>
                                                    <button type="button" @click="reveal()" :disabled="loading"
                                                            class="flex h-7 w-7 items-center justify-center rounded-lg text-slate-400 transition hover:bg-brand-50 hover:text-brand-600 disabled:opacity-50"
                                                            :title="shown ? 'Sembunyikan password' : 'Lihat password'">
                                                        <x-icon name="eye" class="h-4 w-4" x-show="!shown" />
                                                        <x-icon name="eye-off" class="h-4 w-4" x-show="shown" x-cloak />
                                                    </button>
                                                    <button type="button" x-show="shown" x-cloak
                                                            @click="navigator.clipboard.writeText(value); copied = true; setTimeout(() => copied = false, 1500)"
                                                            class="flex h-7 w-7 items-center justify-center rounded-lg text-slate-400 transition hover:bg-emerald-50 hover:text-emerald-600"
                                                            :title="copied ? 'Tersalin' : 'Salin password'">
                                                        <x-icon name="copy" class="h-4 w-4" />
                                                    </button>
                                                </div>
                                                <p x-show="error" x-text="error" class="mt-1 max-w-56 text-[10px] font-medium text-rose-600" x-cloak></p>
                                            </div>
                                        @else
                                            <span class="badge bg-slate-100 text-slate-500">Belum tersedia</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        @if ($user->hasStoredPassword())
                                            <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-slate-400">
                                                <x-icon name="eye" class="h-3.5 w-3.5" /> Hanya lihat
                                            </span>
                                        @else
                                            <form method="POST" action="{{ route('admin.users.password.reset', $user) }}"
                                                  onsubmit="return confirm('Buat password baru untuk {{ $user->username }}? Password lama tidak akan berlaku lagi.')">
                                                @csrf
                                                <button type="submit" class="btn-white btn-sm" title="Buat password baru">
                                                    <x-icon name="refresh" class="h-3.5 w-3.5" /> Buat Password Baru
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 px-4 py-3">
                    <p class="text-[11px] text-slate-400">
                        Menampilkan {{ $users->firstItem() }}–{{ $users->lastItem() }} dari {{ $users->total() }} pengguna
                    </p>
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
