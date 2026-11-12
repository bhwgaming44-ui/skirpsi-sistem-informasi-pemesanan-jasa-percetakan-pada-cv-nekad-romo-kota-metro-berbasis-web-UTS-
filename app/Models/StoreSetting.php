<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'nama_perusahaan', 'alamat', 'no_telepon', 'email_kontak', 'logo', 'jam_operasional',
    'instagram', 'deskripsi', 'biaya_kirim',
])]
class StoreSetting extends Model
{
    protected $table = 'pengaturan_toko';

    protected $primaryKey = 'id_pengaturan';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'biaya_kirim' => 'decimal:2',
        ];
    }

    public static function current(): self
    {
        return self::query()->first() ?? new self([
            'nama_perusahaan' => 'CV Nekad Romo Kota Metro',
            'alamat' => 'Jl. Jendral Sudirman No. 45, Kota Metro, Lampung',
            'no_telepon' => '0812-7890-1234',
            'email_kontak' => 'info@nekadromo.com',
            'jam_operasional' => 'Senin - Sabtu, 08.00 - 20.00 WIB',
            'biaya_kirim' => 15000,
        ]);
    }
}
