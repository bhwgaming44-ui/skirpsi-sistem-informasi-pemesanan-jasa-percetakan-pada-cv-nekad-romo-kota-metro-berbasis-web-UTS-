<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'id_pesanan', 'order_id_gateway', 'transaction_id_gateway', 'payment_type', 'bank_va',
    'va_number', 'bill_key', 'biller_code', 'qr_code_url', 'gross_amount',
    'transaction_status', 'fraud_status', 'payment_time', 'settlement_time', 'raw_response',
])]
#[Hidden(['raw_response'])]
class GatewayTransaction extends Model
{
    protected $table = 'transaksi_gateway';

    protected $primaryKey = 'id_transaksi_gateway';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gross_amount' => 'decimal:2',
            'payment_time' => 'datetime',
            'settlement_time' => 'datetime',
        ];
    }

    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'id_pesanan', 'id_pesanan');
    }

    public function isSettled(): bool
    {
        return in_array($this->transaction_status, ['settlement', 'capture'], true);
    }
}
