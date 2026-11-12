# Sistem Informasi Pemesanan Jasa Percetakan CV Nekad Romo Kota Metro

Aplikasi web untuk pemesanan jasa percetakan secara online: pelanggan dapat menghitung estimasi harga, memesan, membayar, dan melacak progres produksi; admin mengelola katalog, pesanan, produksi, pelanggan, dan laporan; pimpinan melihat ringkasan kinerja usaha.

## Teknologi

- **Laravel 13** + **PHP 8.4**
- **MySQL 8**
- **Tailwind CSS v4**, **Vite**, **Alpine.js**
- **ApexCharts**, **SweetAlert2**, **canvas-confetti**
- **Midtrans Snap** (sandbox & production, mode simulasi otomatis bila kunci belum valid)

## Menjalankan di Laragon

1. Pastikan **PHP 8.4** dan **MySQL 8** dari Laragon aktif. Proyek ini menggunakan PHP 8.4 karena Laravel 13 tidak berjalan di PHP 8.1 bawaan Laragon.
2. Buat database `db_percetakan_nekadromo` (utf8mb4).
3. Sesuaikan `.env` bila perlu:

   ```env
   APP_URL=http://localhost:8001
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=db_percetakan_nekadromo
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. Jalankan migrasi dan data demo:

   ```bash
   php artisan migrate --seed
   ```

5. Pasang aset frontend dan build:

   ```bash
   npm install
   npm run build
   ```

6. Jalankan aplikasi:

   ```bash
   php artisan serve
   ```

   Buka `http://localhost:8001`.

## Akun Demo

| Peran | Username | Password |
| --- | --- | --- |
| Admin | `admin` | `admin123` |
| Pimpinan | `pimpinan` | `pimpinan123` |
| Pelanggan | `raihan`, `siti`, `budi`, `dewi`, `agus` | `pelanggan123` |

## Pembayaran (Midtrans)

- Kunci **sandbox** dan **production** sudah terisi otomatis oleh `SettingSeeder`. Mode **Sandbox aktif secara default** agar pengujian tidak menarik dana sungguhan.
- Buka menu **Admin → Pembayaran** untuk:
  - melihat kunci yang tersimpan (disamarkan),
  - mengganti Server Key, Client Key, Signature Secret Key, Merchant ID, dan batas waktu bayar,
  - memilih kanal pembayaran (QRIS, GoPay, ShopeePay, VA BCA/BNI/BRI/Permata/CIMB, Mandiri Bill, Indomaret, Alfamart, kartu kredit, paylater),
  - menjalankan **Tes koneksi** ke Midtrans,
  - mengaktifkan mode Sandbox atau Production dengan satu klik.
- Nama toko pada halaman pembayaran, item Snap, dan QRIS aplikasi adalah **Nekad Romo**. Nama yang tercetak di dalam QRIS mengikuti **Merchant Name** akun Midtrans (`Nekad Romo.Print Kota Metro`).
- Daftarkan URL webhook berikut pada Midtrans Dashboard → Settings → Payment Notification URL (sandbox dan production terpisah):

  ```text
  {APP_URL}/payments/webhook
  ```

  Contoh: `http://localhost:8001/payments/webhook`. Signature webhook diverifikasi dengan SHA-512 menggunakan Server Key.
- Halaman pembayaran memantau status secara otomatis (polling) dan juga menyinkronkan status langsung ke API Midtrans, sehingga pembayaran tetap terverifikasi saat webhook tidak bisa menjangkau localhost.
- Status `refund`, `chargeback`, `expire`, dan `cancel` ditangani otomatis dan dicatat pada riwayat pesanan.
- Jika **Tes koneksi** menampilkan `cURL error 60`, atur `curl.cainfo` dan `openssl.cafile` di `php.ini` Laragon ke `C:\laragon\etc\ssl\cacert.pem`. Aplikasi juga otomatis memakai fallback `storage/certs/cacert.pem`.

## Peran & Halaman Utama

- **Publik**: beranda, katalog dengan pencarian pintar (toleran typo) & filter kategori realtime, detail produk, keranjang, lacak pesanan (`/lacak`).
- **Keranjang banyak produk**: tombol **Pilih banyak produk** di katalog mengaktifkan pilihan produk, **Pilih semua** untuk seluruh hasil filter, lalu **Tambah ke keranjang** menambahkan semuanya sekaligus (bahan pertama, ukuran standar, jumlah minimal).
- **Produk Tersimpan** (`/produk-tersimpan`): simpan produk lewat ikon bookmark pada kartu/detail produk. Tamu disimpan di sesi perangkat, pelanggan login disimpan permanen di tabel `produk_tersimpan`, dan simpanan tamu otomatis digabung ke akun saat masuk atau mendaftar.
- **Pelanggan**: dashboard (`/akun`), riwayat pesanan, profil, invoice.
- **Admin** (`/admin`): dashboard, pesanan, board produksi, produk, kategori, finishing, pelanggan, pembayaran Midtrans, pengaturan, laporan.
- **Pimpinan** (`/pimpinan`): ringkasan KPI, tren pendapatan, produk terlaris, pelanggan teratas, laporan.

## Kelola Produk & Gambar (Admin)

- Menu **Admin → Produk → Tambah produk** mendukung unggah **gambar utama** dan **galeri foto** sekaligus.
- Gambar utama bisa dipilih lewat klik atau **drag & drop**, diganti, atau dihapus. Format yang diterima JPG, PNG, dan WEBP dengan ukuran maksimal 4 MB.
- Galeri mendukung **multi-file** (maksimal 6 file sekali unggah), pratinjau sebelum disimpan, dan penghapusan foto yang sudah tersimpan.
- Setiap file diberi nama unik otomatis agar tidak bentrok. Gambar utama disimpan di `public/images/products`, sedangkan galeri di `public/images/products/galeri`.
- Halaman detail produk menampilkan **thumbnail galeri** yang dapat diklik untuk mengganti gambar utama tanpa memuat ulang halaman.
- Menu **Admin → Use Case & Flowchart** menyajikan diagram use case tiga aktor serta flowchart alur pemesanan dan alur tambah produk dalam tampilan modern, lengkap dengan tombol **Cetak dokumen**.

## Penyimpanan Progres Otomatis

- **Keranjang pelanggan login** disimpan ke tabel `keranjang_pelanggan` setiap kali item ditambah/dihapus, sehingga tetap ada setelah logout, sesi kedaluwarsa, atau login dari perangkat lain.
- **Keranjang tamu** otomatis digabung ke akun saat login atau daftar. Item dengan konfigurasi identik digabung jumlahnya.
- **Draft checkout** (metode pengambilan, alamat kirim, catatan) disimpan otomatis saat diketik dan dipulihkan ketika checkout dibuka kembali.
- **Pesanan yang sudah dibuat** tersimpan permanen; pembayaran yang tertunda bisa dilanjutkan dari dashboard pelanggan.
- Setelah pesanan berhasil dibuat, keranjang dan draft checkout dibersihkan agar tidak dobel.

## Pengujian

```bash
php artisan test --compact
```

## Catatan Proyek

- Skema database awal dan catatan perancangan tetap tersedia di `database.sql` dan `CATATAN_PROYEK.md`.
- Gambar produk berada di `public/images/products` dan galeri di `public/images/products/galeri`; file desain pelanggan disimpan privat di `storage/app/private/desain`.
- Foto produk *Poster / Foto* memakai karya "Poster prints" oleh viviandnguyen_ (CC BY-SA 2.0), sumber: Flickr.
- Format uang disimpan sebagai integer Rupiah dan dihitung ulang di server saat checkout.
