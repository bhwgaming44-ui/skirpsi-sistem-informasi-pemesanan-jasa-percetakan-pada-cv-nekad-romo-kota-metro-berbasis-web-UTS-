<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_pesanan_finishing', function (Blueprint $table) {
            $table->id('id_detail_finishing');
            $table->foreignId('id_detail')->constrained('detail_pesanan', 'id_detail')->cascadeOnDelete();
            $table->foreignId('id_finishing')->constrained('finishing', 'id_finishing')->restrictOnDelete();
            $table->string('nama_finishing_snapshot', 100);
            $table->decimal('biaya_saat_beli', 12, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_pesanan_finishing');
    }
};
