<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['id_produk', 'file', 'urutan'])]
class ProductImage extends Model
{
    protected $table = 'gambar_produk';

    protected $primaryKey = 'id_gambar';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
        ];
    }

    public function produk(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'id_produk', 'id_produk');
    }

    public function url(): string
    {
        if (str_starts_with($this->file, 'http')) {
            return $this->file;
        }

        return asset('images/products/galeri/'.$this->file);
    }
}
