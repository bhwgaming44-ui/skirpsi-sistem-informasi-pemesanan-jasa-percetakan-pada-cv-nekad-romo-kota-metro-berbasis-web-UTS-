<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['nama_finishing', 'biaya_default'])]
class Finishing extends Model
{
    protected $table = 'finishing';

    protected $primaryKey = 'id_finishing';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'biaya_default' => 'decimal:2',
        ];
    }

    public function produk(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'produk_finishing', 'id_finishing', 'id_produk');
    }
}
