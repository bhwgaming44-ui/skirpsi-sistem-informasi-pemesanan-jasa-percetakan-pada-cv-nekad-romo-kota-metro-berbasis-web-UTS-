<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nama_kategori', 'deskripsi', 'ikon'])]
class Category extends Model
{
    protected $table = 'kategori_produk';

    protected $primaryKey = 'id_kategori';

    public function produk(): HasMany
    {
        return $this->hasMany(Product::class, 'id_kategori', 'id_kategori');
    }
}
