<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PaymentSetting;
use App\Models\User;
use Database\Seeders\SettingSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPaymentSettingsTest extends TestCase
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

    public function test_admin_can_open_midtrans_payment_settings(): void
    {
        $admin = User::query()->where('username', 'admin')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.payments.index'))
            ->assertOk()
            ->assertSee('Mode Sandbox')
            ->assertSee('Mode Production')
            ->assertSee('Nekad Romo')
            ->assertSee('URL Webhook');
    }

    public function test_activation_switches_the_active_midtrans_mode(): void
    {
        $admin = User::query()->where('username', 'admin')->firstOrFail();
        $sandbox = PaymentSetting::query()->where('is_production', false)->firstOrFail();
        $production = PaymentSetting::query()->where('is_production', true)->firstOrFail();

        $production->activate();
        $this->assertTrue($production->fresh()->is_active);

        $this->actingAs($admin)
            ->post(route('admin.payments.activate', $sandbox))
            ->assertRedirect();

        $this->assertTrue($sandbox->fresh()->is_active);
        $this->assertFalse($production->fresh()->is_active);
        $this->assertFalse(PaymentSetting::active()->is_production);
    }

    public function test_admin_can_update_payment_channels_and_keys(): void
    {
        $admin = User::query()->where('username', 'admin')->firstOrFail();
        $sandbox = PaymentSetting::query()->where('is_production', false)->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.payments.update', $sandbox), [
                'merchant_name' => 'Nekad Romo',
                'server_key' => 'SB-Mid-server-BaruUntukPengujian',
                'client_key' => 'SB-Mid-client-BaruUntukPengujian',
                'signature_key' => 'dummy-signature-key-testing',
                'expiry_hours' => 12,
                'enabled_payments' => ['qris', 'gopay', 'bca_va'],
            ])
            ->assertRedirect();

        $sandbox->refresh();

        $this->assertSame('SB-Mid-server-BaruUntukPengujian', $sandbox->server_key);
        $this->assertSame(['qris', 'gopay', 'bca_va'], $sandbox->enabled_payments);
        $this->assertSame(12, $sandbox->expiry_hours);
        $this->assertTrue($sandbox->isConfigured());
    }

    public function test_production_key_is_rejected_when_sandbox_mode_is_selected(): void
    {
        $sandbox = PaymentSetting::query()->where('is_production', false)->firstOrFail();
        $sandbox->forceFill(['server_key' => 'dummy-server-key-production'])->save();

        $this->assertFalse($sandbox->fresh()->isConfigured());
    }

    public function test_configured_payment_page_shows_midtrans_payment_methods(): void
    {
        $customer = User::query()->where('username', 'raihan')->firstOrFail();

        $order = Order::query()->create([
            'kode_pesanan' => 'NR-20261110-BAYAR',
            'id_user' => $customer->id_user,
            'tgl_pesan' => now(),
            'total_produk' => 75000,
            'ongkir' => 0,
            'grand_total' => 75000,
            'metode_pengambilan' => 'Ambil di Toko',
            'status_pembayaran' => 'Menunggu Pembayaran',
            'status_pesanan' => Order::STATUS_MENUNGGU,
        ]);

        $this->actingAs($customer)
            ->get(route('payments.show', $order))
            ->assertOk()
            ->assertSee('Pilih metode pembayaran')
            ->assertSee('QRIS')
            ->assertSee('Nekad Romo')
            ->assertSee('Bayar sebelum');
    }

    public function test_customer_cannot_open_payment_settings(): void
    {
        $customer = User::query()->where('username', 'raihan')->firstOrFail();

        $this->actingAs($customer)
            ->get(route('admin.payments.index'))
            ->assertForbidden();
    }
}
