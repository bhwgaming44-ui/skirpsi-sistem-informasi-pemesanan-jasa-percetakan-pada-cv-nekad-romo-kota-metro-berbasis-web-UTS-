<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PaymentSetting;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\FinishingSeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\SettingSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutFlowTest extends TestCase
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

        PaymentSetting::query()->update([
            'server_key' => 'SB-Mid-server-Placeholder12345',
            'client_key' => 'SB-Mid-client-Placeholder12345',
        ]);
    }

    public function test_customer_can_order_from_cart_and_pay_via_demo_simulation(): void
    {
        $customer = User::query()->where('username', 'raihan')->firstOrFail();

        $product = Product::query()
            ->with('bahanAktif')
            ->where('satuan', 'meter')
            ->firstOrFail();

        $material = $product->bahanAktif->firstOrFail();

        $this->actingAs($customer)
            ->post(route('cart.store'), [
                'product_id' => $product->id_produk,
                'material_id' => $material->id_bahan,
                'panjang' => 2,
                'lebar' => 1,
                'jumlah' => max($product->min_order, 1),
                'tipe_desain' => 'Punya Desain Sendiri',
                'catatan_desain' => 'Uji alur pemesanan.',
            ])
            ->assertRedirect(route('cart.index'));

        $this->actingAs($customer)
            ->get(route('cart.index'))
            ->assertOk();

        $this->actingAs($customer)
            ->get(route('checkout.index'))
            ->assertOk();

        $this->actingAs($customer)
            ->post(route('checkout.store'), [
                'metode_pengambilan' => 'Ambil di Toko',
                'catatan' => 'Pesanan uji otomatis.',
            ])
            ->assertRedirect();

        $order = Order::query()->latest('id_pesanan')->firstOrFail();

        $this->assertSame($customer->id_user, $order->id_user);
        $this->assertSame(Order::STATUS_MENUNGGU, $order->status_pesanan);
        $this->assertSame('Menunggu Pembayaran', $order->status_pembayaran);
        $this->assertDatabaseHas('detail_pesanan', ['id_pesanan' => $order->id_pesanan]);

        $this->actingAs($customer)
            ->post(route('payments.simulate', $order), ['hasil' => 'sukses'])
            ->assertRedirect(route('payments.show', $order));

        $order->refresh();

        $this->assertSame('Lunas', $order->status_pembayaran);
        $this->assertSame(Order::STATUS_ANTREAN, $order->status_pesanan);
        $this->assertDatabaseHas('transaksi_gateway', [
            'id_pesanan' => $order->id_pesanan,
            'transaction_status' => 'settlement',
        ]);
    }

    public function test_customer_cannot_open_another_customers_order(): void
    {
        $customer = User::query()->where('username', 'raihan')->firstOrFail();
        $otherCustomer = User::query()->where('username', 'siti')->firstOrFail();

        $order = Order::query()->create([
            'kode_pesanan' => 'NR-20260101-TEST',
            'id_user' => $otherCustomer->id_user,
            'tgl_pesan' => now(),
            'total_produk' => 50000,
            'ongkir' => 0,
            'grand_total' => 50000,
            'metode_pengambilan' => 'Ambil di Toko',
            'status_pembayaran' => 'Menunggu Pembayaran',
            'status_pesanan' => Order::STATUS_MENUNGGU,
        ]);

        $this->actingAs($customer)
            ->get(route('payments.show', $order))
            ->assertForbidden();
    }
}
