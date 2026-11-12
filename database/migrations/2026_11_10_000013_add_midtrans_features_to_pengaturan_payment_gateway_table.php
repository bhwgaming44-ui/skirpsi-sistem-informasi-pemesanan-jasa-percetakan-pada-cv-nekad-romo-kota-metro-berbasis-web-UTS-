<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengaturan_payment_gateway', function (Blueprint $table) {
            $table->string('merchant_name', 100)->default('Nekad Romo')->after('nama_gateway');
            $table->string('signature_key', 255)->nullable()->after('client_key');
            $table->json('enabled_payments')->nullable()->after('signature_key');
            $table->unsignedSmallInteger('expiry_hours')->default(24)->after('enabled_payments');
            $table->string('last_test_status', 30)->nullable();
            $table->timestamp('last_tested_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pengaturan_payment_gateway', function (Blueprint $table) {
            $table->dropColumn([
                'merchant_name',
                'signature_key',
                'enabled_payments',
                'expiry_hours',
                'last_test_status',
                'last_tested_at',
            ]);
        });
    }
};
