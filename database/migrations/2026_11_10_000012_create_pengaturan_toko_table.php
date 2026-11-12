<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaturan_toko', function (Blueprint $table) {
            $table->id('id_pengaturan');
            $table->string('nama_perusahaan', 150);
            $table->text('alamat');
            $table->string('no_telepon', 20);
            $table->string('email_kontak', 100)->nullable();
            $table->string('logo', 255)->nullable();
            $table->string('jam_operasional', 100)->default('Senin - Sabtu, 08.00 - 20.00 WIB');
            $table->string('instagram', 100)->nullable();
            $table->text('deskripsi')->nullable();
            $table->decimal('biaya_kirim', 12, 2)->default(15000);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan_toko');
    }
};
