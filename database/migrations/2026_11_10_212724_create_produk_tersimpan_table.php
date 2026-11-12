<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produk_tersimpan', function (Blueprint $table) {
            $table->id('id_tersimpan');
            $table->foreignId('id_user')->constrained('users', 'id_user')->cascadeOnDelete();
            $table->foreignId('id_produk')->constrained('produk', 'id_produk')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['id_user', 'id_produk']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produk_tersimpan');
    }
};
