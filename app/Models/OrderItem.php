<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'id_pesanan', 'id_bahan', 'nama_produk_snapshot', 'nama_bahan_snapshot', 'satuan_snapshot',
    'panjang', 'lebar', 'jumlah', 'harga_bahan_saat_beli', 'tipe_desain', 'biaya_desain',
    'file_desain', 'nama_file_desain', 'catatan_desain', 'subtotal',
])]
class OrderItem extends Model
{
    protected $table = 'detail_pesanan';

    protected $primaryKey = 'id_detail';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'panjang' => 'decimal:2',
            'lebar' => 'decimal:2',
            'jumlah' => 'integer',
            'harga_bahan_saat_beli' => 'decimal:2',
            'biaya_desain' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'id_pesanan', 'id_pesanan');
    }

    public function bahan(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'id_bahan', 'id_bahan');
    }

    public function finishing(): HasMany
    {
        return $this->hasMany(OrderItemFinishing::class, 'id_detail', 'id_detail');
    }

    public function luas(): float
    {
        return round((float) $this->panjang * (float) $this->lebar, 2);
    }

    public function ukuranTeks(): string
    {
        if ($this->satuan_snapshot === 'meter') {
            return rtrim(rtrim(number_format((float) $this->panjang, 2, ',', '.'), '0'), ',').' × '.rtrim(rtrim(number_format((float) $this->lebar, 2, ',', '.'), '0'), ',').' m';
        }

        return $this->jumlah.' '.$this->satuan_snapshot;
    }
}
