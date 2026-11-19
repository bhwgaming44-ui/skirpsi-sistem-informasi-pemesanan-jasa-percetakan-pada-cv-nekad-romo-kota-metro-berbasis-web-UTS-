<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Database\Seeders\SettingSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class SharedPaymentLinkTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->seed([
            SettingSeeder::class,
            UserSeeder::class,
        ]);
    }

    public function test_guest_with_signed_link_can_open_the_payment_page_from_another_device(): void
    {
        $order = $this->makeOrder();

        $url = URL::temporarySignedRoute('payments.shared', now()->addHour(), ['order' => $order]);

        $this->get($url)
            ->assertOk()
            ->assertSee('Selesaikan Pembayaran')
            ->assertSee($order->kode_pesanan)
            ->assertSee('Bayar Sekarang');
    }

    public function test_guest_without_a_valid_signature_cannot_open_the_shared_payment_page(): void
    {
        $order = $this->makeOrder();

        $url = URL::temporarySignedRoute('payments.shared', now()->addHour(), ['order' => $order]);

        $this->get($url.'tampered')
            ->assertForbidden();
    }

    public function test_guest_with_signed_link_can_create_a_snap_token(): void
    {
        $order = $this->makeOrder();

        Http::fake([
            '*' => Http::response([
                'token' => 'token-lintas-perangkat',
                'order_id' => $order->kode_pesanan,
                'transaction_id' => 'tx-lintas-perangkat',
            ], 201),
        ]);

        $url = URL::temporarySignedRoute('payments.shared.snap-token', now()->addHour(), ['order' => $order]);

        $this->post($url, [], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['token' => 'token-lintas-perangkat']);

        $this->assertSame('token-lintas-perangkat', $order->fresh()->snap_token);
    }

    public function test_staff_can_open_a_customer_payment_page(): void
    {
        $admin = User::query()->where('username', 'admin')->firstOrFail();
        $order = $this->makeOrder();

        $this->actingAs($admin)
            ->get(route('payments.show', $order))
            ->assertOk()
            ->assertSee($order->kode_pesanan);
    }

    public function test_another_customer_cannot_open_the_payment_page_without_the_payment_key(): void
    {
        $otherCustomer = User::query()
            ->where('role', 'pelanggan')
            ->where('username', '!=', 'raihan')
            ->firstOrFail();

        $order = $this->makeOrder();

        $this->actingAs($otherCustomer)
            ->get(route('payments.show', $order))
            ->assertForbidden();
    }

    public function test_same_order_can_be_paid_from_another_device_or_account_via_payment_key(): void
    {
        $otherCustomer = User::query()
            ->where('role', 'pelanggan')
            ->where('username', '!=', 'raihan')
            ->firstOrFail();

        $order = $this->makeOrder();

        $this->actingAs($otherCustomer)
            ->get($order->paymentUrl())
            ->assertOk()
            ->assertSee($order->kode_pesanan)
            ->assertSee('Bayar Sekarang');
    }

    public function test_snap_token_accepts_the_payment_key_from_another_device(): void
    {
        $otherCustomer = User::query()
            ->where('role', 'pelanggan')
            ->where('username', '!=', 'raihan')
            ->firstOrFail();

        $order = $this->makeOrder();

        Http::fake([
            '*' => Http::response([
                'token' => 'token-device-lain',
                'order_id' => $order->kode_pesanan,
                'transaction_id' => 'tx-device-lain',
            ], 201),
        ]);

        $url = route('payments.snap-token', ['order' => $order, 'key' => $order->paymentKey()]);

        $this->actingAs($otherCustomer)
            ->post($url, [], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJson(['token' => 'token-device-lain']);

        $this->assertSame('token-device-lain', $order->fresh()->snap_token);
    }

    public function test_unpaid_tracking_page_offers_a_shared_payment_button(): void
    {
        $order = $this->makeOrder();

        $this->get(route('tracking.show', $order->kode_pesanan))
            ->assertOk()
            ->assertSee('Bayar Sekarang');
    }

    private function makeOrder(): Order
    {
        $customer = User::query()->where('username', 'raihan')->firstOrFail();

        return Order::query()->create([
            'kode_pesanan' => 'NR-20261119-BAGI',
            'id_user' => $customer->id_user,
            'tgl_pesan' => now(),
            'total_produk' => 45000,
            'ongkir' => 0,
            'grand_total' => 45000,
            'metode_pengambilan' => 'Ambil di Toko',
            'status_pembayaran' => 'Menunggu Pembayaran',
            'status_pesanan' => Order::STATUS_MENUNGGU,
        ]);
    }
}
