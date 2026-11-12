<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'nama_kategori' => 'Digital Printing Outdoor / Indoor',
                'deskripsi' => 'Cetak media format besar untuk promosi luar dan dalam ruang: banner, spanduk, dan stiker vinyl.',
                'ikon' => 'banner',
            ],
            [
                'nama_kategori' => 'Cetak Promosi & Lembaran',
                'deskripsi' => 'Brosur, kartu nama, poster, dan undangan dengan kualitas cetak offset & digital full color.',
                'ikon' => 'document',
            ],
            [
                'nama_kategori' => 'Merchandise & Sablon Digital',
                'deskripsi' => 'Souvenir dan apparel custom: mug keramik, kaos sablon DTF, dan merchandise promosi lainnya.',
                'ikon' => 'sparkles',
            ],
            [
                'nama_kategori' => 'Cetak Dokumen & Kemasan',
                'deskripsi' => 'Kebutuhan administrasi dan kemasan brand: nota custom, invoice, dan paper bag.',
                'ikon' => 'archive',
            ],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(
                ['nama_kategori' => $category['nama_kategori']],
                $category
            );
        }
    }
}
