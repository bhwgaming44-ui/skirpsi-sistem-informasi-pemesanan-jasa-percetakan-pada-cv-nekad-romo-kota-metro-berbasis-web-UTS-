<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PaymentSetting;
use App\Models\User;
use Database\Seeders\SettingSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentMethodSelectionTest extends TestCase
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

    public function test_payment_page_lists_methods_when_nothing_selected_yet(): void
    {
        $order = $this->unpaidOrder();

        $this->actingAs($this->customer())
            ->get(route('payments.show', $order))
            ->assertOk()
            ->assertSee('Pilih metode pembayaran')
            ->assertSee('Belum dipilih')
            ->assertSee('QRIS')
            ->assertSee('Virtual Account BCA');
    }

    public function test_customer_can_select_a_payment_method_and_it_is_saved(): void
    {
        $order = $this->unpaidOrder();

        $this->actingAs($this->customer())
            ->post(route('payments.method', $order), ['metode' => 'qris'])
            ->assertRedirect(route('payments.show', $order))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('pesanan', [
            'id_pesanan' => $order->id_pesanan,
            'metode_pembayaran' => 'qris',
        ]);

        $this->actingAs($this->customer())
            ->get(route('payments.show', $order))
            ->assertOk()
            ->assertSee('Metode pembayaran terpilih')
            ->assertSee('Kembali / Ganti Metode Pembayaran')
            ->assertSee('QRIS');
    }

    public function test_customer_can_go_back_and_choose_another_method(): void
    {
        $order = $this->unpaidOrder();
        $customer = $this->customer();

        $this->actingAs($customer)
            ->post(route('payments.method', $order), ['metode' => 'bca_va'])
            ->assertRedirect(route('payments.show', $order));

        $this->actingAs($customer)
            ->delete(route('payments.method.reset', $order))
            ->assertRedirect(route('payments.show', $order))
            ->assertSessionHas('success');

        $this->assertNull($order->fresh()->metode_pembayaran);

        $this->actingAs($customer)
            ->post(route('payments.method', $order), ['metode' => 'gopay'])
            ->assertRedirect(route('payments.show', $order));

        $this->assertSame('gopay', $order->fresh()->metode_pembayaran);
    }

    public function test_unavailable_method_is_rejected(): void
    {
        $order = $this->unpaidOrder();

        $this->actingAs($this->customer())
            ->post(route('payments.method', $order), ['metode' => 'bitcoin'])
            ->assertSessionHasErrors('metode');

        $this->assertNull($order->fresh()->metode_pembayaran);
    }

    public function test_paid_order_cannot_change_its_payment_method(): void
    {
        $order = $this->unpaidOrder();
        $order->forceFill(['status_pembayaran' => 'Lunas'])->save();

        $this->actingAs($this->customer())
            ->post(route('payments.method', $order), ['metode' => 'qris'])
            ->assertRedirect(route('payments.show', $order))
            ->assertSessionHas('error');

        $this->assertNull($order->fresh()->metode_pembayaran);
    }

    public function test_customer_cannot_change_someone_elses_payment_method(): void
    {
        $order = $this->unpaidOrder();
        $other = User::query()->where('username', 'admin')->firstOrFail();

        $this->actingAs($other)
            ->post(route('payments.method', $order), ['metode' => 'qris'])
            ->assertForbidden();

        $this->assertNull($order->fresh()->metode_pembayaran);
    }

    public function test_only_channels_enabled_by_admin_are_accepted(): void
    {
        $setting = PaymentSetting::query()->where('is_active', true)->firstOrFail();
        $setting->forceFill(['enabled_payments' => ['qris', 'bca_va']])->save();

        $order = $this->unpaidOrder();

        $this->actingAs($this->customer())
            ->post(route('payments.method', $order), ['metode' => 'gopay'])
            ->assertSessionHasErrors('metode');

        $this->actingAs($this->customer())
            ->post(route('payments.method', $order), ['metode' => 'bca_va'])
            ->assertRedirect(route('payments.show', $order));

        $this->assertSame('bca_va', $order->fresh()->metode_pembayaran);
    }

    private function unpaidOrder(): Order
    {
        return Order::query()->create([
            'kode_pesanan' => 'NR-20261113-METODE',
            'id_user' => $this->customer()->id_user,
            'tgl_pesan' => now(),
            'total_produk' => 50000,
            'ongkir' => 0,
            'grand_total' => 50000,
            'metode_pengambilan' => 'Ambil di Toko',
            'status_pembayaran' => 'Menunggu Pembayaran',
            'status_pesanan' => Order::STATUS_MENUNGGU,
        ]);
    }

    private function customer(): User
    {
        return User::query()->where('username', 'raihan')->firstOrFail();
    }
}
