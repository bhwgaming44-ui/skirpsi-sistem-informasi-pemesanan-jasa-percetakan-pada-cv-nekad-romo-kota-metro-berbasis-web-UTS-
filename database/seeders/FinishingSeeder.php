<?php

namespace Database\Seeders;

use App\Models\Finishing;
use Illuminate\Database\Seeder;

class FinishingSeeder extends Seeder
{
    public function run(): void
    {
        $finishings = [
            ['nama_finishing' => 'Mata Ayam (Ring Sudut & Tengah)', 'biaya_default' => 2000],
            ['nama_finishing' => 'Lipat Keliling Lem Rapi', 'biaya_default' => 3000],
            ['nama_finishing' => 'Selongsong (Atas - Bawah)', 'biaya_default' => 4000],
            ['nama_finishing' => 'Laminasi Glossy (Mengkilap)', 'biaya_default' => 5000],
            ['nama_finishing' => 'Laminasi Doff (Elegan Matte)', 'biaya_default' => 5000],
            ['nama_finishing' => 'Lipat 3 Brosur (Z-Fold)', 'biaya_default' => 1000],
            ['nama_finishing' => 'Jilid Spiral', 'biaya_default' => 8000],
            ['nama_finishing' => 'Spot UV', 'biaya_default' => 6000],
            ['nama_finishing' => 'Lem / Perekat', 'biaya_default' => 1500],
            ['nama_finishing' => 'Sablon Emas', 'biaya_default' => 7000],
            ['nama_finishing' => 'Sablon Full Color', 'biaya_default' => 15000],
        ];

        foreach ($finishings as $finishing) {
            Finishing::query()->updateOrCreate(
                ['nama_finishing' => $finishing['nama_finishing']],
                $finishing
            );
        }
    }
}
