<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_pesanan', function (Blueprint $table) {
            $table->id('id_riwayat');
            $table->foreignId('id_pesanan')->constrained('pesanan', 'id_pesanan')->cascadeOnDelete();
            $table->foreignId('id_user')->nullable()->constrained('users', 'id_user')->nullOnDelete();
            $table->string('status_tahapan', 50);
            $table->text('keterangan')->nullable();
            $table->dateTime('created_at')->useCurrent();

            $table->index(['id_pesanan', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_pesanan');
    }
};
