<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Finishing;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::query()
            ->with('kategori')
            ->withCount(['bahan', 'galeri'])
            ->orderByDesc('is_featured')
            ->orderBy('nama_produk');

        if ($request->filled('q')) {
            $query->where('nama_produk', 'like', '%'.$request->string('q')->trim().'%');
        }

        if ($request->filled('kategori')) {
            $query->where('id_kategori', $request->integer('kategori'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->string('status')->toString() === 'aktif');
        }

        return view('admin.products.index', [
            'products' => $query->paginate(10)->withQueryString(),
            'categories' => Category::query()->orderBy('nama_kategori')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.form', [
            'product' => new Product(['satuan' => 'meter', 'min_order' => 1, 'estimasi_hari' => 1, 'is_active' => true]),
            'galeri' => collect(),
            'categories' => Category::query()->orderBy('nama_kategori')->get(),
            'finishings' => Finishing::query()->orderBy('nama_finishing')->get(),
            'selectedFinishing' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        $product = DB::transaction(function () use ($request, $validated): Product {
            $data = Arr::except($validated, ['bahan', 'finishing_ids', 'gambar', 'galeri', 'hapus_galeri', 'hapus_gambar']);
            $data['is_featured'] = $request->boolean('is_featured');
            $data['is_active'] = $request->boolean('is_active');

            if ($request->hasFile('gambar')) {
                $data['gambar'] = $this->storePrimaryImage($request);
            }

            $product = Product::query()->create($data);
            $this->syncMaterials($product, $validated['bahan'] ?? []);
            $product->finishing()->sync($validated['finishing_ids'] ?? []);
            $this->storeGalleryImages($product, $request);

            return $product;
        });

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', 'Produk '.$product->nama_produk.' berhasil ditambahkan.');
    }

    public function edit(Product $product): View
    {
        $product->load([
            'bahan' => fn ($query) => $query->where('is_active', true)->orderBy('id_bahan'),
            'finishing',
            'galeri',
        ]);

        return view('admin.products.form', [
            'product' => $product,
            'galeri' => $product->galeri,
            'categories' => Category::query()->orderBy('nama_kategori')->get(),
            'finishings' => Finishing::query()->orderBy('nama_finishing')->get(),
            'selectedFinishing' => $product->finishing->pluck('id_finishing')->all(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $this->validated($request);

        DB::transaction(function () use ($request, $product, $validated): void {
            $data = Arr::except($validated, ['bahan', 'finishing_ids', 'gambar', 'galeri', 'hapus_galeri', 'hapus_gambar']);
            $data['is_featured'] = $request->boolean('is_featured');
            $data['is_active'] = $request->boolean('is_active');

            if ($request->hasFile('gambar')) {
                $this->deleteImage($product->gambar);
                $data['gambar'] = $this->storePrimaryImage($request);
            } elseif ($request->boolean('hapus_gambar')) {
                $this->deleteImage($product->gambar);
                $data['gambar'] = null;
            }

            $product->update($data);
            $this->syncMaterials($product, $validated['bahan'] ?? []);
            $product->finishing()->sync($validated['finishing_ids'] ?? []);
            $this->deleteSelectedGalleryImages($product, $validated['hapus_galeri'] ?? []);
            $this->storeGalleryImages($product, $request);
        });

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $hasOrders = OrderItem::query()
            ->whereHas('bahan', fn ($query) => $query->where('id_produk', $product->id_produk))
            ->exists();

        if ($hasOrders) {
            $product->update(['is_active' => false, 'is_featured' => false]);

            return back()->with('warning', 'Produk memiliki riwayat pesanan, jadi dinonaktifkan agar data transaksi tetap utuh.');
        }

        $product->galeri()->get()->each(function ($image): void {
            $this->deleteStoredFile('images/products/galeri/'.basename($image->file));
        });

        $this->deleteImage($product->gambar);
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'id_kategori' => ['required', 'integer', 'exists:kategori_produk,id_kategori'],
            'nama_produk' => ['required', 'string', 'max:150'],
            'satuan' => ['required', Rule::in(['meter', 'lembar', 'pcs', 'rim'])],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'galeri' => ['nullable', 'array', 'max:6'],
            'galeri.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'hapus_galeri' => ['nullable', 'array'],
            'hapus_galeri.*' => ['integer'],
            'hapus_gambar' => ['nullable', 'boolean'],
            'min_order' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'estimasi_hari' => ['required', 'integer', 'min:1', 'max:30'],
            'bahan' => ['required', 'array', 'min:1'],
            'bahan.*.id_bahan' => ['nullable', 'integer'],
            'bahan.*.nama_bahan' => ['required', 'string', 'max:100'],
            'bahan.*.harga_satuan' => ['required', 'numeric', 'min:0', 'max:100000000'],
            'finishing_ids' => ['nullable', 'array'],
            'finishing_ids.*' => ['integer', 'exists:finishing,id_finishing'],
        ], [
            'bahan.required' => 'Minimal satu bahan cetak dengan harga harus diisi.',
            'bahan.*.nama_bahan.required' => 'Nama bahan wajib diisi.',
            'bahan.*.harga_satuan.required' => 'Harga bahan wajib diisi.',
            'galeri.max' => 'Gambar galeri maksimal 6 file sekali unggah.',
            'galeri.*.image' => 'Setiap file galeri harus berupa gambar.',
            'galeri.*.mimes' => 'Format galeri harus JPG, PNG, atau WEBP.',
            'galeri.*.max' => 'Ukuran setiap gambar galeri maksimal 4 MB.',
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function syncMaterials(Product $product, array $rows): void
    {
        $keptIds = [];

        foreach ($rows as $row) {
            $existing = isset($row['id_bahan'])
                ? $product->bahan()->whereKey((int) $row['id_bahan'])->first()
                : null;

            if ($existing !== null) {
                $existing->update([
                    'nama_bahan' => $row['nama_bahan'],
                    'harga_satuan' => $row['harga_satuan'],
                    'is_active' => true,
                ]);
                $keptIds[] = $existing->id_bahan;
            } else {
                $created = $product->bahan()->create([
                    'nama_bahan' => $row['nama_bahan'],
                    'harga_satuan' => $row['harga_satuan'],
                    'is_active' => true,
                ]);
                $keptIds[] = $created->id_bahan;
            }
        }

        $product->bahan()
            ->whereNotIn('id_bahan', $keptIds)
            ->update(['is_active' => false]);
    }

    private function storePrimaryImage(Request $request): string
    {
        $file = $request->file('gambar');

        if (! $file instanceof UploadedFile) {
            throw new \RuntimeException('Gambar produk tidak valid.');
        }

        return $this->storeUploadedFile(
            $file,
            'images/products',
            $request->string('nama_produk')->toString(),
        );
    }

    private function storeGalleryImages(Product $product, Request $request): void
    {
        $files = $request->file('galeri', []);

        if ($files instanceof UploadedFile) {
            $files = [$files];
        }

        if (! is_array($files) || $files === []) {
            return;
        }

        $urutan = (int) $product->galeri()->max('urutan');

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            $urutan++;

            $product->galeri()->create([
                'file' => $this->storeUploadedFile($file, 'images/products/galeri', $product->nama_produk),
                'urutan' => $urutan,
            ]);
        }
    }

    /**
     * @param  array<int, int|string>  $ids
     */
    private function deleteSelectedGalleryImages(Product $product, array $ids): void
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));

        if ($ids === []) {
            return;
        }

        $product->galeri()
            ->whereIn('id_gambar', $ids)
            ->get()
            ->each(function ($image): void {
                $this->deleteStoredFile('images/products/galeri/'.basename($image->file));
                $image->delete();
            });
    }

    private function storeUploadedFile(UploadedFile $file, string $folder, string $prefix): string
    {
        $extension = strtolower($file->extension() ?: 'jpg');

        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $extension = 'jpg';
        }

        $name = Str::slug($prefix);
        $name = ($name !== '' ? $name : 'produk').'-'.Str::lower(Str::random(10)).'.'.$extension;
        $directory = public_path($folder);

        File::ensureDirectoryExists($directory);
        $file->move($directory, $name);

        return $name;
    }

    private function deleteImage(?string $name): void
    {
        if (blank($name) || str_starts_with($name, 'http')) {
            return;
        }

        $this->deleteStoredFile('images/products/'.basename($name));
    }

    private function deleteStoredFile(string $relativePath): void
    {
        if (blank($relativePath) || str_starts_with($relativePath, 'http')) {
            return;
        }

        $path = public_path($relativePath);

        if (is_file($path)) {
            unlink($path);
        }
    }
}
