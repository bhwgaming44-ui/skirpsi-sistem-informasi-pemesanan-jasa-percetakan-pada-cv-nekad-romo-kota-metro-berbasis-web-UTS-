<?php

namespace Tests\Feature;

use App\Models\GatewayTransaction;
use App\Models\Order;
use App\Models\PaymentSetting;
use App\Models\User;
use App\Services\PaymentService;
use Database\Seeders\SettingSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MidtransWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            SettingSeeder::class,
            UserSeeder::class,
        ]);
    }

    public function test_chargeback_notification_marks_payment_as_chargeback(): void
    {
        $order = $this->paidOrder();
        $setting = PaymentSetting::query()->where('is_production', false)->firstOrFail();
        $payload = $this->payload($order, $setting->server_key, 'chargeback');

        $this->postJson(route('payments.webhook'), $payload)
            ->assertOk()
            ->assertJson(['message' => 'OK']);

        $this->assertSame('Chargeback', $order->fresh()->status_pembayaran);
        $this->assertDatabaseHas('transaksi_gateway', [
            'id_pesanan' => $order->id_pesanan,
            'transaction_status' => 'chargeback',
        ]);
    }

    public function test_expired_notification_marks_unpaid_order_as_expired(): void
    {
        $order = $this->unpaidOrder();
        $setting = PaymentSetting::query()->where('is_production', false)->firstOrFail();
        $payload = $this->payload($order, $setting->server_key, 'expire');

        $this->postJson(route('payments.webhook'), $payload)
            ->assertOk();

        $this->assertSame('Kadaluarsa', $order->fresh()->status_pembayaran);
    }

    public function test_webhook_rejects_invalid_signature(): void
    {
        $order = $this->unpaidOrder();
        $payload = $this->payload($order, 'kunci-yang-salah', 'settlement');

        $this->postJson(route('payments.webhook'), $payload)
            ->assertUnauthorized();

        $this->assertSame('Menunggu Pembayaran', $order->fresh()->status_pembayaran);
        $this->assertNull(GatewayTransaction::query()->where('id_pesanan', $order->id_pesanan)->first());
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Order $order, string $serverKey, string $status): array
    {
        $grossAmount = number_format((float) $order->grand_total, 2, '.', '');

        return [
            'order_id' => $order->kode_pesanan,
            'status_code' => '200',
            'gross_amount' => $grossAmount,
            'signature_key' => hash('sha512', $order->kode_pesanan.'200'.$grossAmount.$serverKey),
            'transaction_id' => 'MID-'.strtoupper(uniqid()),
            'transaction_status' => $status,
            'payment_type' => 'qris',
            'transaction_time' => now()->format('Y-m-d H:i:s'),
            'fraud_status' => 'accept',
        ];
    }

    private function unpaidOrder(): Order
    {
        $customer = User::query()->where('username', 'raihan')->firstOrFail();

        return Order::query()->create([
            'kode_pesanan' => 'NR-20261110-HOOK',
            'id_user' => $customer->id_user,
            'tgl_pesan' => now(),
            'total_produk' => 50000,
            'ongkir' => 0,
            'grand_total' => 50000,
            'metode_pengambilan' => 'Ambil di Toko',
            'status_pembayaran' => 'Menunggu Pembayaran',
            'status_pesanan' => Order::STATUS_MENUNGGU,
        ]);
    }

    private function paidOrder(): Order
    {
        $order = $this->unpaidOrder();

        app(PaymentService::class)->markPaid($order);

        return $order->fresh();
    }
}
