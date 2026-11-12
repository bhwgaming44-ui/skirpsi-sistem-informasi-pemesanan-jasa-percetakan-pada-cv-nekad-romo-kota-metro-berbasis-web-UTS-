<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gambar_produk', function (Blueprint $table) {
            $table->id('id_gambar');
            $table->foreignId('id_produk')->constrained('produk', 'id_produk')->cascadeOnDelete();
            $table->string('file', 255);
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();

            $table->index(['id_produk', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gambar_produk');
    }
};
