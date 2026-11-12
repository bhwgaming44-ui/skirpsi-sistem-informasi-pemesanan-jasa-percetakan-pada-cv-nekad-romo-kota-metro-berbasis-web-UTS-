<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesanan', function (Blueprint $table) {
            $table->id('id_pesanan');
            $table->string('kode_pesanan', 30)->unique();
            $table->foreignId('id_user')->constrained('users', 'id_user')->restrictOnDelete();
            $table->dateTime('tgl_pesan');
            $table->decimal('total_produk', 12, 2)->default(0);
            $table->decimal('ongkir', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->enum('metode_pengambilan', ['Ambil di Toko', 'Kirim Kurir/Ekspedisi'])->default('Ambil di Toko');
            $table->text('alamat_pengiriman')->nullable();
            $table->enum('status_pembayaran', ['Menunggu Pembayaran', 'Lunas', 'Kadaluarsa', 'Gagal'])->default('Menunggu Pembayaran');
            $table->enum('status_pesanan', ['Menunggu Pembayaran', 'Pesanan Masuk (Antrean)', 'Sedang Dicetak', 'Selesai Cetak', 'Siap Diambil/Dikirim', 'Selesai', 'Batal'])->default('Menunggu Pembayaran');
            $table->string('snap_token', 255)->nullable();
            $table->date('estimasi_selesai')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['status_pesanan', 'tgl_pesan']);
            $table->index(['status_pembayaran', 'tgl_pesan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesanan');
    }
};
