<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produk', function (Blueprint $table) {
            $table->id('id_produk');
            $table->foreignId('id_kategori')->constrained('kategori_produk', 'id_kategori')->cascadeOnDelete();
            $table->string('nama_produk', 150);
            $table->enum('satuan', ['meter', 'lembar', 'pcs', 'rim'])->default('meter');
            $table->text('deskripsi')->nullable();
            $table->string('gambar', 255)->nullable();
            $table->unsignedInteger('min_order')->default(1);
            $table->unsignedTinyInteger('estimasi_hari')->default(1);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};
