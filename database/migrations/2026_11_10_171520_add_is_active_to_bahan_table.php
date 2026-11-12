<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('bahan', 'is_active')) {
            Schema::table('bahan', function (Blueprint $table): void {
                $table->boolean('is_active')->default(true)->after('harga_satuan');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('bahan', 'is_active')) {
            Schema::table('bahan', function (Blueprint $table): void {
                $table->dropColumn('is_active');
            });
        }
    }
};
