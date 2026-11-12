<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\SavedProductService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SavedProductController extends Controller
{
    public function __construct(private readonly SavedProductService $savedProducts) {}

    public function index(): View
    {
        $savedProductIds = $this->savedProducts->ids();

        $products = Product::query()
            ->aktif()
            ->whereIn('id_produk', $savedProductIds)
            ->with(['kategori', 'bahanAktif'])
            ->withCount('bahanAktif')
            ->orderByDesc('is_featured')
            ->orderBy('id_produk')
            ->paginate(9);

        return view('saved.index', [
            'products' => $products,
            'savedProductIds' => $savedProductIds,
        ]);
    }

    public function toggle(Request $request, Product $product): JsonResponse|RedirectResponse
    {
        abort_unless($product->is_active, 404);

        $saved = $this->savedProducts->toggle($product->id_produk);
        $message = $saved
            ? $product->nama_produk.' disimpan ke daftar produk tersimpan.'
            : $product->nama_produk.' dihapus dari produk tersimpan.';

        if ($request->expectsJson()) {
            return response()->json([
                'saved' => $saved,
                'product_id' => $product->id_produk,
                'count' => $this->savedProducts->count(),
                'message' => $message,
            ]);
        }

        return back()->with($saved ? 'success' : 'info', $message);
    }
}
