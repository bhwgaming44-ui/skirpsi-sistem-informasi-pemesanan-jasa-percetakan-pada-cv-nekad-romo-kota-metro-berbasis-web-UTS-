<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produk_finishing', function (Blueprint $table) {
            $table->id('id_produk_finishing');
            $table->foreignId('id_produk')->constrained('produk', 'id_produk')->cascadeOnDelete();
            $table->foreignId('id_finishing')->constrained('finishing', 'id_finishing')->cascadeOnDelete();
            $table->unique(['id_produk', 'id_finishing']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produk_finishing');
    }
};
