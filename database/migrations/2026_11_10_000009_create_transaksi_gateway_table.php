<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_gateway', function (Blueprint $table) {
            $table->id('id_transaksi_gateway');
            $table->foreignId('id_pesanan')->constrained('pesanan', 'id_pesanan')->cascadeOnDelete();
            $table->string('order_id_gateway', 50)->unique();
            $table->string('transaction_id_gateway', 100)->nullable();
            $table->string('payment_type', 50)->nullable();
            $table->string('bank_va', 30)->nullable();
            $table->string('va_number', 50)->nullable();
            $table->string('bill_key', 50)->nullable();
            $table->string('biller_code', 50)->nullable();
            $table->text('qr_code_url')->nullable();
            $table->decimal('gross_amount', 12, 2)->default(0);
            $table->enum('transaction_status', ['pending', 'settlement', 'capture', 'deny', 'expire', 'cancel'])->default('pending');
            $table->string('fraud_status', 30)->nullable();
            $table->dateTime('payment_time')->nullable();
            $table->dateTime('settlement_time')->nullable();
            $table->text('raw_response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_gateway');
    }
};
