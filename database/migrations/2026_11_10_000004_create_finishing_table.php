<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finishing', function (Blueprint $table) {
            $table->id('id_finishing');
            $table->string('nama_finishing', 100);
            $table->decimal('biaya_default', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finishing');
    }
};
