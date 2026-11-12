<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->string('status_pembayaran', 30)->default('Menunggu Pembayaran')->change();
        });
    }

    public function down(): void
    {
        DB::table('pesanan')
            ->whereNotIn('status_pembayaran', ['Menunggu Pembayaran', 'Lunas', 'Kadaluarsa', 'Gagal'])
            ->update(['status_pembayaran' => 'Gagal']);

        Schema::table('pesanan', function (Blueprint $table) {
            $table->enum('status_pembayaran', ['Menunggu Pembayaran', 'Lunas', 'Kadaluarsa', 'Gagal'])
                ->default('Menunggu Pembayaran')
                ->change();
        });
    }
};
