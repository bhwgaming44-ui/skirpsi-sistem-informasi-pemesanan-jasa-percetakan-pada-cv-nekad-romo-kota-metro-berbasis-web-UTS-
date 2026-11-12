-- ========================================================
-- SISTEM INFORMASI PEMESANAN JASA PERCETAKAN 
-- PADA CV NEKAD ROMO KOTA METRO BERBASIS WEB
-- Basis Data: db_percetakan_nekadromo
-- Versi: 3.0 (Arsitektur Pembayaran Otomatis PAYMENT GATEWAY - Midtrans/Snap API)
-- Standar: Normalisasi 1NF - 3NF dengan Definisi Eksplisit Primary Key
-- ========================================================

CREATE DATABASE IF NOT EXISTS `db_percetakan_nekadromo` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_percetakan_nekadromo`;

-- 1. TABEL USERS (Pengguna Sistem: Pelanggan, Admin, Pimpinan)
CREATE TABLE IF NOT EXISTS `users` (
  `id_user` INT(11) NOT NULL AUTO_INCREMENT,
  `nama` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `no_hp` VARCHAR(20) NOT NULL,
  `alamat` TEXT DEFAULT NULL,
  `username` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('pelanggan', 'admin', 'pimpinan') NOT NULL DEFAULT 'pelanggan',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `idx_users_username` (`username`),
  UNIQUE KEY `idx_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. TABEL KATEGORI PRODUK
CREATE TABLE IF NOT EXISTS `kategori_produk` (
  `id_kategori` INT(11) NOT NULL AUTO_INCREMENT,
  `nama_kategori` VARCHAR(100) NOT NULL,
  `deskripsi` TEXT DEFAULT NULL,
  PRIMARY KEY (`id_kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. TABEL PRODUK
CREATE TABLE IF NOT EXISTS `produk` (
  `id_produk` INT(11) NOT NULL AUTO_INCREMENT,
  `id_kategori` INT(11) NOT NULL,
  `nama_produk` VARCHAR(150) NOT NULL,
  `satuan` ENUM('meter', 'lembar', 'pcs', 'rim') NOT NULL DEFAULT 'meter',
  `deskripsi` TEXT DEFAULT NULL,
  `gambar` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id_produk`),
  CONSTRAINT `fk_produk_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori_produk` (`id_kategori`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. TABEL BAHAN CETAK
CREATE TABLE IF NOT EXISTS `bahan` (
  `id_bahan` INT(11) NOT NULL AUTO_INCREMENT,
  `id_produk` INT(11) NOT NULL,
  `nama_bahan` VARCHAR(100) NOT NULL,
  `harga_satuan` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id_bahan`),
  CONSTRAINT `fk_bahan_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. TABEL MASTER FINISHING
CREATE TABLE IF NOT EXISTS `finishing` (
  `id_finishing` INT(11) NOT NULL AUTO_INCREMENT,
  `nama_finishing` VARCHAR(100) NOT NULL,
  `biaya_default` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id_finishing`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. TABEL RELASI PRODUK & FINISHING (M:N)
CREATE TABLE IF NOT EXISTS `produk_finishing` (
  `id_produk_finishing` INT(11) NOT NULL AUTO_INCREMENT,
  `id_produk` INT(11) NOT NULL,
  `id_finishing` INT(11) NOT NULL,
  PRIMARY KEY (`id_produk_finishing`),
  UNIQUE KEY `idx_produk_finishing_unique` (`id_produk`, `id_finishing`),
  CONSTRAINT `fk_pf_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_pf_finishing` FOREIGN KEY (`id_finishing`) REFERENCES `finishing` (`id_finishing`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. TABEL PESANAN (Header Transaksi)
CREATE TABLE IF NOT EXISTS `pesanan` (
  `id_pesanan` INT(11) NOT NULL AUTO_INCREMENT,
  `kode_pesanan` VARCHAR(30) NOT NULL,
  `id_user` INT(11) NOT NULL,
  `tgl_pesan` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `total_produk` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `ongkir` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `grand_total` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `metode_pengambilan` ENUM('Ambil di Toko', 'Kirim Kurir/Ekspedisi') NOT NULL DEFAULT 'Ambil di Toko',
  `alamat_pengiriman` TEXT DEFAULT NULL,
  `status_pembayaran` ENUM('Menunggu Pembayaran', 'Lunas', 'Kadaluarsa', 'Gagal') NOT NULL DEFAULT 'Menunggu Pembayaran',
  `status_pesanan` ENUM('Menunggu Pembayaran', 'Pesanan Masuk (Antrean)', 'Sedang Dicetak', 'Selesai Cetak', 'Siap Diambil/Dikirim', 'Selesai', 'Batal') NOT NULL DEFAULT 'Menunggu Pembayaran',
  `snap_token` VARCHAR(255) DEFAULT NULL,
  `catatan` TEXT DEFAULT NULL,
  PRIMARY KEY (`id_pesanan`),
  UNIQUE KEY `idx_pesanan_kode` (`kode_pesanan`),
  CONSTRAINT `fk_pesanan_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. TABEL DETAIL PESANAN (Item Cetak)
CREATE TABLE IF NOT EXISTS `detail_pesanan` (
  `id_detail` INT(11) NOT NULL AUTO_INCREMENT,
  `id_pesanan` INT(11) NOT NULL,
  `id_bahan` INT(11) NOT NULL,
  `panjang` DECIMAL(8,2) NOT NULL DEFAULT 1.00,
  `lebar` DECIMAL(8,2) NOT NULL DEFAULT 1.00,
  `jumlah` INT(11) NOT NULL DEFAULT 1,
  `harga_bahan_saat_beli` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `tipe_desain` ENUM('Punya Desain Sendiri', 'Jasa Desain Nekad Romo') NOT NULL DEFAULT 'Punya Desain Sendiri',
  `biaya_desain` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `file_desain` VARCHAR(255) DEFAULT NULL,
  `catatan_desain` TEXT DEFAULT NULL,
  `subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id_detail`),
  CONSTRAINT `fk_detail_pesanan` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_detail_bahan` FOREIGN KEY (`id_bahan`) REFERENCES `bahan` (`id_bahan`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. TABEL DETAIL PESANAN FINISHING (Multi Finishing per Item)
CREATE TABLE IF NOT EXISTS `detail_pesanan_finishing` (
  `id_detail_finishing` INT(11) NOT NULL AUTO_INCREMENT,
  `id_detail` INT(11) NOT NULL,
  `id_finishing` INT(11) NOT NULL,
  `biaya_saat_beli` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id_detail_finishing`),
  CONSTRAINT `fk_dpf_detail` FOREIGN KEY (`id_detail`) REFERENCES `detail_pesanan` (`id_detail`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_dpf_finishing` FOREIGN KEY (`id_finishing`) REFERENCES `finishing` (`id_finishing`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. TABEL TRANSAKSI PAYMENT GATEWAY (Integrasi Midtrans / Gateway Otomatis)
CREATE TABLE IF NOT EXISTS `transaksi_gateway` (
  `id_transaksi_gateway` INT(11) NOT NULL AUTO_INCREMENT,
  `id_pesanan` INT(11) NOT NULL,
  `order_id_gateway` VARCHAR(50) NOT NULL,
  `transaction_id_gateway` VARCHAR(100) DEFAULT NULL,
  `payment_type` VARCHAR(50) DEFAULT NULL,
  `bank_va` VARCHAR(30) DEFAULT NULL,
  `va_number` VARCHAR(50) DEFAULT NULL,
  `bill_key` VARCHAR(50) DEFAULT NULL,
  `biller_code` VARCHAR(50) DEFAULT NULL,
  `qr_code_url` TEXT DEFAULT NULL,
  `gross_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `transaction_status` ENUM('pending', 'settlement', 'capture', 'deny', 'expire', 'cancel') NOT NULL DEFAULT 'pending',
  `fraud_status` VARCHAR(30) DEFAULT NULL,
  `payment_time` DATETIME DEFAULT NULL,
  `settlement_time` DATETIME DEFAULT NULL,
  `raw_response` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_transaksi_gateway`),
  UNIQUE KEY `idx_gateway_order_id` (`order_id_gateway`),
  CONSTRAINT `fk_gateway_pesanan` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 11. TABEL RIWAYAT STATUS PESANAN (Audit Trail & Timeline Otomatis)
CREATE TABLE IF NOT EXISTS `riwayat_pesanan` (
  `id_riwayat` INT(11) NOT NULL AUTO_INCREMENT,
  `id_pesanan` INT(11) NOT NULL,
  `id_user` INT(11) DEFAULT NULL,
  `status_tahapan` VARCHAR(50) NOT NULL,
  `keterangan` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_riwayat`),
  CONSTRAINT `fk_riwayat_pesanan` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_riwayat_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 12. TABEL PENGATURAN PAYMENT GATEWAY (Konfigurasi API Key)
CREATE TABLE IF NOT EXISTS `pengaturan_payment_gateway` (
  `id_konfigurasi` INT(11) NOT NULL AUTO_INCREMENT,
  `nama_gateway` VARCHAR(50) NOT NULL DEFAULT 'Midtrans',
  `server_key` VARCHAR(255) NOT NULL,
  `client_key` VARCHAR(255) NOT NULL,
  `merchant_id` VARCHAR(50) DEFAULT NULL,
  `is_production` TINYINT(1) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_konfigurasi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 13. TABEL PENGATURAN TOKO (Profil Instansi CV Nekad Romo)
CREATE TABLE IF NOT EXISTS `pengaturan_toko` (
  `id_pengaturan` INT(11) NOT NULL AUTO_INCREMENT,
  `nama_perusahaan` VARCHAR(150) NOT NULL,
  `alamat` TEXT NOT NULL,
  `no_telepon` VARCHAR(20) NOT NULL,
  `email_kontak` VARCHAR(100) DEFAULT NULL,
  `logo` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id_pengaturan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ========================================================
-- DATA SEEDER AWAL
-- ========================================================

-- Akun Pengguna Default (Password: admin123 / password123)
INSERT INTO `users` (`id_user`, `nama`, `email`, `no_hp`, `alamat`, `username`, `password`, `role`) VALUES
(1, 'Administrator Nekad Romo', 'admin@nekadromo.com', '081234567890', 'Kota Metro, Lampung', 'admin', '$2y$10$wN9aE3zTgbH9c1i2Yj3VSu7m2eP5lG0Fm4uJ8u6m9aV9n8tK4uV.O', 'admin'),
(2, 'Pimpinan CV Nekad Romo', 'pimpinan@nekadromo.com', '081234567891', 'Kota Metro, Lampung', 'pimpinan', '$2y$10$wN9aE3zTgbH9c1i2Yj3VSu7m2eP5lG0Fm4uJ8u6m9aV9n8tK4uV.O', 'pimpinan'),
(3, 'Muhammad Raihan', 'raihan@gmail.com', '085712345678', 'Jl. Ki Hajar Dewantara, Kota Metro', 'raihan', '$2y$10$wN9aE3zTgbH9c1i2Yj3VSu7m2eP5lG0Fm4uJ8u6m9aV9n8tK4uV.O', 'pelanggan');

-- Kategori Produk
INSERT INTO `kategori_produk` (`id_kategori`, `nama_kategori`, `deskripsi`) VALUES
(1, 'Digital Printing Outdoor / Indoor', 'Cetak media format besar seperti banner, spanduk, baliho, dan backdrop'),
(2, 'Cetak Promosi & Lembaran', 'Cetak kertas promosi seperti brosur, pamflet, flyer, poster'),
(3, 'Merchandise & Sablon Digital', 'Cetak mug keramik, stiker cutting/label, dan souvenir');

-- Produk Percetakan
INSERT INTO `produk` (`id_produk`, `id_kategori`, `nama_produk`, `satuan`, `deskripsi`, `gambar`) VALUES
(1, 1, 'Cetak Banner / Spanduk Outdoor', 'meter', 'Layanan cetak spanduk & banner outdoor dengan daya tahan cuaca tinggi', 'banner.jpg'),
(2, 2, 'Cetak Brosur & Leaflet', 'lembar', 'Cetak media promosi kertas full color ukuran A4/A5', 'brosur.jpg'),
(3, 3, 'Cetak Mug Keramik Custom', 'pcs', 'Cetak mug custom full color menggunakan press sublimasi digital', 'mug.jpg');

-- Bahan Cetak
INSERT INTO `bahan` (`id_bahan`, `id_produk`, `nama_bahan`, `harga_satuan`) VALUES
(1, 1, 'Flexi Standar 280 gram', 18000.00),
(2, 1, 'Flexi Tebal 340 gram', 25000.00),
(3, 1, 'Flexi Korea 440 gram', 35000.00),
(4, 2, 'Art Paper 120 gram', 1500.00),
(5, 2, 'Art Paper 150 gram', 2000.00),
(6, 3, 'Mug Keramik Standar Import', 20000.00);

-- Master Finishing
INSERT INTO `finishing` (`id_finishing`, `nama_finishing`, `biaya_default`) VALUES
(1, 'Mata Ayam (Ring Sudut & Tengah)', 2000.00),
(2, 'Lipat Keliling Lem Rapi', 3000.00),
(3, 'Selongsong (Atas - Bawah)', 4000.00),
(4, 'Laminasi Glossy (Mengkilap)', 5000.00),
(5, 'Laminasi Doff (Elegan Matte)', 5000.00),
(6, 'Lipat 3 Brosur (Z-Fold)', 1000.00);

-- Relasi Produk ke Finishing
INSERT INTO `produk_finishing` (`id_produk`, `id_finishing`) VALUES
(1, 1),
(1, 2),
(1, 3),
(2, 4),
(2, 5),
(2, 6);

-- Pengaturan Payment Gateway (Midtrans Sandbox Default untuk Pengujian)
INSERT INTO `pengaturan_payment_gateway` (`id_konfigurasi`, `nama_gateway`, `server_key`, `client_key`, `merchant_id`, `is_production`, `is_active`) VALUES
(1, 'Midtrans Snap', 'SB-Mid-server-DemoKeyPlaceholder12345', 'SB-Mid-client-DemoKeyPlaceholder12345', 'G123456789', 0, 1);

-- Pengaturan Profil CV Nekad Romo
INSERT INTO `pengaturan_toko` (`id_pengaturan`, `nama_perusahaan`, `alamat`, `no_telepon`, `email_kontak`, `logo`) VALUES
(1, 'CV NEKAD ROMO KOTA METRO', 'Jl. Jendral Sudirman No. 45, Kota Metro, Lampung', '081278901234', 'info@nekadromo.com', 'logo.png');
