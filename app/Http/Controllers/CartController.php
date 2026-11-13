<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StoreSetting;
use App\Services\CartService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cart) {}

    public function index(): View|RedirectResponse
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('catalog.index')->with('error', 'Keranjang masih kosong. Yuk pilih layanan cetak dulu!');
        }

        return view('cart.index', [
            'items' => $this->cart->items(),
            'subtotal' => $this->cart->subtotal(),
            'store' => StoreSetting::current(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $base = $request->validate([
            'product_id' => ['required', 'integer', 'exists:produk,id_produk'],
            'material_id' => ['required', 'integer', 'exists:bahan,id_bahan'],
        ]);

        /** @var Product $product */
        $product = Product::query()->with(['bahanAktif', 'finishing'])->findOrFail($base['product_id']);

        if (! $product->is_active) {
            throw ValidationException::withMessages(['product_id' => 'Produk ini sedang tidak tersedia.']);
        }

        $validated = $request->validate([
            'panjang' => [$product->satuan === 'meter' ? 'required' : 'nullable', 'numeric', 'min:0.1', 'max:50'],
            'lebar' => [$product->satuan === 'meter' ? 'required' : 'nullable', 'numeric', 'min:0.1', 'max:50'],
            'jumlah' => ['required', 'integer', 'min:1', 'max:5000'],
            'finishing_ids' => ['nullable', 'array'],
            'finishing_ids.*' => ['integer', 'exists:finishing,id_finishing'],
            'tipe_desain' => ['required', Rule::in(['Punya Desain Sendiri', 'Jasa Desain Nekad Romo'])],
            'catatan_desain' => ['nullable', 'string', 'max:1000'],
            'file_desain' => ['nullable', 'file', 'max:'.config('percetakan.maks_upload_kb'), 'mimes:jpg,jpeg,png,webp,pdf,zip'],
        ], [
            'file_desain.mimes' => 'File desain harus berupa JPG, PNG, WEBP, PDF, atau ZIP.',
            'file_desain.max' => 'Ukuran file desain maksimal 20 MB.',
        ]);

        $material = $product->bahanAktif->firstWhere('id_bahan', (int) $base['material_id']);

        if ($material === null) {
            throw ValidationException::withMessages(['material_id' => 'Bahan cetak tidak tersedia untuk produk ini.']);
        }

        $finishingIds = array_map('intval', $validated['finishing_ids'] ?? []);
        $allowedFinishing = $product->finishing->pluck('id_finishing')->all();

        if (array_diff($finishingIds, $allowedFinishing) !== []) {
            throw ValidationException::withMessages(['finishing_ids' => 'Ada opsi finishing yang tidak tersedia untuk produk ini.']);
        }

        $filePath = null;
        $fileName = null;

        if ($request->hasFile('file_desain')) {
            $file = $request->file('file_desain');
            $filePath = $file->store('desain');
            $fileName = $file->getClientOriginalName();
        }

        $this->cart->add([
            'product_id' => $product->id_produk,
            'material_id' => $material->id_bahan,
            'panjang' => (float) ($validated['panjang'] ?? 1),
            'lebar' => (float) ($validated['lebar'] ?? 1),
            'jumlah' => (int) $validated['jumlah'],
            'finishing_ids' => $finishingIds,
            'tipe_desain' => $validated['tipe_desain'],
            'catatan_desain' => $validated['catatan_desain'] ?? null,
            'file_desain' => $filePath,
            'nama_file_desain' => $fileName,
        ]);

        return redirect()
            ->route('cart.index')
            ->with('success', $product->nama_produk.' berhasil masuk keranjang.');
    }

    public function bulkStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_ids' => ['required', 'array', 'min:1', 'max:100'],
            'product_ids.*' => ['integer', 'exists:produk,id_produk'],
        ], [
            'product_ids.required' => 'Pilih minimal satu produk terlebih dahulu.',
            'product_ids.min' => 'Pilih minimal satu produk terlebih dahulu.',
        ]);

        $productIds = array_values(array_unique(array_map('intval', $validated['product_ids'])));

        $products = Product::query()
            ->with('bahanAktif')
            ->whereIn('id_produk', $productIds)
            ->orderBy('id_produk')
            ->get();

        $added = 0;
        $skipped = [];

        foreach ($products as $product) {
            $material = $product->bahanAktif->first();

            if (! $product->is_active || $material === null) {
                $skipped[] = $product->nama_produk;

                continue;
            }

            $this->cart->add([
                'product_id' => $product->id_produk,
                'material_id' => $material->id_bahan,
                'panjang' => 1,
                'lebar' => 1,
                'jumlah' => 1,
                'finishing_ids' => [],
                'tipe_desain' => 'Punya Desain Sendiri',
            ]);

            $added++;
        }

        if ($added === 0) {
            return back()->with('error', 'Produk yang dipilih belum bisa ditambahkan ke keranjang. Silakan pilih produk lain.');
        }

        $message = $added.' produk berhasil ditambahkan ke keranjang.';

        if ($skipped !== []) {
            $message .= ' '.count($skipped).' produk tidak tersedia dan dilewati.';
        }

        return redirect()
            ->route('cart.index')
            ->with('success', $message);
    }

    public function destroy(string $id): RedirectResponse
    {
        $this->cart->remove($id);

        return back()->with('success', 'Item berhasil dihapus dari keranjang.');
    }

    public function clear(): RedirectResponse
    {
        $this->cart->clear();

        return redirect()->route('catalog.index')->with('success', 'Keranjang berhasil dikosongkan.');
    }
}
