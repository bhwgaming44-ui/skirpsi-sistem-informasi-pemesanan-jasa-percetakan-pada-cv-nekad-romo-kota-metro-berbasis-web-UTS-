@extends('layouts.dashboard')

@section('title', 'Pengaturan Sistem')

@section('content')

    <div class="space-y-5" x-data="{ tab: 'toko' }">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="font-display text-xl font-extrabold text-slate-900">Pengaturan Sistem</h2>
                <p class="mt-0.5 text-sm text-slate-500">Kelola profil toko dan kontak publik. Konfigurasi pembayaran ada di menu Pembayaran.</p>
            </div>
            <a href="{{ route('home') }}" target="_blank" class="btn-white btn-sm">
                <x-icon name="eye" class="h-4 w-4" /> Lihat situs
            </a>
        </div>

        {{-- Tabs --}}
        <div class="card flex flex-wrap items-center gap-1.5 p-1.5">
            <button type="button" @click="tab = 'toko'"
                    class="flex flex-1 items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold transition sm:flex-none"
                    :class="tab === 'toko' ? 'bg-gradient-to-r from-brand-600 to-indigo-600 text-white shadow-md shadow-brand-500/25' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-800'">
                <x-icon name="printer" class="h-4 w-4" /> Profil Toko
            </button>
            <a href="{{ route('admin.payments.index') }}"
               class="flex flex-1 items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-500 transition hover:bg-slate-100 hover:text-slate-800 sm:flex-none">
                <x-icon name="credit-card" class="h-4 w-4" /> Pembayaran Midtrans
                <x-icon name="arrow-right" class="h-3.5 w-3.5" />
            </a>
        </div>

        {{-- ================= STORE ================= --}}
        <form method="POST" action="{{ route('admin.settings.store') }}" enctype="multipart/form-data"
              x-show="tab === 'toko'" x-transition.opacity class="grid gap-5 xl:grid-cols-[1.6fr_1fr]">
            @csrf
            @method('PUT')

            <div class="space-y-5">
                <div class="card p-5 sm:p-6">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                            <x-icon name="printer" class="h-4.5 w-4.5" />
                        </span>
                        <div>
                            <h3 class="font-display text-base font-bold text-slate-900">Identitas toko</h3>
                            <p class="text-xs text-slate-400">Informasi ini tampil di header, footer, dan invoice.</p>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="nama_perusahaan" class="label">Nama Perusahaan</label>
                            <input id="nama_perusahaan" name="nama_perusahaan" type="text" required maxlength="150"
                                   value="{{ old('nama_perusahaan', $store->nama_perusahaan) }}" class="input">
                            <x-form-error name="nama_perusahaan" />
                        </div>

                        <div>
                            <label for="no_telepon" class="label">Nomor Telepon / WhatsApp</label>
                            <input id="no_telepon" name="no_telepon" type="text" required maxlength="20"
                                   value="{{ old('no_telepon', $store->no_telepon) }}" class="input">
                            <x-form-error name="no_telepon" />
                        </div>

                        <div>
                            <label for="email_kontak" class="label">Email Kontak</label>
                            <input id="email_kontak" name="email_kontak" type="email" maxlength="100"
                                   value="{{ old('email_kontak', $store->email_kontak) }}" class="input">
                            <x-form-error name="email_kontak" />
                        </div>

                        <div class="sm:col-span-2">
                            <label for="alamat" class="label">Alamat Toko</label>
                            <textarea id="alamat" name="alamat" rows="2" required maxlength="500" class="textarea">{{ old('alamat', $store->alamat) }}</textarea>
                            <x-form-error name="alamat" />
                        </div>

                        <div>
                            <label for="jam_operasional" class="label">Jam Operasional</label>
                            <input id="jam_operasional" name="jam_operasional" type="text" required maxlength="100"
                                   value="{{ old('jam_operasional', $store->jam_operasional) }}" class="input">
                            <x-form-error name="jam_operasional" />
                        </div>

                        <div>
                            <label for="instagram" class="label">Instagram</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">@</span>
                                <input id="instagram" name="instagram" type="text" maxlength="100"
                                       value="{{ old('instagram', $store->instagram) }}" placeholder="nekadromo.print" class="input !pl-9">
                            </div>
                            <x-form-error name="instagram" />
                        </div>

                        <div class="sm:col-span-2">
                            <label for="deskripsi" class="label">Deskripsi Singkat</label>
                            <textarea id="deskripsi" name="deskripsi" rows="3" maxlength="1000"
                                      placeholder="Tampil di footer situs."
                                      class="textarea">{{ old('deskripsi', $store->deskripsi) }}</textarea>
                            <x-form-error name="deskripsi" />
                        </div>
                    </div>
                </div>

                <div class="card p-5 sm:p-6">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                            <x-icon name="truck" class="h-4.5 w-4.5" />
                        </span>
                        <div>
                            <h3 class="font-display text-base font-bold text-slate-900">Pengiriman</h3>
                            <p class="text-xs text-slate-400">Biaya kirim default saat pelanggan memilih kurir.</p>
                        </div>
                    </div>

                    <div class="mt-4 max-w-xs">
                        <label for="biaya_kirim" class="label">Biaya Kirim (Rp)</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">Rp</span>
                            <input id="biaya_kirim" name="biaya_kirim" type="number" min="0" max="1000000" step="500" required
                                   value="{{ old('biaya_kirim', (int) $store->biaya_kirim) }}" class="input !pl-10">
                        </div>
                        <x-form-error name="biaya_kirim" />
                    </div>
                </div>
            </div>

            {{-- Logo & submit --}}
            <aside class="space-y-4 xl:sticky xl:top-24 xl:self-start">
                <div class="card p-5">
                    <h3 class="font-display text-base font-bold text-slate-900">Logo toko</h3>
                    <p class="mt-0.5 text-xs text-slate-400">PNG/JPG/SVG, maks. 2 MB. Rasio ideal 1:1.</p>

                    <div class="mt-3 flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50/60 p-3">
                        <img src="{{ $store->logo ? asset('images/'.$store->logo) : asset('images/logo.svg') }}"
                             alt="Logo toko" class="h-16 w-16 rounded-xl bg-white object-contain p-1 shadow-sm">
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold text-slate-800">{{ $store->logo ?: 'logo.svg' }}</p>
                            <p class="text-[10px] text-slate-400">Digunakan di sidebar & invoice.</p>
                        </div>
                    </div>

                    <input type="file" name="logo" accept="image/jpeg,image/png,image/webp,image/svg+xml"
                           class="mt-3 w-full cursor-pointer rounded-xl border border-slate-200 bg-white px-3 py-2 text-[11px] file:mr-2 file:rounded-lg file:border-0 file:bg-brand-50 file:px-2.5 file:py-1.5 file:text-[11px] file:font-bold file:text-brand-700">
                    <x-form-error name="logo" />

                    <button type="submit" class="btn-primary mt-4 w-full">
                        <x-icon name="check-circle" class="h-4.5 w-4.5" /> Simpan Profil Toko
                    </button>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <h3 class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-500">
                        <x-icon name="info" class="h-4 w-4 text-brand-500" /> Pratinjau kontak
                    </h3>
                    <div class="mt-3 space-y-2 text-[11px] text-slate-600">
                        <p class="flex items-start gap-2"><x-icon name="map-pin" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-brand-500" /> {{ $store->alamat }}</p>
                        <p class="flex items-center gap-2"><x-icon name="phone" class="h-3.5 w-3.5 shrink-0 text-brand-500" /> {{ $store->no_telepon }}</p>
                        <p class="flex items-center gap-2"><x-icon name="mail" class="h-3.5 w-3.5 shrink-0 text-brand-500" /> {{ $store->email_kontak ?: '-' }}</p>
                        <p class="flex items-center gap-2"><x-icon name="clock" class="h-3.5 w-3.5 shrink-0 text-brand-500" /> {{ $store->jam_operasional }}</p>
                    </div>
                </div>
            </aside>
        </form>

    </div>
@endsection
