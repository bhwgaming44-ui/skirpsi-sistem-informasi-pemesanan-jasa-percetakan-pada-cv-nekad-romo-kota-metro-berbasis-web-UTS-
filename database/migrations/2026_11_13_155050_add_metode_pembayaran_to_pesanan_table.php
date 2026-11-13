<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menyimpan kanal pembayaran yang dipilih pelanggan (mis. qris, bca_va)
     * sehingga pilihan tersebut tetap tersimpan di database dan bisa diganti.
     */
    public function up(): void
    {
        if (Schema::hasColumn('pesanan', 'metode_pembayaran')) {
            return;
        }

        Schema::table('pesanan', function (Blueprint $table): void {
            $table->string('metode_pembayaran', 30)->nullable()->after('snap_token');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('pesanan', 'metode_pembayaran')) {
            return;
        }

        Schema::table('pesanan', function (Blueprint $table): void {
            $table->dropColumn('metode_pembayaran');
        });
    }
};
