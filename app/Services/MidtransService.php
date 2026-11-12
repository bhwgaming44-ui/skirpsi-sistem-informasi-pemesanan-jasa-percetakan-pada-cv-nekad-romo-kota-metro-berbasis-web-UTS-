<?php

namespace App\Services;

use App\Models\GatewayTransaction;
use App\Models\Order;
use App\Models\PaymentSetting;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class MidtransService
{
    public function __construct(private readonly PaymentService $payments) {}

    public function activeSetting(): ?PaymentSetting
    {
        return PaymentSetting::active();
    }

    public function isReady(): bool
    {
        $setting = $this->activeSetting();

        return $setting !== null && $setting->isConfigured();
    }

    public function createSnapToken(Order $order, ?PaymentSetting $setting = null): ?string
    {
        $setting ??= $this->activeSetting();

        if ($setting === null || ! $setting->isConfigured()) {
            return null;
        }

        $order->loadMissing(['detail.finishing', 'user']);

        if ($order->snap_token !== null) {
            $status = $this->fetchStatus($order, $setting);

            if (($status['transaction_status'] ?? null) === 'pending') {
                return $order->snap_token;
            }

            if (in_array($status['transaction_status'] ?? null, ['settlement', 'capture'], true)) {
                $this->applyPayload($order, $status);

                return null;
            }
        }

        $enabledPayments = $setting->paymentChannels();
        $storeName = $setting->displayName();

        $itemDetails = [[
            'id' => 'ORDER-'.$order->id_pesanan,
            'price' => (int) round((float) $order->total_produk),
            'quantity' => 1,
            'name' => mb_substr($storeName.' · Cetak '.$order->kode_pesanan.' ('.$order->detail->count().' item)', 0, 50),
        ]];

        if ((float) $order->ongkir > 0) {
            $itemDetails[] = [
                'id' => 'ONGKIR',
                'price' => (int) round((float) $order->ongkir),
                'quantity' => 1,
                'name' => 'Biaya Pengiriman',
            ];
        }

        $payload = [
            'transaction_details' => [
                'order_id' => $order->kode_pesanan,
                'gross_amount' => (int) round((float) $order->grand_total),
            ],
            'item_details' => $itemDetails,
            'customer_details' => [
                'first_name' => $order->user->nama,
                'email' => $order->user->email,
                'phone' => $order->user->no_hp,
                'billing_address' => [
                    'address' => $order->user->alamat ?? $order->alamat_pengiriman ?? '-',
                ],
                'shipping_address' => [
                    'first_name' => $order->user->nama,
                    'phone' => $order->user->no_hp,
                    'address' => $order->alamat_pengiriman ?: ($order->user->alamat ?? '-'),
                ],
            ],
            'enabled_payments' => $enabledPayments,
            'expiry' => [
                'start_time' => now()->format('Y-m-d H:i:s O'),
                'unit' => 'hours',
                'duration' => max(1, (int) $setting->expiry_hours),
            ],
            'callbacks' => [
                'finish' => route('payments.finish', $order->kode_pesanan),
            ],
            'custom_field1' => $order->kode_pesanan,
            'custom_field2' => $storeName,
        ];

        if (in_array('credit_card', $enabledPayments, true)) {
            $payload['credit_card'] = ['secure' => true];
        }

        try {
            $response = $this->requestSnapToken($setting, $payload);

            if ($response === null) {
                return null;
            }

            $token = (string) $response->json('token');

            if ($token === '') {
                return null;
            }

            GatewayTransaction::query()->updateOrCreate(
                ['order_id_gateway' => (string) $response->json('order_id', $order->kode_pesanan)],
                [
                    'id_pesanan' => $order->id_pesanan,
                    'transaction_id_gateway' => $response->json('transaction_id'),
                    'gross_amount' => $order->grand_total,
                    'transaction_status' => 'pending',
                    'raw_response' => json_encode($response->json(), JSON_PRETTY_PRINT),
                ]
            );

            $order->forceFill(['snap_token' => $token])->save();

            return $token;
        } catch (Throwable $exception) {
            Log::error('Gagal membuat Snap token Midtrans.', [
                'order' => $order->kode_pesanan,
                'mode' => $setting->modeLabel(),
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handleNotification(array $payload): bool
    {
        $orderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $signature = (string) ($payload['signature_key'] ?? '');

        if ($orderId === '' || $signature === '') {
            Log::warning('Webhook Midtrans ditolak: payload tidak lengkap.', ['order_id' => $orderId]);

            return false;
        }

        $setting = $this->settingForSignature($orderId, $statusCode, $grossAmount, $signature);

        if ($setting === null) {
            Log::warning('Webhook Midtrans ditolak: signature tidak valid.', ['order_id' => $orderId]);

            return false;
        }

        $order = $this->resolveOrder($orderId);

        if ($order === null) {
            Log::warning('Webhook Midtrans: pesanan tidak ditemukan.', ['order_id' => $orderId]);

            return false;
        }

        if (abs((float) $grossAmount - (float) $order->grand_total) > 0.01) {
            Log::warning('Webhook Midtrans: nominal tidak sesuai.', [
                'order_id' => $orderId,
                'expected' => $order->grand_total,
                'received' => $grossAmount,
            ]);

            return false;
        }

        $this->applyPayload($order, $payload);

        return true;
    }

    /**
     * Sinkronisasi status langsung ke API Midtrans. Dipakai halaman pembayaran
     * ketika webhook belum masuk (mis. saat dikembangkan di localhost).
     *
     * @return array<string, mixed>
     */
    public function syncStatus(Order $order, ?PaymentSetting $setting = null): array
    {
        $setting ??= $this->activeSetting();

        if ($setting === null || ! $setting->isConfigured()) {
            return ['ok' => false, 'message' => 'Midtrans belum dikonfigurasi.'];
        }

        $cacheKey = 'midtrans-status-sync:'.$order->id_pesanan;

        try {
            $lock = Cache::lock($cacheKey, 10);

            if (! $lock->get()) {
                return ['ok' => false, 'message' => 'Sinkronisasi sedang berjalan.'];
            }

            try {
                $response = $this->httpRequest($setting, 12)
                    ->get($setting->apiStatusUrl($order->kode_pesanan));

                if ($response->status() === 404) {
                    return ['ok' => false, 'not_found' => true, 'message' => 'Transaksi belum dibuat di Midtrans.'];
                }

                if (! $response->successful()) {
                    return ['ok' => false, 'message' => 'Midtrans menolak permintaan status (HTTP '.$response->status().').'];
                }

                $payload = (array) $response->json();

                if ((string) ($payload['status_code'] ?? '') === '404') {
                    return ['ok' => false, 'not_found' => true, 'message' => 'Transaksi belum dibuat di Midtrans.'];
                }

                $this->applyPayload($order, $payload);

                return ['ok' => true, 'payload' => $payload];
            } finally {
                $lock->release();
            }
        } catch (LockTimeoutException) {
            return ['ok' => false, 'message' => 'Sinkronisasi sedang berjalan.'];
        } catch (Throwable $exception) {
            Log::warning('Sinkronisasi status Midtrans gagal.', [
                'order' => $order->kode_pesanan,
                'message' => $exception->getMessage(),
            ]);

            return ['ok' => false, 'message' => 'Tidak dapat menghubungi Midtrans.'];
        }
    }

    /**
     * @return array{ok: bool, http: int, message: string}
     */
    public function testConnection(PaymentSetting $setting): array
    {
        $probeOrderId = 'CEK-'.now()->format('YmdHis');

        try {
            $response = $this->httpRequest($setting, 12)
                ->get($setting->apiStatusUrl($probeOrderId));

            $http = $response->status();
            $ok = in_array($http, [200, 404], true);

            $message = match (true) {
                $ok => 'Koneksi berhasil. Kunci '.$setting->modeLabel().' dikenali Midtrans.',
                $http === 401 => 'Kunci ditolak Midtrans (401). Periksa Server Key dan mode.',
                default => 'Midtrans membalas HTTP '.$http.'. Periksa koneksi dan kunci.',
            };

            $setting->forceFill([
                'last_test_status' => $ok ? 'ok' : 'failed',
                'last_tested_at' => now(),
            ])->save();

            return ['ok' => $ok, 'http' => $http, 'message' => $message];
        } catch (Throwable $exception) {
            $setting->forceFill([
                'last_test_status' => 'failed',
                'last_tested_at' => now(),
            ])->save();

            return [
                'ok' => false,
                'http' => 0,
                'message' => 'Tidak dapat menghubungi Midtrans: '.$exception->getMessage(),
            ];
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function applyPayload(Order $order, array $payload): void
    {
        $transactionStatus = (string) ($payload['transaction_status'] ?? 'pending');
        $status = $this->normalizeStatus($transactionStatus);

        $va = $payload['va_numbers'][0] ?? [];
        $bank = $va['bank'] ?? ($payload['permata_va_number'] ?? null ? 'permata' : null);

        GatewayTransaction::query()->updateOrCreate(
            ['order_id_gateway' => (string) ($payload['order_id'] ?? $order->kode_pesanan)],
            [
                'id_pesanan' => $order->id_pesanan,
                'transaction_id_gateway' => $payload['transaction_id'] ?? null,
                'payment_type' => $payload['payment_type'] ?? null,
                'bank_va' => $bank,
                'va_number' => $va['va_number'] ?? $payload['permata_va_number'] ?? null,
                'bill_key' => $payload['bill_key'] ?? null,
                'biller_code' => $payload['biller_code'] ?? null,
                'qr_code_url' => $this->extractQrUrl($payload),
                'gross_amount' => $order->grand_total,
                'transaction_status' => $status,
                'fraud_status' => $payload['fraud_status'] ?? null,
                'payment_time' => $payload['transaction_time'] ?? null,
                'settlement_time' => $payload['settlement_time'] ?? null,
                'raw_response' => json_encode($payload, JSON_PRETTY_PRINT),
            ]
        );

        if ($status === 'settlement' && $order->isPaid()) {
            return;
        }

        $fraudStatus = (string) ($payload['fraud_status'] ?? 'accept');
        $method = strtoupper((string) ($payload['payment_type'] ?? 'gateway'));

        if (in_array($status, ['settlement', 'capture'], true) && $fraudStatus !== 'challenge') {
            $this->payments->markPaid($order, [
                'keterangan' => 'Pembayaran '.$method.' berhasil diverifikasi otomatis. Pesanan masuk antrean cetak.',
            ]);

            return;
        }

        if (in_array($status, ['refund', 'partial_refund'], true)) {
            $this->payments->markReversed(
                $order,
                'Dikembalikan',
                'Dana pembayaran dikembalikan melalui Midtrans ('.$status.').'
            );

            return;
        }

        if (in_array($status, ['chargeback', 'partial_chargeback'], true)) {
            $this->payments->markReversed(
                $order,
                'Chargeback',
                'Midtrans menerima chargeback dari penerbit kartu. Hubungi pelanggan untuk penyelesaian.'
            );

            return;
        }

        if (in_array($status, ['deny', 'cancel', 'expire', 'failure'], true)) {
            $label = match ($status) {
                'expire' => 'Kadaluarsa',
                'cancel' => 'Dibatalkan',
                default => 'Gagal',
            };

            $this->payments->markFailed($order, $label, 'Status transaksi gateway: '.$status.'.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchStatus(Order $order, PaymentSetting $setting): array
    {
        try {
            $response = $this->httpRequest($setting, 10)
                ->get($setting->apiStatusUrl($order->kode_pesanan));

            if (! $response->successful()) {
                return [];
            }

            $payload = (array) $response->json();

            return (string) ($payload['status_code'] ?? '') === '404' ? [] : $payload;
        } catch (Throwable) {
            return [];
        }
    }

    private function settingForSignature(
        string $orderId,
        string $statusCode,
        string $grossAmount,
        string $signature
    ): ?PaymentSetting {
        $settings = PaymentSetting::query()
            ->whereNotNull('server_key')
            ->get();

        foreach ($settings as $setting) {
            if (! $setting->isConfigured()) {
                continue;
            }

            $expected = hash('sha512', $orderId.$statusCode.$grossAmount.$setting->server_key);

            if (hash_equals($expected, $signature)) {
                return $setting;
            }
        }

        return null;
    }

    private function resolveOrder(string $orderId): ?Order
    {
        $order = Order::query()->where('kode_pesanan', $orderId)->first();

        if ($order !== null) {
            return $order;
        }

        $baseCode = preg_replace('/-REV\d+$/', '', $orderId);

        return $baseCode !== $orderId
            ? Order::query()->where('kode_pesanan', $baseCode)->first()
            : null;
    }

    private function requestSnapToken(PaymentSetting $setting, array $payload): mixed
    {
        $response = $this->httpRequest($setting)
            ->post($setting->apiBaseUrl(), $payload);

        if ($response->successful()) {
            return $response;
        }

        if ($response->status() === 406 && str_contains((string) $response->json('status_message'), 'order_id')) {
            $payload['transaction_details']['order_id'] .= '-REV'.now()->format('His');

            $retry = $this->httpRequest($setting)
                ->post($setting->apiBaseUrl(), $payload);

            return $retry->successful() ? $retry : null;
        }

        Log::warning('Midtrans menolak pembuatan Snap token.', [
            'http' => $response->status(),
            'body' => Str::limit((string) $response->body(), 500),
        ]);

        return null;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function extractQrUrl(array $payload): ?string
    {
        if (! empty($payload['qr_code_url'])) {
            return (string) $payload['qr_code_url'];
        }

        foreach ((array) ($payload['actions'] ?? []) as $action) {
            if (($action['name'] ?? null) === 'generate-qr-code' && ! empty($action['url'])) {
                return (string) $action['url'];
            }
        }

        return null;
    }

    private function normalizeStatus(string $status): string
    {
        return in_array($status, [
            'pending', 'settlement', 'capture', 'authorize', 'deny', 'expire',
            'cancel', 'failure', 'refund', 'partial_refund', 'chargeback', 'partial_chargeback',
        ], true) ? $status : 'pending';
    }

    private function httpRequest(PaymentSetting $setting, int $timeout = 20): PendingRequest
    {
        $request = Http::withBasicAuth($setting->server_key, '')
            ->acceptJson()
            ->connectTimeout(5)
            ->timeout($timeout);

        $caBundle = $this->caBundle();

        return $caBundle === null ? $request : $request->withOptions(['verify' => $caBundle]);
    }

    /**
     * Sebagian instalasi PHP Windows belum mengatur curl.cainfo, sehingga
     * koneksi HTTPS ke Midtrans gagal dengan cURL error 60. Gunakan bundle
     * yang tersedia di proyek/Laragon sebagai fallback.
     */
    private function caBundle(): ?string
    {
        $candidates = [
            config('services.midtrans.ca_bundle'),
            storage_path('certs/cacert.pem'),
            'C:\\laragon\\etc\\ssl\\cacert.pem',
            '/etc/ssl/certs/ca-certificates.crt',
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && $candidate !== '' && is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
