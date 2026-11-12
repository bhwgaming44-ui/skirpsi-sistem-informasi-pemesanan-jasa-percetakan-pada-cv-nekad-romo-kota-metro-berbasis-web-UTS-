<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaturan_payment_gateway', function (Blueprint $table) {
            $table->id('id_konfigurasi');
            $table->string('nama_gateway', 50)->default('Midtrans');
            $table->string('server_key');
            $table->string('client_key');
            $table->string('merchant_id', 50)->nullable();
            $table->boolean('is_production')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan_payment_gateway');
    }
};
