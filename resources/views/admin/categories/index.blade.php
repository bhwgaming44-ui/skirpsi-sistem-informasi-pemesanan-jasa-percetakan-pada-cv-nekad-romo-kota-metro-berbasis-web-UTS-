@extends('layouts.dashboard')

@section('title', 'Kategori Produk')

@section('content')
    @php
        $iconOptions = [
            'banner' => 'Banner / Spanduk',
            'document' => 'Dokumen / Undangan',
            'sparkles' => 'Merchandise',
            'archive' => 'Kemasan / Paper Bag',
            'printer' => 'Digital Printing',
            'palette' => 'Desain Grafis',
            'tag' => 'Label & Stiker',
            'layers' => 'Lainnya',
        ];
    @endphp

    <div class="space-y-5" x-data="{
        open: false,
        editId: null,
        form: { nama_kategori: '', deskripsi: '', ikon: 'banner' },
        create() {
            this.editId = null;
            this.form = { nama_kategori: '', deskripsi: '', ikon: 'banner' };
            this.open = true;
        },
        edit(id, nama, deskripsi, ikon) {
            this.editId = id;
            this.form = { nama_kategori: nama, deskripsi: deskripsi ?? '', ikon: ikon ?? 'banner' };
            this.open = true;
        },
    }">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="font-display text-xl font-extrabold text-slate-900">Kategori Produk</h2>
                <p class="mt-0.5 text-sm text-slate-500">Kelompokkan layanan cetak agar mudah ditemukan pelanggan.</p>
            </div>
            <button type="button" @click="create()" class="btn-primary btn-sm">
                <x-icon name="plus" class="h-4 w-4" /> Tambah kategori
            </button>
        </div>

        <div class="card overflow-hidden">
            @if ($categories->isEmpty())
                <div class="p-5">
                    <x-empty-state icon="layers" title="Belum ada kategori" message="Tambahkan kategori pertama untuk mengelompokkan produk.">
                        <button type="button" @click="create()" class="btn-primary btn-sm mt-1">
                            <x-icon name="plus" class="h-4 w-4" /> Tambah kategori
                        </button>
                    </x-empty-state>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="table-modern min-w-[680px]">
                        <thead>
                            <tr>
                                <th class="w-14">Ikon</th>
                                <th>Nama Kategori</th>
                                <th>Deskripsi</th>
                                <th class="text-center">Produk</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($categories as $category)
                                <tr>
                                    <td>
                                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-indigo-600 text-white shadow-sm">
                                            <x-icon :name="$category->ikon ?: 'layers'" class="h-5 w-5" />
                                        </span>
                                    </td>
                                    <td>
                                        <p class="text-sm font-bold text-slate-800">{{ $category->nama_kategori }}</p>
                                        <p class="text-[10px] text-slate-400">#{{ $category->id_kategori }}</p>
                                    </td>
                                    <td class="max-w-md">
                                        <p class="line-clamp-2 text-xs leading-relaxed text-slate-500">{{ $category->deskripsi ?: '—' }}</p>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $category->produk_count > 0 ? 'bg-brand-50 text-brand-700' : 'bg-slate-100 text-slate-500' }}">
                                            {{ $category->produk_count }} produk
                                        </span>
                                    </td>
                                    <td>
                                        <div class="flex items-center justify-end gap-1">
                                            <button type="button"
                                                    @click="edit({{ $category->id_kategori }}, @js($category->nama_kategori), @js($category->deskripsi), @js($category->ikon))"
                                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-brand-50 hover:text-brand-600" title="Edit">
                                                <x-icon name="edit" class="h-4 w-4" />
                                            </button>
                                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                                                  data-confirm="Kategori {{ $category->nama_kategori }} akan dihapus permanen."
                                                  data-confirm-button="Ya, hapus" data-confirm-title="Hapus kategori?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-rose-50 hover:text-rose-600" title="Hapus">
                                                    <x-icon name="trash" class="h-4 w-4" />
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        @if ($categories->hasPages())
            <div>{{ $categories->links() }}</div>
        @endif

        {{-- Modal --}}
        <div x-cloak x-show="open" class="fixed inset-0 z-[60] flex items-center justify-center p-4" role="dialog" aria-modal="true">
            <div x-show="open" x-transition.opacity class="absolute inset-0 bg-ink-950/60 backdrop-blur-sm" @click="open = false"></div>

            <div x-show="open" x-transition.scale.origin.center
                 class="relative w-full max-w-lg overflow-hidden rounded-3xl border border-white/60 bg-white shadow-2xl">
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 bg-slate-50/70 px-6 py-4">
                    <div>
                        <h3 class="font-display text-base font-bold text-slate-900" x-text="editId ? 'Edit kategori' : 'Tambah kategori'">Tambah kategori</h3>
                        <p class="text-[11px] text-slate-400">Nama kategori akan tampil di katalog dan filter.</p>
                    </div>
                    <button type="button" @click="open = false" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-white hover:text-slate-700">
                        <x-icon name="close" class="h-4.5 w-4.5" />
                    </button>
                </div>

                <form method="POST" :action="editId ? '{{ url('admin/kategori') }}/' + editId : '{{ route('admin.categories.store') }}'">
                    @csrf
                    <input type="hidden" name="_method" :value="editId ? 'PUT' : 'POST'">

                    <div class="space-y-4 p-6">
                        <div>
                            <label for="nama_kategori" class="label">Nama Kategori</label>
                            <input id="nama_kategori" name="nama_kategori" type="text" x-model="form.nama_kategori" required maxlength="100"
                                   placeholder="Contoh: Digital Printing" class="input">
                        </div>

                        <div>
                            <label for="deskripsi" class="label">Deskripsi</label>
                            <textarea id="deskripsi" name="deskripsi" rows="3" x-model="form.deskripsi" maxlength="1000"
                                      placeholder="Penjelasan singkat kategori ini" class="textarea"></textarea>
                        </div>

                        <div>
                            <label for="ikon" class="label">Ikon</label>
                            <select id="ikon" name="ikon" x-model="form.ikon" class="select">
                                @foreach ($iconOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 border-t border-slate-100 bg-slate-50/70 px-6 py-4">
                        <button type="button" @click="open = false" class="btn-white btn-sm">Batal</button>
                        <button type="submit" class="btn-primary btn-sm">
                            <x-icon name="check" class="h-4 w-4" />
                            <span x-text="editId ? 'Simpan perubahan' : 'Tambah kategori'">Tambah kategori</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
