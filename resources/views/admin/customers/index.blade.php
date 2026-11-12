@extends('layouts.dashboard')

@section('title', 'Data Pelanggan')

@section('content')
    <div class="space-y-5">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="font-display text-xl font-extrabold text-slate-900">Data Pelanggan</h2>
                <p class="mt-0.5 text-sm text-slate-500">Daftar akun pelanggan beserta riwayat belanja.</p>
            </div>
            <span class="badge bg-brand-50 text-brand-700">
                <x-icon name="users" class="h-3.5 w-3.5" /> {{ $customers->total() }} pelanggan
            </span>
        </div>

        <form method="GET" action="{{ route('admin.customers.index') }}" class="card flex flex-col gap-3 p-4 sm:flex-row sm:items-center">
            <div class="relative flex-1">
                <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                    <x-icon name="search" class="h-4.5 w-4.5" />
                </span>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama, username, email, atau no. HP..." class="input !pl-10">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-primary btn-sm !px-4">Cari</button>
                @if (request()->filled('q'))
                    <a href="{{ route('admin.customers.index') }}" class="btn-white btn-sm">Reset</a>
                @endif
            </div>
        </form>

        <div class="card overflow-hidden">
            @if ($customers->isEmpty())
                <div class="p-5">
                    <x-empty-state icon="users" title="Pelanggan tidak ditemukan"
                                   message="Belum ada akun pelanggan yang cocok dengan pencarian Anda." />
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="table-modern min-w-[820px]">
                        <thead>
                            <tr>
                                <th>Pelanggan</th>
                                <th>Kontak</th>
                                <th>Alamat</th>
                                <th class="text-center">Pesanan</th>
                                <th>Total Belanja</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($customers as $customer)
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-2.5">
                                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-accent-500 text-xs font-bold text-white">
                                                {{ strtoupper(substr($customer->nama, 0, 1)) }}
                                            </span>
                                            <div class="min-w-0 leading-tight">
                                                <p class="truncate text-xs font-bold text-slate-800">{{ $customer->nama }}</p>
                                                <p class="truncate text-[10px] text-slate-400">@ {{ $customer->username }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-[11px] font-semibold text-slate-700">{{ $customer->no_hp }}</p>
                                        <p class="text-[10px] text-slate-400">{{ $customer->email }}</p>
                                    </td>
                                    <td class="max-w-xs">
                                        <p class="line-clamp-2 text-[11px] leading-relaxed text-slate-500">{{ $customer->alamat ?: '—' }}</p>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $customer->pesanan_count > 0 ? 'bg-brand-50 text-brand-700' : 'bg-slate-100 text-slate-500' }}">
                                            {{ $customer->pesanan_count }}
                                        </span>
                                    </td>
                                    <td class="text-xs font-bold text-slate-800">
                                        Rp {{ number_format((int) ($customer->total_belanja ?? 0), 0, ',', '.') }}
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('admin.customers.show', $customer) }}"
                                           class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-brand-50 hover:text-brand-600" title="Lihat detail">
                                            <x-icon name="eye" class="h-4 w-4" />
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 px-4 py-3">
                    <p class="text-[11px] text-slate-400">
                        Menampilkan {{ $customers->firstItem() }}–{{ $customers->lastItem() }} dari {{ $customers->total() }} pelanggan
                    </p>
                    {{ $customers->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
