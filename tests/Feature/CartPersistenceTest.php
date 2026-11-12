<?php

namespace Tests\Feature;

use App\Models\CustomerCart;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\FinishingSeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\SettingSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartPersistenceTest extends TestCase
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

    public function test_customer_cart_is_persisted_and_survives_logout_and_login(): void
    {
        $customer = User::query()->where('username', 'raihan')->firstOrFail();
        $product = Product::query()->with('bahanAktif')->where('satuan', 'meter')->firstOrFail();

        $this->actingAs($customer)
            ->post(route('cart.store'), $this->cartPayload($product))
            ->assertRedirect(route('cart.index'));

        $this->assertDatabaseHas('keranjang_pelanggan', ['id_user' => $customer->id_user]);

        $this->post(route('logout'))->assertRedirect(route('home'));
        $this->assertGuest();

        $this->assertDatabaseHas('keranjang_pelanggan', ['id_user' => $customer->id_user]);

        $this->post(route('login.store'), [
            'login' => 'raihan',
            'password' => 'pelanggan123',
        ])->assertRedirect(route('customer.dashboard'));

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee($product->nama_produk)
            ->assertSee('1 item siap diproses');
    }

    public function test_guest_cart_is_merged_into_the_account_after_login(): void
    {
        $customer = User::query()->where('username', 'raihan')->firstOrFail();
        $product = Product::query()->with('bahanAktif')->where('satuan', 'meter')->firstOrFail();

        $this->post(route('cart.store'), $this->cartPayload($product))
            ->assertRedirect(route('cart.index'));

        $this->post(route('login.store'), [
            'login' => 'raihan',
            'password' => 'pelanggan123',
        ])->assertRedirect(route('customer.dashboard'));

        $stored = CustomerCart::query()->where('id_user', $customer->id_user)->firstOrFail();

        $this->assertCount(1, $stored->items);
        $this->assertSame($product->id_produk, array_values($stored->items)[0]['product_id']);

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee($product->nama_produk);
    }

    public function test_customer_cart_is_restored_from_database_when_session_is_empty(): void
    {
        $customer = User::query()->where('username', 'raihan')->firstOrFail();
        $product = Product::query()->with('bahanAktif')->where('satuan', 'meter')->firstOrFail();

        $this->actingAs($customer)
            ->post(route('cart.store'), $this->cartPayload($product));

        $this->flushSession();

        $this->actingAs($customer)
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee($product->nama_produk);
    }

    public function test_checkout_draft_is_autosaved_and_restored_after_login(): void
    {
        $customer = User::query()->where('username', 'raihan')->firstOrFail();
        $product = Product::query()->with('bahanAktif')->where('satuan', 'meter')->firstOrFail();

        $this->actingAs($customer)
            ->post(route('cart.store'), $this->cartPayload($product));

        $this->actingAs($customer)
            ->postJson(route('checkout.draft'), [
                'metode_pengambilan' => 'Kirim Kurir/Ekspedisi',
                'alamat_pengiriman' => 'Jl. Merdeka No. 10, Metro Pusat',
                'catatan' => 'Tolong packing rapi.',
            ])
            ->assertOk()
            ->assertJson(['saved' => true]);

        $stored = CustomerCart::query()->where('id_user', $customer->id_user)->firstOrFail();

        $this->assertSame('Jl. Merdeka No. 10, Metro Pusat', $stored->checkout_draft['alamat_pengiriman'] ?? null);
        $this->assertSame('Tolong packing rapi.', $stored->checkout_draft['catatan'] ?? null);

        $this->post(route('logout'));

        $this->post(route('login.store'), [
            'login' => 'raihan',
            'password' => 'pelanggan123',
        ]);

        $this->get(route('checkout.index'))
            ->assertOk()
            ->assertSee('Jl. Merdeka No. 10, Metro Pusat')
            ->assertSee('Tolong packing rapi.');
    }

    public function test_checkout_draft_is_removed_after_the_order_is_created(): void
    {
        $customer = User::query()->where('username', 'raihan')->firstOrFail();
        $product = Product::query()->with('bahanAktif')->where('satuan', 'meter')->firstOrFail();

        $this->actingAs($customer)
            ->post(route('cart.store'), $this->cartPayload($product));

        $this->actingAs($customer)
            ->postJson(route('checkout.draft'), [
                'metode_pengambilan' => 'Ambil di Toko',
                'catatan' => 'Draft sementara.',
            ]);

        $this->assertDatabaseHas('keranjang_pelanggan', ['id_user' => $customer->id_user]);

        $this->actingAs($customer)
            ->post(route('checkout.store'), [
                'metode_pengambilan' => 'Ambil di Toko',
                'catatan' => 'Pesanan uji.',
            ])
            ->assertRedirect();

        $this->assertDatabaseMissing('keranjang_pelanggan', ['id_user' => $customer->id_user]);
    }

    /**
     * @return array<string, int|float|string>
     */
    private function cartPayload(Product $product): array
    {
        return [
            'product_id' => $product->id_produk,
            'material_id' => $product->bahanAktif->firstOrFail()->id_bahan,
            'panjang' => 2,
            'lebar' => 1,
            'jumlah' => max($product->min_order, 1),
            'tipe_desain' => 'Punya Desain Sendiri',
        ];
    }
}
