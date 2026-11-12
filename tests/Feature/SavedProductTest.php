<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\SavedProduct;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\FinishingSeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\SettingSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SavedProductTest extends TestCase
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

    public function test_guest_can_save_and_remove_a_product_from_the_session(): void
    {
        $product = Product::query()->where('is_active', true)->firstOrFail();

        $this->postJson(route('saved.toggle', $product))
            ->assertOk()
            ->assertJson([
                'saved' => true,
                'product_id' => $product->id_produk,
                'count' => 1,
            ]);

        $this->withSession(['saved_products' => [$product->id_produk]])
            ->get(route('saved.index'))
            ->assertOk()
            ->assertSee($product->nama_produk);

        $this->withSession(['saved_products' => [$product->id_produk]])
            ->postJson(route('saved.toggle', $product))
            ->assertOk()
            ->assertJson([
                'saved' => false,
                'product_id' => $product->id_produk,
                'count' => 0,
            ]);

        $this->get(route('saved.index'))
            ->assertOk()
            ->assertSee('Belum ada produk tersimpan');
    }

    public function test_saved_products_of_a_customer_persist_in_the_database(): void
    {
        $user = User::query()->where('username', 'raihan')->firstOrFail();
        $product = Product::query()->where('is_active', true)->firstOrFail();

        $this->actingAs($user)
            ->postJson(route('saved.toggle', $product))
            ->assertOk()
            ->assertJsonPath('saved', true)
            ->assertJsonPath('count', 1);

        $this->assertDatabaseHas('produk_tersimpan', [
            'id_user' => $user->id_user,
            'id_produk' => $product->id_produk,
        ]);

        $this->actingAs($user)->get(route('catalog.index'))->assertOk();

        $this->actingAs($user)
            ->get(route('saved.index'))
            ->assertOk()
            ->assertSee($product->nama_produk);

        $this->actingAs($user)
            ->postJson(route('saved.toggle', $product))
            ->assertOk()
            ->assertJsonPath('saved', false);

        $this->assertDatabaseMissing('produk_tersimpan', [
            'id_user' => $user->id_user,
            'id_produk' => $product->id_produk,
        ]);
    }

    public function test_catalog_cards_mark_saved_products_for_the_customer(): void
    {
        $user = User::query()->where('username', 'raihan')->firstOrFail();
        $product = Product::query()->where('is_active', true)->firstOrFail();

        SavedProduct::query()->create([
            'id_user' => $user->id_user,
            'id_produk' => $product->id_produk,
        ]);

        $this->actingAs($user)
            ->get(route('catalog.index'))
            ->assertOk()
            ->assertSee('data-saved="true"', false);
    }

    public function test_inactive_products_cannot_be_saved(): void
    {
        $product = Product::query()->where('is_active', true)->firstOrFail();
        $product->update(['is_active' => false]);

        $this->postJson(route('saved.toggle', $product))->assertNotFound();
    }

    public function test_guest_saved_products_are_merged_into_the_account_after_login(): void
    {
        $user = User::query()->where('username', 'raihan')->firstOrFail();
        $product = Product::query()->where('is_active', true)->firstOrFail();

        $this->withSession(['saved_products' => [$product->id_produk]])
            ->post(route('login.store'), [
                'login' => 'raihan',
                'password' => 'pelanggan123',
            ])
            ->assertRedirect(route('customer.dashboard'));

        $this->assertDatabaseHas('produk_tersimpan', [
            'id_user' => $user->id_user,
            'id_produk' => $product->id_produk,
        ]);
    }
}
