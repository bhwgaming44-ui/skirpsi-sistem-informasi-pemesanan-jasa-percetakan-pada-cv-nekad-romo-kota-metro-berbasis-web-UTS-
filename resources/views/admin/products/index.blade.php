@extends('layouts.dashboard')

@section('title', 'Master Produk')

@section('content')
    @php
        $satuanLabel = fn (?string $satuan) => match ($satuan) {
            'meter' => 'm²',
            'lembar' => 'lembar',
            'pcs' => 'pcs',
            'rim' => 'rim',
            default => $satuan,
        };
    @endphp

    <div class="space-y-5">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="font-display text-xl font-extrabold text-slate-900">Master Produk</h2>
                <p class="mt-0.5 text-sm text-slate-500">Kelola layanan cetak, bahan, harga, dan status tampil di katalog.</p>
            </div>
            <a href="{{ route('admin.products.create') }}" class="btn-primary btn-sm">
                <x-icon name="plus" class="h-4 w-4" /> Tambah produk
            </a>
        </div>

        <form method="GET" action="{{ route('admin.products.index') }}" class="card flex flex-col gap-3 p-4 lg:flex-row lg:items-center">
            <div class="relative flex-1">
                <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400">
                    <x-icon name="search" class="h-4.5 w-4.5" />
                </span>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama produk..." class="input !pl-10">
            </div>

            <select name="kategori" class="select lg:!w-52" onchange="this.form.submit()">
                <option value="">Semua kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id_kategori }}" @selected((string) request('kategori') === (string) $category->id_kategori)>
                        {{ $category->nama_kategori }}
                    </option>
                @endforeach
            </select>

            <select name="status" class="select lg:!w-40" onchange="this.form.submit()">
                <option value="">Semua status</option>
                <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
                <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
            </select>

            <div class="flex gap-2">
                <button type="submit" class="btn-primary btn-sm !px-4">
                    <x-icon name="search" class="h-4 w-4" /> Filter
                </button>
                @if (request()->hasAny(['q', 'kategori', 'status']))
                    <a href="{{ route('admin.products.index') }}" class="btn-white btn-sm">Reset</a>
                @endif
            </div>
        </form>

        @if ($products->isEmpty())
            <x-empty-state icon="package" title="Produk tidak ditemukan"
                           message="Coba ubah kata kunci/filter, atau tambahkan produk baru ke katalog.">
                <a href="{{ route('admin.products.create') }}" class="btn-primary btn-sm mt-1">
                    <x-icon name="plus" class="h-4 w-4" /> Tambah produk
                </a>
            </x-empty-state>
        @else
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($products as $product)
                    <article class="card overflow-hidden">
                        <div class="relative aspect-[16/9] overflow-hidden bg-slate-100">
                            <img src="{{ $product->gambarUrl() }}" alt="{{ $product->nama_produk }}" class="h-full w-full object-cover">

                            <div class="absolute left-3 top-3 flex flex-wrap gap-1.5">
                                <span class="badge {{ $product->is_active ? 'bg-emerald-500 text-white' : 'bg-slate-800/80 text-white' }}">
                                    {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                                @if ($product->is_featured)
                                    <span class="badge bg-amber-400 text-amber-950">
                                        <x-icon name="star" class="h-3 w-3" /> Unggulan
                                    </span>
                                @endif
                            </div>

                            @if ($product->galeri_count > 0)
                                <span class="absolute bottom-3 left-3 badge bg-white/90 text-slate-700 backdrop-blur">
                                    <x-icon name="image" class="h-3 w-3" /> {{ $product->galeri_count }} foto
                                </span>
                            @endif

                            <span class="absolute bottom-3 right-3 badge bg-white/90 text-slate-700 backdrop-blur">
                                {{ $product->kategori->nama_kategori }}
                            </span>
                        </div>

                        <div class="p-4">
                            <h3 class="line-clamp-1 text-sm font-bold text-slate-900">{{ $product->nama_produk }}</h3>
                            <p class="mt-0.5 line-clamp-2 min-h-8 text-[11px] leading-relaxed text-slate-400">
                                {{ \Illuminate\Support\Str::limit(strip_tags($product->deskripsi), 90) ?: 'Belum ada deskripsi produk.' }}
                            </p>

                            <div class="mt-3 grid grid-cols-3 gap-1.5 text-center">
                                <div class="rounded-lg bg-slate-50 px-1.5 py-2">
                                    <p class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Harga mulai</p>
                                    <p class="mt-0.5 text-[11px] font-bold text-slate-800">Rp {{ number_format($product->hargaMulai(), 0, ',', '.') }}</p>
                                </div>
                                <div class="rounded-lg bg-slate-50 px-1.5 py-2">
                                    <p class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Bahan</p>
                                    <p class="mt-0.5 text-[11px] font-bold text-slate-800">{{ $product->bahan_count }} jenis</p>
                                </div>
                                <div class="rounded-lg bg-slate-50 px-1.5 py-2">
                                    <p class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Estimasi</p>
                                    <p class="mt-0.5 text-[11px] font-bold text-slate-800">{{ $product->estimasi_hari }} hari</p>
                                </div>
                            </div>

                            <div class="mt-3 flex items-center justify-between gap-2 border-t border-dashed border-slate-200 pt-3">
                                <span class="text-[10px] font-semibold text-slate-400">
                                    Satuan {{ $satuanLabel($product->satuan) }}
                                </span>
                                <div class="flex items-center gap-1">
                                    <a href="{{ route('catalog.show', $product) }}" target="_blank"
                                       class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700" title="Lihat di katalog">
                                        <x-icon name="eye" class="h-4 w-4" />
                                    </a>
                                    <a href="{{ route('admin.products.edit', $product) }}"
                                       class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-brand-50 hover:text-brand-600" title="Edit produk">
                                        <x-icon name="edit" class="h-4 w-4" />
                                    </a>
                                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}"
                                          data-confirm="Produk {{ $product->nama_produk }} akan dihapus. Jika sudah pernah dipesan, produk hanya dinonaktifkan."
                                          data-confirm-button="Ya, hapus" data-confirm-title="Hapus produk?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-rose-50 hover:text-rose-600" title="Hapus produk">
                                            <x-icon name="trash" class="h-4 w-4" />
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            @if ($products->hasPages())
                <div>{{ $products->links() }}</div>
            @endif
        @endif
    </div>
@endsection
