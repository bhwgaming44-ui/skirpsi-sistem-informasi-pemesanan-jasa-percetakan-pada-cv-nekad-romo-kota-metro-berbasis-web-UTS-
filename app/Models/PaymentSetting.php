<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

#[Fillable([
    'nama_gateway', 'merchant_name', 'server_key', 'client_key', 'signature_key',
    'enabled_payments', 'expiry_hours', 'merchant_id', 'is_production', 'is_active',
    'last_test_status', 'last_tested_at',
])]
#[Hidden(['server_key', 'signature_key'])]
class PaymentSetting extends Model
{
    protected $table = 'pengaturan_payment_gateway';

    protected $primaryKey = 'id_konfigurasi';

    /**
     * Kanal pembayaran Midtrans yang didukung aplikasi.
     *
     * @var array<string, array{label: string, group: string, icon: string, note: string}>
     */
    public const PAYMENT_CHANNELS = [
        'qris' => ['label' => 'QRIS', 'group' => 'QRIS & E-Wallet', 'icon' => 'qr', 'note' => 'Semua bank & e-wallet'],
        'other_qris' => ['label' => 'QRIS Lainnya', 'group' => 'QRIS & E-Wallet', 'icon' => 'qr', 'note' => 'Aplikasi QRIS lain'],
        'gopay' => ['label' => 'GoPay', 'group' => 'QRIS & E-Wallet', 'icon' => 'wallet', 'note' => 'Saldo & GoPay Later'],
        'shopeepay' => ['label' => 'ShopeePay', 'group' => 'QRIS & E-Wallet', 'icon' => 'wallet', 'note' => 'Saldo ShopeePay'],
        'bca_va' => ['label' => 'Virtual Account BCA', 'group' => 'Virtual Account', 'icon' => 'credit-card', 'note' => 'Verifikasi otomatis'],
        'bni_va' => ['label' => 'Virtual Account BNI', 'group' => 'Virtual Account', 'icon' => 'credit-card', 'note' => 'Verifikasi otomatis'],
        'bri_va' => ['label' => 'Virtual Account BRI', 'group' => 'Virtual Account', 'icon' => 'credit-card', 'note' => 'Verifikasi otomatis'],
        'permata_va' => ['label' => 'Virtual Account Permata', 'group' => 'Virtual Account', 'icon' => 'credit-card', 'note' => 'Verifikasi otomatis'],
        'cimb_va' => ['label' => 'Virtual Account CIMB', 'group' => 'Virtual Account', 'icon' => 'credit-card', 'note' => 'Verifikasi otomatis'],
        'other_va' => ['label' => 'Virtual Account Lainnya', 'group' => 'Virtual Account', 'icon' => 'credit-card', 'note' => 'Bank lain'],
        'echannel' => ['label' => 'Mandiri Bill', 'group' => 'Virtual Account', 'icon' => 'receipt', 'note' => 'Kode biller Mandiri'],
        'indomaret' => ['label' => 'Indomaret', 'group' => 'Gerai Retail', 'icon' => 'archive', 'note' => 'Bayar di kasir'],
        'alfamart' => ['label' => 'Alfamart', 'group' => 'Gerai Retail', 'icon' => 'archive', 'note' => 'Bayar di kasir'],
        'credit_card' => ['label' => 'Kartu Kredit / Debit', 'group' => 'Kartu', 'icon' => 'credit-card', 'note' => 'Visa, Mastercard, JCB'],
        'akulaku' => ['label' => 'Akulaku', 'group' => 'Paylater', 'icon' => 'wallet-2', 'note' => 'Cicilan paylater'],
        'kredivo' => ['label' => 'Kredivo', 'group' => 'Paylater', 'icon' => 'wallet-2', 'note' => 'Cicilan paylater'],
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_production' => 'boolean',
            'is_active' => 'boolean',
            'enabled_payments' => 'array',
            'expiry_hours' => 'integer',
            'last_tested_at' => 'datetime',
        ];
    }

    public static function active(): ?self
    {
        return self::query()->where('is_active', true)->latest('id_konfigurasi')->first();
    }

    /**
     * @return Collection<int, self>
     */
    public static function allModes(): Collection
    {
        return self::query()
            ->orderBy('is_production')
            ->orderBy('id_konfigurasi')
            ->get();
    }

    public function isSandbox(): bool
    {
        return ! $this->is_production;
    }

    public function modeLabel(): string
    {
        return $this->is_production ? 'Production' : 'Sandbox';
    }

    public function displayName(): string
    {
        return filled($this->merchant_name) ? $this->merchant_name : 'Nekad Romo';
    }

    /**
     * Kunci harus cocok dengan lingkungan yang dipilih. Kunci sandbox diawali
     * "SB-Mid-server-", sedangkan kunci production diawali "Mid-server-".
     */
    public function keyMatchesEnvironment(): bool
    {
        if (blank($this->server_key)) {
            return false;
        }

        return $this->is_production
            ? str_starts_with($this->server_key, 'Mid-server-')
            : str_starts_with($this->server_key, 'SB-Mid-server-');
    }

    public function isConfigured(): bool
    {
        return $this->keyMatchesEnvironment()
            && filled($this->client_key)
            && ! str_contains((string) $this->server_key, 'Placeholder')
            && ! str_contains((string) $this->server_key, 'Demo')
            && ! str_contains((string) $this->client_key, 'Placeholder');
    }

    public function hasSignatureKey(): bool
    {
        return filled($this->signature_key);
    }

    public function isConnectionVerified(): bool
    {
        return $this->last_test_status === 'ok';
    }

    public function connectionTone(): string
    {
        return match ($this->last_test_status) {
            'ok' => 'emerald',
            'failed' => 'rose',
            default => 'slate',
        };
    }

    /**
     * @return list<string>
     */
    public function paymentChannels(): array
    {
        $enabled = is_array($this->enabled_payments) ? $this->enabled_payments : [];

        $channels = array_values(array_filter(
            $enabled,
            fn (mixed $channel): bool => is_string($channel) && array_key_exists($channel, self::PAYMENT_CHANNELS)
        ));

        return $channels === [] ? self::defaultChannels() : $channels;
    }

    /**
     * @return list<string>
     */
    public static function defaultChannels(): array
    {
        return ['qris', 'other_qris', 'gopay', 'shopeepay', 'bca_va', 'bni_va', 'bri_va', 'permata_va', 'indomaret', 'alfamart'];
    }

    public function snapBaseUrl(): string
    {
        return $this->is_production
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    }

    public function apiBaseUrl(): string
    {
        return $this->is_production
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    }

    public function apiStatusUrl(string $orderId): string
    {
        $base = $this->is_production
            ? 'https://api.midtrans.com/v2/'
            : 'https://api.sandbox.midtrans.com/v2/';

        return $base.rawurlencode($orderId).'/status';
    }

    public function webhookUrl(): string
    {
        return route('payments.webhook');
    }

    public function activate(): void
    {
        DB::transaction(function (): void {
            self::query()->whereKeyNot($this->getKey())->update(['is_active' => false]);
            $this->forceFill(['is_active' => true])->save();
        });
    }

    public function maskedServerKey(): string
    {
        return $this->mask($this->server_key);
    }

    public function maskedClientKey(): string
    {
        return $this->mask($this->client_key);
    }

    public function maskedSignatureKey(): string
    {
        return $this->mask($this->signature_key);
    }

    private function mask(?string $value): string
    {
        if (blank($value)) {
            return 'Belum diatur';
        }

        if (mb_strlen($value) <= 18) {
            return mb_substr($value, 0, 6).str_repeat('•', 8);
        }

        return mb_substr($value, 0, 12).str_repeat('•', 10).mb_substr($value, -4);
    }
}
