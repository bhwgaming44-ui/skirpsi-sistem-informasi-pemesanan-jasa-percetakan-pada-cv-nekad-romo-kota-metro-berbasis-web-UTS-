<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Database\Seeders\CategorySeeder;
use Database\Seeders\FinishingSeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\SettingSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
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

    public function test_public_pages_render_successfully(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Nekad Romo');

        $this->get(route('catalog.index'))
            ->assertOk()
            ->assertSee('Katalog');

        $this->get(route('tracking.index'))
            ->assertOk();

        $this->get(route('login'))
            ->assertOk();

        $this->get(route('register'))
            ->assertOk();
    }

    public function test_product_detail_page_renders_the_selected_product(): void
    {
        $product = Product::query()->where('is_active', true)->firstOrFail();

        $this->get(route('catalog.show', $product))
            ->assertOk()
            ->assertSee($product->nama_produk);
    }

    public function test_unknown_page_uses_the_custom_not_found_view(): void
    {
        $this->get('/halaman-yang-tidak-ada')
            ->assertNotFound()
            ->assertSee('Halaman tidak ditemukan');
    }

    public function test_catalog_search_still_works_without_javascript(): void
    {
        $this->get(route('catalog.index', ['q' => 'Cetak Mug Keramik Custom']))
            ->assertOk()
            ->assertSee('Cetak Mug Keramik Custom')
            ->assertSee('Hasil pencarian untuk');
    }

    public function test_catalog_filter_endpoint_returns_live_search_results(): void
    {
        $product = Product::query()->where('nama_produk', 'Cetak Mug Keramik Custom')->firstOrFail();

        $response = $this->getJson(route('catalog.filter', ['q' => 'Cetak Mug Keramik Custom']))
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('from', 1)
            ->assertJsonPath('to', 1)
            ->assertJsonPath('search', 'Cetak Mug Keramik Custom')
            ->assertJsonPath('has_filters', true)
            ->assertJsonPath('url', route('catalog.index', ['q' => 'Cetak Mug Keramik Custom'], false));

        $this->assertStringContainsString($product->nama_produk, $response->json('html'));
    }

    public function test_catalog_filter_endpoint_supports_category_and_sorting(): void
    {
        $category = Category::query()->where('nama_kategori', 'Digital Printing Outdoor / Indoor')->firstOrFail();

        $total = Product::query()
            ->where('is_active', true)
            ->where('id_kategori', $category->id_kategori)
            ->count();

        $this->getJson(route('catalog.filter', [
            'kategori' => $category->id_kategori,
            'sort' => 'nama',
        ]))
            ->assertOk()
            ->assertJsonPath('total', $total)
            ->assertJsonPath('category', $category->id_kategori)
            ->assertJsonPath('sort', 'nama')
            ->assertJsonPath('has_filters', true);
    }

    public function test_catalog_filter_endpoint_shows_close_matches_for_typos(): void
    {
        $response = $this->getJson(route('catalog.filter', ['q' => 'baner']))
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('search', 'baner');

        $this->assertStringContainsString('Cetak Banner / Spanduk Outdoor', $response->json('html'));

        $response = $this->getJson(route('catalog.filter', ['q' => 'brosur murah']))
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('search', 'brosur murah');

        $this->assertStringContainsString('Cetak Brosur &amp; Leaflet', $response->json('html'));
    }

    public function test_catalog_filter_endpoint_handles_empty_results_and_invalid_sort(): void
    {
        $this->getJson(route('catalog.filter', ['q' => 'zzzzzz']))
            ->assertOk()
            ->assertJsonPath('total', 0)
            ->assertJsonPath('from', null)
            ->assertJsonPath('to', null)
            ->assertJsonPath('has_filters', true);

        $this->getJson(route('catalog.filter', ['sort' => 'acak']))
            ->assertOk()
            ->assertJsonPath('sort', 'terbaru')
            ->assertJsonPath('has_filters', false);
    }
}
