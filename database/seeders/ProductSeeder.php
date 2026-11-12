<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::query()->pluck('id_kategori', 'nama_kategori');

        $products = [
            [
                'kategori' => 'Digital Printing Outdoor / Indoor',
                'nama_produk' => 'Cetak Banner / Spanduk Outdoor',
                'satuan' => 'meter',
                'deskripsi' => 'Banner outdoor full color tahan cuaca dengan finishing presisi. Cocok untuk spanduk toko, event, dan promo musiman.',
                'gambar' => 'banner.jpg',
                'min_order' => 1,
                'estimasi_hari' => 1,
                'is_featured' => true,
                'bahan' => [
                    ['nama_bahan' => 'Flexi Standar 280 gram', 'harga_satuan' => 18000],
                    ['nama_bahan' => 'Flexi Tebal 340 gram', 'harga_satuan' => 25000],
                    ['nama_bahan' => 'Flexi Korea 440 gram', 'harga_satuan' => 35000],
                ],
                'finishing' => [1, 2, 3],
            ],
            [
                'kategori' => 'Digital Printing Outdoor / Indoor',
                'nama_produk' => 'Stiker Vinyl Custom',
                'satuan' => 'lembar',
                'deskripsi' => 'Stiker vinyl cutting / printing untuk kemasan produk, label, dan branding kendaraan. Tahan air dan sinar matahari.',
                'gambar' => 'stiker.jpg',
                'min_order' => 10,
                'estimasi_hari' => 1,
                'is_featured' => true,
                'bahan' => [
                    ['nama_bahan' => 'Vinyl Ritrama', 'harga_satuan' => 10000],
                    ['nama_bahan' => 'Vinyl Transparan', 'harga_satuan' => 12000],
                    ['nama_bahan' => 'Sticker Oneway', 'harga_satuan' => 15000],
                ],
                'finishing' => [4, 5],
            ],
            [
                'kategori' => 'Cetak Promosi & Lembaran',
                'nama_produk' => 'Cetak Brosur & Leaflet',
                'satuan' => 'lembar',
                'deskripsi' => 'Brosur lipat full color untuk promosi usaha, sekolah, dan event. Tersedia beberapa pilihan ketebalan kertas.',
                'gambar' => 'brosur.jpg',
                'min_order' => 50,
                'estimasi_hari' => 2,
                'is_featured' => true,
                'bahan' => [
                    ['nama_bahan' => 'Art Paper 120 gram', 'harga_satuan' => 1500],
                    ['nama_bahan' => 'Art Paper 150 gram', 'harga_satuan' => 2000],
                    ['nama_bahan' => 'Matt Paper 150 gram', 'harga_satuan' => 2200],
                ],
                'finishing' => [4, 5, 6],
            ],
            [
                'kategori' => 'Cetak Promosi & Lembaran',
                'nama_produk' => 'Kartu Nama Premium',
                'satuan' => 'pcs',
                'deskripsi' => 'Kartu nama profesional dengan cetak tajam dan pilihan laminasi mewah. Minimal pemesanan 100 pcs.',
                'gambar' => 'kartu-nama.jpg',
                'min_order' => 100,
                'estimasi_hari' => 2,
                'is_featured' => false,
                'bahan' => [
                    ['nama_bahan' => 'Art Carton 260 gram', 'harga_satuan' => 700],
                    ['nama_bahan' => 'Art Carton 310 gram', 'harga_satuan' => 900],
                    ['nama_bahan' => 'Concorde 1000', 'harga_satuan' => 1000],
                ],
                'finishing' => [4, 5, 8],
            ],
            [
                'kategori' => 'Cetak Promosi & Lembaran',
                'nama_produk' => 'Undangan Custom',
                'satuan' => 'pcs',
                'deskripsi' => 'Undangan pernikahan dan acara custom dengan beragam bahan elegan, cocok untuk momen spesial Anda.',
                'gambar' => 'undangan.jpg',
                'min_order' => 50,
                'estimasi_hari' => 4,
                'is_featured' => true,
                'bahan' => [
                    ['nama_bahan' => 'Art Carton 230 gram', 'harga_satuan' => 2500],
                    ['nama_bahan' => 'Linen 300 gram', 'harga_satuan' => 3500],
                    ['nama_bahan' => 'Jasmine 400 gram', 'harga_satuan' => 4000],
                ],
                'finishing' => [4, 5, 8, 10],
            ],
            [
                'kategori' => 'Cetak Promosi & Lembaran',
                'nama_produk' => 'Poster / Foto',
                'satuan' => 'lembar',
                'deskripsi' => 'Cetak poster dan foto kualitas tinggi untuk dekorasi, pameran, maupun kebutuhan promosi indoor.',
                'gambar' => 'poster.jpg',
                'min_order' => 1,
                'estimasi_hari' => 1,
                'is_featured' => false,
                'bahan' => [
                    ['nama_bahan' => 'Foto Paper Glossy', 'harga_satuan' => 12000],
                    ['nama_bahan' => 'Art Paper 150 gram', 'harga_satuan' => 8000],
                    ['nama_bahan' => 'Synthetic Paper', 'harga_satuan' => 20000],
                ],
                'finishing' => [4, 5],
            ],
            [
                'kategori' => 'Merchandise & Sablon Digital',
                'nama_produk' => 'Mug Custom Sublim',
                'satuan' => 'pcs',
                'deskripsi' => 'Mug keramik custom full color, cocok untuk souvenir, hadiah, dan merchandise perusahaan.',
                'gambar' => 'mug.jpg',
                'min_order' => 12,
                'estimasi_hari' => 2,
                'is_featured' => true,
                'bahan' => [
                    ['nama_bahan' => 'Mug Keramik Standar', 'harga_satuan' => 20000],
                    ['nama_bahan' => 'Mug Premium Coating', 'harga_satuan' => 28000],
                    ['nama_bahan' => 'Mug Magic Color Change', 'harga_satuan' => 45000],
                ],
                'finishing' => [10],
            ],
            [
                'kategori' => 'Merchandise & Sablon Digital',
                'nama_produk' => 'Kaos Sablon DTF',
                'satuan' => 'pcs',
                'deskripsi' => 'Kaos custom sablon DTF full color, nyaman dipakai dan cocok untuk seragam komunitas maupun event.',
                'gambar' => 'kaos.jpg',
                'min_order' => 6,
                'estimasi_hari' => 3,
                'is_featured' => true,
                'bahan' => [
                    ['nama_bahan' => 'Cotton Combed 24s', 'harga_satuan' => 45000],
                    ['nama_bahan' => 'Cotton Combed 30s', 'harga_satuan' => 55000],
                    ['nama_bahan' => 'Dry Fit Sport', 'harga_satuan' => 65000],
                ],
                'finishing' => [11],
            ],
            [
                'kategori' => 'Cetak Dokumen & Kemasan',
                'nama_produk' => 'Nota / Invoice Custom',
                'satuan' => 'rim',
                'deskripsi' => 'Cetak nota dan invoice usaha dengan nomor urut, rangkap, dan desain profesional.',
                'gambar' => 'nota.jpg',
                'min_order' => 1,
                'estimasi_hari' => 2,
                'is_featured' => false,
                'bahan' => [
                    ['nama_bahan' => 'HVS 70 gram', 'harga_satuan' => 85000],
                    ['nama_bahan' => 'HVS 80 gram', 'harga_satuan' => 95000],
                    ['nama_bahan' => 'NCR 2 Ply', 'harga_satuan' => 175000],
                ],
                'finishing' => [7, 9],
            ],
            [
                'kategori' => 'Cetak Dokumen & Kemasan',
                'nama_produk' => 'Paper Bag Custom',
                'satuan' => 'pcs',
                'deskripsi' => 'Paper bag branded untuk kemasan produk dan souvenir. Tersedia bahan kraft, ivory, dan art paper.',
                'gambar' => 'paperbag.jpg',
                'min_order' => 50,
                'estimasi_hari' => 3,
                'is_featured' => false,
                'bahan' => [
                    ['nama_bahan' => 'Kraft 150 gram', 'harga_satuan' => 3500],
                    ['nama_bahan' => 'Ivory 210 gram', 'harga_satuan' => 4500],
                    ['nama_bahan' => 'Art Paper 250 gram', 'harga_satuan' => 5000],
                ],
                'finishing' => [4, 5, 9, 10],
            ],
        ];

        foreach ($products as $data) {
            $product = Product::query()->updateOrCreate(
                ['nama_produk' => $data['nama_produk']],
                [
                    'id_kategori' => $categories[$data['kategori']],
                    'satuan' => $data['satuan'],
                    'deskripsi' => $data['deskripsi'],
                    'gambar' => $data['gambar'],
                    'min_order' => $data['min_order'],
                    'estimasi_hari' => $data['estimasi_hari'],
                    'is_featured' => $data['is_featured'],
                    'is_active' => true,
                ]
            );

            foreach ($data['bahan'] as $material) {
                $product->bahan()->updateOrCreate(
                    ['nama_bahan' => $material['nama_bahan']],
                    ['harga_satuan' => $material['harga_satuan'], 'is_active' => true]
                );
            }

            $product->finishing()->sync($data['finishing']);
        }
    }
}
