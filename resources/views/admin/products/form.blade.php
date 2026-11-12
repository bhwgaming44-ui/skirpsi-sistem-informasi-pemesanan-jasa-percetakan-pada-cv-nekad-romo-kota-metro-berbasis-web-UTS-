@extends('layouts.dashboard')

@section('title', $product->exists ? 'Edit Produk' : 'Tambah Produk')

@section('content')
    @php
        $isEdit = $product->exists;
        $satuanOptions = [
            'meter' => 'Meter persegi (m²) — banner, spanduk, vinyl',
            'lembar' => 'Lembar — brosur, undangan, kartu nama',
            'pcs' => 'Pcs — kaos, mug, paper bag, nota',
            'rim' => 'Rim — kertas cetak massal',
        ];

        $materialRows = old('bahan');

        if ($materialRows === null) {
            $materialRows = $product->bahan
                ->map(fn ($bahan) => [
                    'id_bahan' => $bahan->id_bahan,
                    'nama_bahan' => $bahan->nama_bahan,
                    'harga_satuan' => (float) $bahan->harga_satuan,
                ])
                ->values()
                ->all();
        }

        if (count($materialRows) === 0) {
            $materialRows = [['id_bahan' => null, 'nama_bahan' => '', 'harga_satuan' => 0]];
        }
    @endphp

    <div class="space-y-5">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <a href="{{ route('admin.products.index') }}" class="inline-flex items-center gap-1 text-[11px] font-bold text-slate-400 transition hover:text-brand-600">
                    <x-icon name="arrow-left" class="h-3.5 w-3.5" /> Kembali ke daftar produk
                </a>
                <h2 class="mt-1 font-display text-xl font-extrabold text-slate-900">
                    {{ $isEdit ? 'Edit Produk' : 'Tambah Produk Baru' }}
                </h2>
                <p class="mt-0.5 text-sm text-slate-500">
                    {{ $isEdit ? 'Perbarui informasi produk "'.$product->nama_produk.'".' : 'Lengkapi data produk, bahan, dan finishing yang tersedia.' }}
                </p>
            </div>
            @if ($isEdit)
                <a href="{{ route('catalog.show', $product) }}" target="_blank" class="btn-white btn-sm">
                    <x-icon name="eye" class="h-4 w-4" /> Lihat di katalog
                </a>
            @endif
        </div>

        <form method="POST"
              action="{{ $isEdit ? route('admin.products.update', $product) : route('admin.products.store') }}"
              enctype="multipart/form-data"
              x-data="{
                  rows: @js($materialRows),
                  addRow() {
                      this.rows.push({ id_bahan: null, nama_bahan: '', harga_satuan: 0 });
                  },
                  removeRow(index) {
                      if (this.rows.length > 1) {
                          this.rows.splice(index, 1);
                      }
                  },
                  existingGallery: @js($galeri->map(fn ($image) => ['id' => $image->id_gambar, 'url' => $image->url()])->values()),
                  removedGallery: [],
                  newGallery: [],
                  primaryPreview: @js($product->gambar ? $product->gambarUrl() : null),
                  hasPrimary: @js((bool) $product->gambar),
                  primaryRemoved: false,
                  draggingPrimary: false,
                  draggingGallery: false,
                  onPrimaryChange(event) {
                      const file = event.target.files[0];
                      if (! file) {
                          return;
                      }
                      this.primaryPreview = URL.createObjectURL(file);
                      this.hasPrimary = true;
                      this.primaryRemoved = false;
                  },
                  onPrimaryDrop(event) {
                      const files = event.dataTransfer?.files;
                      if (! files || files.length === 0) {
                          return;
                      }
                      this.$refs.primaryInput.files = files;
                      this.onPrimaryChange({ target: this.$refs.primaryInput });
                  },
                  removePrimary() {
                      this.$refs.primaryInput.value = '';
                      this.primaryPreview = null;
                      this.hasPrimary = false;
                      this.primaryRemoved = true;
                  },
                  buildGalleryPreviews() {
                      this.newGallery.forEach((item) => URL.revokeObjectURL(item.url));
                      this.newGallery = Array.from(this.$refs.galleryInput.files).map((file) => ({
                          name: file.name,
                          url: URL.createObjectURL(file),
                      }));
                  },
                  addGalleryFiles(files) {
                      const data = new DataTransfer();
                      Array.from(this.$refs.galleryInput.files).forEach((file) => data.items.add(file));
                      Array.from(files).forEach((file) => {
                          if (file.type.startsWith('image/')) {
                              data.items.add(file);
                          }
                      });
                      this.$refs.galleryInput.files = data.files;
                      this.buildGalleryPreviews();
                  },
                  onGalleryDrop(event) {
                      const files = event.dataTransfer?.files;
                      if (! files || files.length === 0) {
                          return;
                      }
                      this.addGalleryFiles(files);
                  },
                  removeNewGallery(index) {
                      const data = new DataTransfer();
                      const files = Array.from(this.$refs.galleryInput.files);
                      files.splice(index, 1).forEach((file) => data.items.add(file));
                      this.$refs.galleryInput.files = data.files;
                      this.buildGalleryPreviews();
                  },
                  removeExistingGallery(index) {
                      const image = this.existingGallery[index];
                      if (! image) {
                          return;
                      }
                      this.removedGallery.push(image.id);
                      this.existingGallery.splice(index, 1);
                  },
              }">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif

            <div class="grid gap-5 xl:grid-cols-[1.6fr_1fr]">
                <div class="space-y-5">
                    {{-- Informasi utama --}}
                    <div class="card p-5 sm:p-6">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                                <x-icon name="package" class="h-4.5 w-4.5" />
                            </span>
                            <div>
                                <h3 class="font-display text-base font-bold text-slate-900">Informasi produk</h3>
                                <p class="text-xs text-slate-400">Data utama yang tampil di katalog.</p>
                            </div>
                        </div>

                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label for="nama_produk" class="label">Nama Produk</label>
                                <input id="nama_produk" name="nama_produk" type="text" required maxlength="150"
                                       value="{{ old('nama_produk', $product->nama_produk) }}"
                                       placeholder="Contoh: Banner Flexi Outdoor"
                                       class="input @error('nama_produk') !border-rose-300 !ring-rose-100 @enderror">
                                <x-form-error name="nama_produk" />
                            </div>

                            <div>
                                <label for="id_kategori" class="label">Kategori</label>
                                <select id="id_kategori" name="id_kategori" required class="select @error('id_kategori') !border-rose-300 @enderror">
                                    <option value="">Pilih kategori</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id_kategori }}" @selected((string) old('id_kategori', $product->id_kategori) === (string) $category->id_kategori)>
                                            {{ $category->nama_kategori }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-form-error name="id_kategori" />
                            </div>

                            <div>
                                <label for="satuan" class="label">Satuan Hitung</label>
                                <select id="satuan" name="satuan" required class="select @error('satuan') !border-rose-300 @enderror">
                                    @foreach ($satuanOptions as $value => $label)
                                        <option value="{{ $value }}" @selected(old('satuan', $product->satuan) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <x-form-error name="satuan" />
                            </div>

                            <div>
                                <label for="min_order" class="label">Minimal Order</label>
                                <input id="min_order" name="min_order" type="number" min="1" max="10000" required
                                       value="{{ old('min_order', $product->min_order) }}"
                                       class="input @error('min_order') !border-rose-300 !ring-rose-100 @enderror">
                                <x-form-error name="min_order" />
                            </div>

                            <div>
                                <label for="estimasi_hari" class="label">Estimasi Pengerjaan (hari)</label>
                                <input id="estimasi_hari" name="estimasi_hari" type="number" min="1" max="30" required
                                       value="{{ old('estimasi_hari', $product->estimasi_hari) }}"
                                       class="input @error('estimasi_hari') !border-rose-300 !ring-rose-100 @enderror">
                                <x-form-error name="estimasi_hari" />
                            </div>

                            <div class="sm:col-span-2">
                                <label for="deskripsi" class="label">Deskripsi</label>
                                <textarea id="deskripsi" name="deskripsi" rows="4" maxlength="2000"
                                          placeholder="Jelaskan keunggulan, penggunaan, dan spesifikasi produk."
                                          class="textarea @error('deskripsi') !border-rose-300 !ring-rose-100 @enderror">{{ old('deskripsi', $product->deskripsi) }}</textarea>
                                <x-form-error name="deskripsi" />
                            </div>
                        </div>
                    </div>

                    {{-- Bahan & harga --}}
                    <div class="card p-5 sm:p-6">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5">
                                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                                    <x-icon name="layers" class="h-4.5 w-4.5" />
                                </span>
                                <div>
                                    <h3 class="font-display text-base font-bold text-slate-900">Bahan & harga</h3>
                                    <p class="text-xs text-slate-400">Minimal satu bahan. Harga per satuan hitung.</p>
                                </div>
                            </div>
                            <button type="button" @click="addRow()" class="btn-white btn-sm">
                                <x-icon name="plus" class="h-4 w-4" /> Tambah bahan
                            </button>
                        </div>

                        <x-form-error name="bahan" />

                        <div class="mt-4 space-y-2.5">
                            <template x-for="(row, index) in rows" :key="index">
                                <div class="rounded-2xl border border-slate-200 bg-slate-50/60 p-3">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="badge bg-white text-slate-500 shadow-sm">
                                            Bahan <span x-text="index + 1"></span>
                                        </span>
                                        <button type="button" @click="removeRow(index)" x-show="rows.length > 1"
                                                class="inline-flex h-7 w-7 items-center justify-center rounded-lg text-slate-400 transition hover:bg-rose-50 hover:text-rose-600" title="Hapus bahan">
                                            <x-icon name="trash" class="h-3.5 w-3.5" />
                                        </button>
                                    </div>

                                    <input type="hidden" :name="`bahan[${index}][id_bahan]`" :value="row.id_bahan ?? ''">

                                    <div class="mt-2.5 grid gap-2.5 sm:grid-cols-[1.4fr_1fr]">
                                        <div>
                                            <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-slate-400">Nama bahan</label>
                                            <input type="text" :name="`bahan[${index}][nama_bahan]`" x-model="row.nama_bahan" required
                                                   maxlength="100" placeholder="Contoh: Flexi China 280gr"
                                                   class="input !py-2.5 !text-xs">
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-slate-400">Harga / satuan (Rp)</label>
                                            <input type="number" :name="`bahan[${index}][harga_satuan]`" x-model.number="row.harga_satuan" required
                                                   min="0" step="100" class="input !py-2.5 !text-xs">
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Finishing --}}
                    <div class="card p-5 sm:p-6">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                                <x-icon name="scissors" class="h-4.5 w-4.5" />
                            </span>
                            <div>
                                <h3 class="font-display text-base font-bold text-slate-900">Finishing tersedia</h3>
                                <p class="text-xs text-slate-400">Pilih opsi finishing yang bisa dipilih pelanggan.</p>
                            </div>
                        </div>

                        @if ($finishings->isEmpty())
                            <p class="mt-4 rounded-xl bg-amber-50 px-4 py-3 text-xs text-amber-700">
                                Belum ada master finishing.
                                <a href="{{ route('admin.finishings.index') }}" class="font-bold underline">Tambahkan dulu</a>.
                            </p>
                        @else
                            <div class="mt-4 grid gap-2 sm:grid-cols-2">
                                @foreach ($finishings as $finishing)
                                    <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 transition hover:border-brand-300 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50/60">
                                        <input type="checkbox" name="finishing_ids[]" value="{{ $finishing->id_finishing }}"
                                               @checked(in_array($finishing->id_finishing, old('finishing_ids', $selectedFinishing)))
                                               class="h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-200">
                                        <span class="min-w-0 flex-1">
                                            <span class="block truncate text-xs font-bold text-slate-800">{{ $finishing->nama_finishing }}</span>
                                            <span class="block text-[10px] font-semibold text-brand-600">+ Rp {{ number_format((float) $finishing->biaya_default, 0, ',', '.') }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                        <x-form-error name="finishing_ids" />
                    </div>
                </div>

                {{-- Sidebar --}}
                <aside class="space-y-4 xl:sticky xl:top-24 xl:self-start">
                    <div class="card p-5">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h3 class="font-display text-base font-bold text-slate-900">Gambar produk</h3>
                                <p class="mt-0.5 text-xs text-slate-400">JPG/PNG/WEBP, maks. 4 MB per file. Rasio ideal 4:3.</p>
                            </div>
                            <span class="badge bg-brand-50 text-brand-700">
                                <x-icon name="image" class="h-3.5 w-3.5" /> <span x-text="(hasPrimary ? 1 : 0) + existingGallery.length + newGallery.length"></span>
                            </span>
                        </div>

                        <input type="hidden" name="hapus_gambar" :value="primaryRemoved ? '1' : '0'">

                        <label class="mt-3 block cursor-pointer"
                               @dragover.prevent="draggingPrimary = true"
                               @dragleave.prevent="draggingPrimary = false"
                               @drop.prevent="draggingPrimary = false; onPrimaryDrop($event)">
                            <input x-ref="primaryInput" type="file" name="gambar" accept="image/jpeg,image/png,image/webp"
                                   class="sr-only" @change="onPrimaryChange($event)">
                            <div class="relative aspect-[4/3] overflow-hidden rounded-2xl border-2 border-dashed transition"
                                 :class="draggingPrimary ? 'border-brand-500 bg-brand-50' : 'border-slate-300 bg-slate-50'">
                                <template x-if="primaryPreview">
                                    <img :src="primaryPreview" alt="Pratinjau gambar utama" class="h-full w-full object-cover">
                                </template>

                                <template x-if="! primaryPreview">
                                    <div class="flex h-full w-full flex-col items-center justify-center gap-2 px-4 text-center">
                                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm">
                                            <x-icon name="image" class="h-6 w-6" />
                                        </span>
                                        <span class="text-[11px] font-semibold text-slate-500">Belum ada gambar utama</span>
                                        <span class="text-[10px] text-slate-400">Tarik file ke sini atau klik untuk memilih</span>
                                    </div>
                                </template>

                                <span class="absolute inset-x-2 bottom-2 flex items-center justify-center gap-1.5 rounded-xl bg-ink-950/70 px-3 py-2 text-[11px] font-bold text-white backdrop-blur">
                                    <x-icon name="upload" class="h-3.5 w-3.5" />
                                    <span x-text="draggingPrimary ? 'Lepaskan gambar di sini' : 'Klik atau tarik gambar utama'">Klik atau tarik gambar utama</span>
                                </span>
                            </div>
                        </label>

                        <div class="mt-2 flex items-center justify-between gap-2">
                            <p class="text-[10px] text-slate-400">Gambar utama tampil di kartu katalog.</p>
                            <button type="button" x-show="hasPrimary" @click="removePrimary()"
                                    class="inline-flex items-center gap-1 text-[10px] font-bold text-rose-500 transition hover:text-rose-700">
                                <x-icon name="trash" class="h-3 w-3" /> Hapus
                            </button>
                        </div>
                        <x-form-error name="gambar" />

                        <div class="mt-4 border-t border-dashed border-slate-200 pt-4">
                            <div class="flex items-center justify-between gap-2">
                                <div>
                                    <h4 class="text-xs font-bold text-slate-800">Galeri foto</h4>
                                    <p class="text-[10px] text-slate-400">Maks. 6 gambar sekaligus, bisa lebih dari sekali simpan.</p>
                                </div>
                                <span class="badge bg-slate-100 text-slate-500" x-text="existingGallery.length + newGallery.length + ' foto'"></span>
                            </div>

                            <template x-for="id in removedGallery" :key="id">
                                <input type="hidden" name="hapus_galeri[]" :value="id">
                            </template>

                            <div class="mt-3 grid grid-cols-3 gap-2" x-show="existingGallery.length > 0">
                                <template x-for="(image, index) in existingGallery" :key="image.id">
                                    <div class="group relative aspect-square overflow-hidden rounded-xl border border-slate-200 bg-slate-100">
                                        <img :src="image.url" alt="Galeri produk" class="h-full w-full object-cover">
                                        <button type="button" @click="removeExistingGallery(index)"
                                                class="absolute right-1 top-1 flex h-6 w-6 items-center justify-center rounded-lg bg-ink-950/70 text-white opacity-0 backdrop-blur transition group-hover:opacity-100"
                                                title="Hapus dari galeri">
                                            <x-icon name="close" class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                </template>
                            </div>

                            <label class="mt-3 block cursor-pointer"
                                   @dragover.prevent="draggingGallery = true"
                                   @dragleave.prevent="draggingGallery = false"
                                   @drop.prevent="draggingGallery = false; onGalleryDrop($event)">
                                <input x-ref="galleryInput" type="file" name="galeri[]" multiple
                                       accept="image/jpeg,image/png,image/webp" class="sr-only"
                                       @change="buildGalleryPreviews()">
                                <div class="flex flex-col items-center justify-center gap-1.5 rounded-xl border-2 border-dashed px-3 py-4 text-center transition"
                                     :class="draggingGallery ? 'border-brand-500 bg-brand-50' : 'border-slate-300 bg-slate-50 hover:border-brand-400'">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white text-brand-500 shadow-sm">
                                        <x-icon name="plus" class="h-4 w-4" />
                                    </span>
                                    <span class="text-[11px] font-bold text-slate-600" x-text="draggingGallery ? 'Lepaskan gambar galeri' : 'Tambah foto galeri'"></span>
                                    <span class="text-[10px] text-slate-400">Tarik beberapa file atau klik untuk memilih</span>
                                </div>
                            </label>

                            <div class="mt-3 grid grid-cols-3 gap-2" x-show="newGallery.length > 0">
                                <template x-for="(image, index) in newGallery" :key="image.url">
                                    <div class="group relative aspect-square overflow-hidden rounded-xl border-2 border-brand-300 bg-slate-100">
                                        <img :src="image.url" :alt="image.name" class="h-full w-full object-cover">
                                        <span class="absolute inset-x-0 bottom-0 truncate bg-ink-950/70 px-1.5 py-1 text-[9px] font-semibold text-white backdrop-blur" x-text="image.name"></span>
                                        <button type="button" @click="removeNewGallery(index)"
                                                class="absolute right-1 top-1 flex h-6 w-6 items-center justify-center rounded-lg bg-ink-950/70 text-white transition hover:bg-rose-600"
                                                title="Batalkan gambar ini">
                                            <x-icon name="close" class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                </template>
                            </div>

                            <x-form-error name="galeri" />
                            <x-form-error name="galeri.*" />
                        </div>
                    </div>

                    <div class="card p-5">
                        <h3 class="font-display text-base font-bold text-slate-900">Status tampil</h3>
                        <p class="mt-0.5 text-xs text-slate-400">Atur visibilitas dan penonjolan produk.</p>

                        <div class="mt-3 space-y-2.5">
                            <label class="flex cursor-pointer items-start gap-2.5 rounded-xl border border-slate-200 p-3 transition hover:border-brand-300">
                                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active ?? true))
                                       class="mt-0.5 h-4 w-4 rounded border-slate-300 text-brand-600 focus:ring-brand-200">
                                <span>
                                    <span class="block text-xs font-bold text-slate-800">Aktif di katalog</span>
                                    <span class="block text-[10px] text-slate-400">Produk dapat dilihat & dipesan pelanggan.</span>
                                </span>
                            </label>

                            <label class="flex cursor-pointer items-start gap-2.5 rounded-xl border border-slate-200 p-3 transition hover:border-brand-300">
                                <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured ?? false))
                                       class="mt-0.5 h-4 w-4 rounded border-slate-300 text-amber-500 focus:ring-amber-200">
                                <span>
                                    <span class="block text-xs font-bold text-slate-800">Tandai unggulan</span>
                                    <span class="block text-[10px] text-slate-400">Tampil di beranda sebagai produk favorit.</span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="card p-5">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-bold text-slate-500">Harga mulai</span>
                            <span class="font-display text-lg font-extrabold text-brand-600"
                                  x-text="rupiah(Math.min(...rows.map((row) => Number(row.harga_satuan) || 0)))">Rp 0</span>
                        </div>
                        <p class="mt-1 text-[10px] leading-relaxed text-slate-400">
                            Dihitung otomatis dari harga bahan terendah.
                        </p>

                        <div class="mt-4 space-y-2">
                            <button type="submit" class="btn-primary w-full">
                                <x-icon name="check-circle" class="h-4.5 w-4.5" /> {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Produk' }}
                            </button>
                            <a href="{{ route('admin.products.index') }}" class="btn-white w-full">Batal</a>
                        </div>
                    </div>
                </aside>
            </div>
        </form>
    </div>
@endsection
