<?php

namespace Tests\Feature;

use App\Models\Product;
use Database\Seeders\CategorySeeder;
use Database\Seeders\FinishingSeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\SettingSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartBulkTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->seed([
            SettingSeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
            FinishingSeeder::class,
            ProductSeeder::class,
        ]);
    }

    public function test_guest_can_add_multiple_products_to_the_cart_at_once(): void
    {
        $products = Product::query()
            ->aktif()
            ->whereHas('bahanAktif')
            ->orderBy('id_produk')
            ->limit(3)
            ->get();

        $this->assertCount(3, $products);

        $this->post(route('cart.bulk'), [
            'product_ids' => $products->pluck('id_produk')->all(),
        ])
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('success');

        $cart = session('cart');

        $this->assertIsArray($cart);
        $this->assertCount(3, $cart);

        foreach ($cart as $item) {
            $product = $products->firstWhere('id_produk', $item['product_id']);

            $this->assertNotNull($product);
            $this->assertSame($product->bahanAktif->first()->id_bahan, $item['material_id']);
            $this->assertSame(max($product->min_order, 1), $item['jumlah']);
            $this->assertSame('Punya Desain Sendiri', $item['tipe_desain']);
            $this->assertSame([], $item['finishing_ids']);
        }

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee($products->first()->nama_produk)
            ->assertSee($products->last()->nama_produk);
    }

    public function test_duplicate_product_ids_are_only_added_once(): void
    {
        $product = Product::query()->aktif()->whereHas('bahanAktif')->firstOrFail();

        $this->post(route('cart.bulk'), [
            'product_ids' => [
                $product->id_produk,
                $product->id_produk,
                $product->id_produk,
            ],
        ])->assertRedirect(route('cart.index'));

        $this->assertCount(1, session('cart'));
    }

    public function test_at_least_one_product_is_required(): void
    {
        $this->post(route('cart.bulk'), ['product_ids' => []])
            ->assertSessionHasErrors('product_ids');
    }

    public function test_inactive_products_are_skipped_with_a_warning(): void
    {
        $products = Product::query()
            ->aktif()
            ->whereHas('bahanAktif')
            ->orderBy('id_produk')
            ->limit(2)
            ->get();

        $products->last()->update(['is_active' => false]);

        $this->post(route('cart.bulk'), [
            'product_ids' => $products->pluck('id_produk')->all(),
        ])
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('success', fn (string $message): bool => str_contains($message, '1 produk berhasil')
                && str_contains($message, '1 produk tidak tersedia'));

        $cart = session('cart');

        $this->assertCount(1, $cart);
        $this->assertSame($products->first()->id_produk, array_values($cart)[0]['product_id']);
    }

    public function test_catalog_filter_returns_all_matching_product_ids(): void
    {
        $response = $this->getJson(route('catalog.filter'));

        $response->assertOk()->assertJsonStructure(['html', 'ids', 'total']);

        $this->assertSame(
            Product::query()
                ->aktif()
                ->orderByDesc('is_featured')
                ->orderBy('id_produk')
                ->pluck('id_produk')
                ->all(),
            $response->json('ids')
        );
    }
}
