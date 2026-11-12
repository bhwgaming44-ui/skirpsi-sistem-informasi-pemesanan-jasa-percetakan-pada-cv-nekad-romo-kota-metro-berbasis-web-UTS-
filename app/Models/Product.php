<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'id_kategori', 'nama_produk', 'satuan', 'deskripsi', 'gambar',
    'min_order', 'estimasi_hari', 'is_featured', 'is_active',
])]
class Product extends Model
{
    protected $table = 'produk';

    protected $primaryKey = 'id_produk';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'min_order' => 'integer',
            'estimasi_hari' => 'integer',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'id_kategori', 'id_kategori');
    }

    public function bahan(): HasMany
    {
        return $this->hasMany(Material::class, 'id_produk', 'id_produk');
    }

    public function bahanAktif(): HasMany
    {
        return $this->hasMany(Material::class, 'id_produk', 'id_produk')->where('is_active', true);
    }

    public function finishing(): BelongsToMany
    {
        return $this->belongsToMany(Finishing::class, 'produk_finishing', 'id_produk', 'id_finishing')
            ->withPivot('id_produk_finishing')
            ->orderBy('finishing.id_finishing');
    }

    public function galeri(): HasMany
    {
        return $this->hasMany(ProductImage::class, 'id_produk', 'id_produk')
            ->orderBy('urutan')
            ->orderBy('id_gambar');
    }

    #[Scope]
    protected function aktif(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    #[Scope]
    protected function unggulan(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function gambarUrl(): string
    {
        if (blank($this->gambar)) {
            return asset('images/products/default.jpg');
        }

        if (str_starts_with($this->gambar, 'http')) {
            return $this->gambar;
        }

        return asset('images/products/'.$this->gambar);
    }

    public function hargaMulai(): float
    {
        $minimum = $this->bahan->min('harga_satuan');

        return (float) ($minimum ?? 0);
    }
}
