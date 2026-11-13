<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'kode_pesanan', 'id_user', 'tgl_pesan', 'total_produk', 'ongkir', 'grand_total',
    'metode_pengambilan', 'alamat_pengiriman', 'status_pembayaran', 'status_pesanan',
    'snap_token', 'metode_pembayaran', 'estimasi_selesai', 'catatan',
])]
class Order extends Model
{
    public const CREATED_AT = 'tgl_pesan';

    public const UPDATED_AT = 'updated_at';

    public const STATUS_MENUNGGU = 'Menunggu Pembayaran';

    public const STATUS_ANTREAN = 'Pesanan Masuk (Antrean)';

    public const STATUS_CETAK = 'Sedang Dicetak';

    public const STATUS_SELESAI_CETAK = 'Selesai Cetak';

    public const STATUS_SIAP = 'Siap Diambil/Dikirim';

    public const STATUS_SELESAI = 'Selesai';

    public const STATUS_BATAL = 'Batal';

    public const TAHAPAN = [
        self::STATUS_MENUNGGU,
        self::STATUS_ANTREAN,
        self::STATUS_CETAK,
        self::STATUS_SELESAI_CETAK,
        self::STATUS_SIAP,
        self::STATUS_SELESAI,
    ];

    protected $table = 'pesanan';

    protected $primaryKey = 'id_pesanan';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tgl_pesan' => 'datetime',
            'estimasi_selesai' => 'date',
            'total_produk' => 'decimal:2',
            'ongkir' => 'decimal:2',
            'grand_total' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function detail(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'id_pesanan', 'id_pesanan');
    }

    public function transaksi(): HasMany
    {
        return $this->hasMany(GatewayTransaction::class, 'id_pesanan', 'id_pesanan');
    }

    public function transaksiTerakhir(): HasOne
    {
        return $this->hasOne(GatewayTransaction::class, 'id_pesanan', 'id_pesanan')->latestOfMany('id_transaksi_gateway');
    }

    public function riwayat(): HasMany
    {
        return $this->hasMany(OrderHistory::class, 'id_pesanan', 'id_pesanan')->orderBy('created_at');
    }

    public function isPaid(): bool
    {
        return $this->status_pembayaran === 'Lunas';
    }

    public function isCancelled(): bool
    {
        return $this->status_pesanan === self::STATUS_BATAL;
    }

    public function canBeCancelledByCustomer(): bool
    {
        return $this->status_pesanan === self::STATUS_MENUNGGU;
    }

    public function getRouteKeyName(): string
    {
        return 'kode_pesanan';
    }

    public function nextStatus(): ?string
    {
        $index = array_search($this->status_pesanan, self::TAHAPAN, true);

        if ($index === false || $index >= count(self::TAHAPAN) - 1) {
            return null;
        }

        return self::TAHAPAN[$index + 1];
    }

    public function statusColor(): string
    {
        return match ($this->status_pesanan) {
            self::STATUS_MENUNGGU => 'amber',
            self::STATUS_ANTREAN => 'sky',
            self::STATUS_CETAK => 'violet',
            self::STATUS_SELESAI_CETAK => 'indigo',
            self::STATUS_SIAP => 'teal',
            self::STATUS_SELESAI => 'emerald',
            default => 'rose',
        };
    }

    public function tahapanAktif(): int
    {
        $index = array_search($this->status_pesanan, self::TAHAPAN, true);

        return $index === false ? 0 : $index;
    }
}
