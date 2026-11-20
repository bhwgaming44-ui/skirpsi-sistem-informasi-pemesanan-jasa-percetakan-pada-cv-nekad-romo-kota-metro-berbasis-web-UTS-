<?php

namespace Tests\Feature;

use App\Models\GatewayTransaction;
use App\Models\Order;
use App\Models\User;
use App\Services\MidtransService;
use Database\Seeders\SettingSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MidtransSnapTokenTest extends TestCase
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

    public function test_snap_token_is_created_with_revised_order_id_when_midtrans_reports_duplicate(): void
    {
        $customer = User::query()->where('username', 'raihan')->firstOrFail();

        $order = Order::query()->create([
            'kode_pesanan' => 'NR-20261119-REVC',
            'id_user' => $customer->id_user,
            'tgl_pesan' => now(),
            'total_produk' => 25000,
            'ongkir' => 0,
            'grand_total' => 25000,
            'metode_pengambilan' => 'Ambil di Toko',
            'metode_pembayaran' => 'qris',
            'status_pembayaran' => 'Menunggu Pembayaran',
            'status_pesanan' => Order::STATUS_MENUNGGU,
        ]);

        $attempt = 0;

        Http::fake(function (Request $request) use (&$attempt) {
            $attempt++;

            if ($attempt === 1) {
                return Http::response([
                    'error_messages' => ['transaction_details.order_id has already been taken'],
                ], 400);
            }

            return Http::response([
                'token' => 'snap-token-revisi',
                'order_id' => $request->data()['transaction_details']['order_id'],
                'transaction_id' => 'tx-revisi-1',
            ], 201);
        });

        $token = app(MidtransService::class)->createSnapToken($order, null, 'qris');

        $this->assertSame('snap-token-revisi', $token);
        $this->assertSame('snap-token-revisi', $order->fresh()->snap_token);
        $this->assertSame(2, $attempt);

        $recorded = Http::recorded();
        $revisionOrderId = $recorded[1][0]->data()['transaction_details']['order_id'];

        $this->assertStringStartsWith('NR-20261119-REVC-REV', $revisionOrderId);

        $this->assertDatabaseHas('transaksi_gateway', [
            'order_id_gateway' => $revisionOrderId,
            'transaction_id_gateway' => 'tx-revisi-1',
        ]);
    }

    public function test_snap_token_is_not_retried_for_unrelated_midtrans_errors(): void
    {
        $customer = User::query()->where('username', 'raihan')->firstOrFail();

        $order = Order::query()->create([
            'kode_pesanan' => 'NR-20261119-REVE',
            'id_user' => $customer->id_user,
            'tgl_pesan' => now(),
            'total_produk' => 30000,
            'ongkir' => 0,
            'grand_total' => 30000,
            'metode_pengambilan' => 'Ambil di Toko',
            'metode_pembayaran' => 'qris',
            'status_pembayaran' => 'Menunggu Pembayaran',
            'status_pesanan' => Order::STATUS_MENUNGGU,
        ]);

        Http::fake([
            '*' => Http::response([
                'error_messages' => ['enabled_payments is not valid'],
            ], 400),
        ]);

        $token = app(MidtransService::class)->createSnapToken($order, null, 'qris');

        $this->assertNull($token);
        $this->assertNull($order->fresh()->snap_token);

        Http::assertSentCount(1);
    }

    public function test_status_sync_uses_the_revised_gateway_order_id(): void
    {
        $customer = User::query()->where('username', 'raihan')->firstOrFail();

        $order = Order::query()->create([
            'kode_pesanan' => 'NR-20261119-REVS',
            'id_user' => $customer->id_user,
            'tgl_pesan' => now(),
            'total_produk' => 25000,
            'ongkir' => 0,
            'grand_total' => 25000,
            'metode_pengambilan' => 'Ambil di Toko',
            'metode_pembayaran' => 'qris',
            'status_pembayaran' => 'Menunggu Pembayaran',
            'status_pesanan' => Order::STATUS_MENUNGGU,
        ]);

        $gatewayOrderId = 'NR-20261119-REVS-REV1234561';

        GatewayTransaction::query()->create([
            'id_pesanan' => $order->id_pesanan,
            'order_id_gateway' => $gatewayOrderId,
            'transaction_id_gateway' => 'tx-revisi-status',
            'gross_amount' => 25000,
            'transaction_status' => 'pending',
        ]);

        Http::fake([
            '*' => Http::response([
                'order_id' => $gatewayOrderId,
                'transaction_id' => 'tx-revisi-status',
                'transaction_status' => 'settlement',
                'status_code' => '200',
                'gross_amount' => '25000.00',
                'payment_type' => 'qris',
                'fraud_status' => 'accept',
            ], 200),
        ]);

        $result = app(MidtransService::class)->syncStatus($order);

        $this->assertTrue($result['ok'] ?? false);

        Http::assertSent(function (Request $request) use ($gatewayOrderId): bool {
            return str_contains($request->url(), $gatewayOrderId);
        });

        $this->assertTrue($order->fresh()->isPaid());
    }
}
