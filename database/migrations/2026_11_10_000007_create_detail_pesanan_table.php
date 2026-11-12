<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_pesanan', function (Blueprint $table) {
            $table->id('id_detail');
            $table->foreignId('id_pesanan')->constrained('pesanan', 'id_pesanan')->cascadeOnDelete();
            $table->foreignId('id_bahan')->constrained('bahan', 'id_bahan')->restrictOnDelete();
            $table->string('nama_produk_snapshot', 150);
            $table->string('nama_bahan_snapshot', 100);
            $table->enum('satuan_snapshot', ['meter', 'lembar', 'pcs', 'rim'])->default('meter');
            $table->decimal('panjang', 8, 2)->default(1);
            $table->decimal('lebar', 8, 2)->default(1);
            $table->unsignedInteger('jumlah')->default(1);
            $table->decimal('harga_bahan_saat_beli', 12, 2)->default(0);
            $table->enum('tipe_desain', ['Punya Desain Sendiri', 'Jasa Desain Nekad Romo'])->default('Punya Desain Sendiri');
            $table->decimal('biaya_desain', 12, 2)->default(0);
            $table->string('file_desain', 255)->nullable();
            $table->string('nama_file_desain', 255)->nullable();
            $table->text('catatan_desain')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_pesanan');
    }
};
