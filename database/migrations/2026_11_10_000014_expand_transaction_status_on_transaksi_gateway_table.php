<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi_gateway', function (Blueprint $table) {
            $table->string('transaction_status', 30)->default('pending')->change();
        });
    }

    public function down(): void
    {
        DB::table('transaksi_gateway')
            ->whereNotIn('transaction_status', ['pending', 'settlement', 'capture', 'deny', 'expire', 'cancel'])
            ->update(['transaction_status' => 'pending']);

        Schema::table('transaksi_gateway', function (Blueprint $table) {
            $table->enum('transaction_status', ['pending', 'settlement', 'capture', 'deny', 'expire', 'cancel'])
                ->default('pending')
                ->change();
        });
    }
};
