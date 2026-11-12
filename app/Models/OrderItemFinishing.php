<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['id_detail', 'id_finishing', 'nama_finishing_snapshot', 'biaya_saat_beli'])]
class OrderItemFinishing extends Model
{
    protected $table = 'detail_pesanan_finishing';

    protected $primaryKey = 'id_detail_finishing';

    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'biaya_saat_beli' => 'decimal:2',
        ];
    }

    public function detail(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'id_detail', 'id_detail');
    }

    public function finishing(): BelongsTo
    {
        return $this->belongsTo(Finishing::class, 'id_finishing', 'id_finishing');
    }
}
