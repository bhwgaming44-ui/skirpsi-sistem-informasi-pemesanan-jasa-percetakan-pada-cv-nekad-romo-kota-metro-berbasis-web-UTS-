<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Models\User;
use App\Services\SavedProductService;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __construct(private readonly SavedProductService $savedProducts) {}

    public function __invoke(): View
    {
        $categories = Category::query()
            ->withCount(['produk' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('id_kategori')
            ->get();

        $featuredProducts = Product::query()
            ->aktif()
            ->unggulan()
            ->with(['kategori', 'bahanAktif', 'finishing'])
            ->orderBy('id_produk')
            ->limit(6)
            ->get();

        $stats = [
            'pelanggan' => User::query()->where('role', 'pelanggan')->count(),
            'pesanan' => Order::query()->where('status_pembayaran', 'Lunas')->count(),
            'produk' => Product::query()->where('is_active', true)->count(),
            'rating' => '4.9',
        ];

        return view('home', [
            'categories' => $categories,
            'featuredProducts' => $featuredProducts,
            'savedProductIds' => $this->savedProducts->ids(),
            'stats' => $stats,
            'store' => StoreSetting::current(),
        ]);
    }
}
