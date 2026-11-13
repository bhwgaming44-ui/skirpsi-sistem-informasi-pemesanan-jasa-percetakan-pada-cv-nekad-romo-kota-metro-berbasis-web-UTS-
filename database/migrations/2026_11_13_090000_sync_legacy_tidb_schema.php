<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menyelaraskan database TiDB lama (hasil import database.sql) dengan schema
     * migrasi Laravel tanpa menghapus tabel, kolom, atau baris yang sudah ada.
     */
    public function up(): void
    {
        $this->createMissingTables();
        $this->addMissingColumns();
        $this->widenStatusColumns();
        $this->addMissingUniqueIndexes();
        $this->backfillTimestamps();
    }

    /**
     * Migrasi ini hanya menambah, sehingga rollback tidak menghapus data lama.
     */
    public function down(): void
    {
        //
    }

    private function createMissingTables(): void
    {
        if (! Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table): void {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        if (! Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table): void {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }

        if (! Schema::hasTable('cache')) {
            Schema::create('cache', function (Blueprint $table): void {
                $table->string('key')->primary();
                $table->mediumText('value');
                $table->bigInteger('expiration')->index();
            });
        }

        if (! Schema::hasTable('cache_locks')) {
            Schema::create('cache_locks', function (Blueprint $table): void {
                $table->string('key')->primary();
                $table->string('owner');
                $table->bigInteger('expiration')->index();
            });
        }

        if (! Schema::hasTable('jobs')) {
            Schema::create('jobs', function (Blueprint $table): void {
                $table->id();
                $table->string('queue')->index();
                $table->longText('payload');
                $table->unsignedSmallInteger('attempts');
                $table->unsignedInteger('reserved_at')->nullable();
                $table->unsignedInteger('available_at');
                $table->unsignedInteger('created_at');
            });
        }

        if (! Schema::hasTable('job_batches')) {
            Schema::create('job_batches', function (Blueprint $table): void {
                $table->string('id')->primary();
                $table->string('name');
                $table->integer('total_jobs');
                $table->integer('pending_jobs');
                $table->integer('failed_jobs');
                $table->longText('failed_job_ids');
                $table->mediumText('options')->nullable();
                $table->integer('cancelled_at')->nullable();
                $table->integer('created_at');
                $table->integer('finished_at')->nullable();
            });
        }

        if (! Schema::hasTable('failed_jobs')) {
            Schema::create('failed_jobs', function (Blueprint $table): void {
                $table->id();
                $table->string('uuid')->unique();
                $table->string('connection');
                $table->string('queue');
                $table->longText('payload');
                $table->longText('exception');
                $table->timestamp('failed_at')->useCurrent();

                $table->index(['connection', 'queue', 'failed_at']);
            });
        }

        if (! Schema::hasTable('produk_tersimpan')) {
            Schema::create('produk_tersimpan', function (Blueprint $table): void {
                $table->id('id_tersimpan');
                $this->addReferenceColumn($table, 'id_user', 'users', 'id_user');
                $this->addReferenceColumn($table, 'id_produk', 'produk', 'id_produk');
                $table->timestamps();

                $table->foreign('id_user')->references('id_user')->on('users')->cascadeOnDelete();
                $table->foreign('id_produk')->references('id_produk')->on('produk')->cascadeOnDelete();
                $table->unique(['id_user', 'id_produk']);
            });
        }

        if (! Schema::hasTable('keranjang_pelanggan')) {
            Schema::create('keranjang_pelanggan', function (Blueprint $table): void {
                $table->id('id_keranjang');
                $this->addReferenceColumn($table, 'id_user', 'users', 'id_user');
                $table->json('items');
                $table->json('checkout_draft')->nullable();
                $table->timestamps();

                $table->foreign('id_user')->references('id_user')->on('users')->cascadeOnDelete();
                $table->unique('id_user');
            });
        }

        if (! Schema::hasTable('gambar_produk')) {
            Schema::create('gambar_produk', function (Blueprint $table): void {
                $table->id('id_gambar');
                $this->addReferenceColumn($table, 'id_produk', 'produk', 'id_produk');
                $table->string('file', 255);
                $table->unsignedSmallInteger('urutan')->default(0);
                $table->timestamps();

                $table->foreign('id_produk')->references('id_produk')->on('produk')->cascadeOnDelete();
                $table->index(['id_produk', 'urutan']);
            });
        }
    }

    /**
     * Menambahkan kolom foreign key dengan tipe yang sama seperti kolom
     * referensinya. Database lama memakai INT, sedangkan instalasi baru
     * memakai BIGINT UNSIGNED, sehingga tipe tidak boleh ditulis statis.
     */
    private function addReferenceColumn(Blueprint $table, string $column, string $referencedTable, string $referencedColumn): void
    {
        $type = $this->columnType($referencedTable, $referencedColumn);

        match (true) {
            str_contains($type, 'bigint') => $table->unsignedBigInteger($column),
            str_contains($type, 'unsigned') => $table->unsignedInteger($column),
            default => $table->integer($column),
        };
    }

    /**
     * Tipe kolom dibaca lewat schema builder agar migrasi ini juga berjalan di
     * SQLite (dipakai test), bukan hanya MySQL/TiDB.
     */
    private function columnType(string $tableName, string $columnName): string
    {
        foreach (Schema::getColumns($tableName) as $column) {
            if (($column['name'] ?? null) !== $columnName) {
                continue;
            }

            return strtolower(trim(
                ((string) ($column['type_name'] ?? '')).' '.((string) ($column['type'] ?? ''))
            ));
        }

        return 'bigint unsigned';
    }

    private function addMissingColumns(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'foto_profil')) {
                $table->string('foto_profil', 255)->nullable();
            }

            if (! Schema::hasColumn('users', 'password_terlihat')) {
                $table->text('password_terlihat')->nullable();
            }

            if (! Schema::hasColumn('users', 'remember_token')) {
                $table->rememberToken();
            }

            if (! Schema::hasColumn('users', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });

        Schema::table('kategori_produk', function (Blueprint $table): void {
            if (! Schema::hasColumn('kategori_produk', 'ikon')) {
                $table->string('ikon', 50)->default('printer');
            }

            if (! Schema::hasColumn('kategori_produk', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }

            if (! Schema::hasColumn('kategori_produk', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });

        Schema::table('produk', function (Blueprint $table): void {
            if (! Schema::hasColumn('produk', 'min_order')) {
                $table->unsignedInteger('min_order')->default(1);
            }

            if (! Schema::hasColumn('produk', 'estimasi_hari')) {
                $table->unsignedTinyInteger('estimasi_hari')->default(1);
            }

            if (! Schema::hasColumn('produk', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }

            if (! Schema::hasColumn('produk', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });

        foreach (['bahan', 'finishing', 'pengaturan_toko'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                if (! Schema::hasColumn($table->getTable(), 'created_at')) {
                    $table->timestamp('created_at')->nullable();
                }

                if (! Schema::hasColumn($table->getTable(), 'updated_at')) {
                    $table->timestamp('updated_at')->nullable();
                }
            });
        }

        Schema::table('pesanan', function (Blueprint $table): void {
            if (! Schema::hasColumn('pesanan', 'estimasi_selesai')) {
                $table->date('estimasi_selesai')->nullable();
            }

            if (! Schema::hasColumn('pesanan', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }

            if (! Schema::hasColumn('pesanan', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });

        Schema::table('detail_pesanan', function (Blueprint $table): void {
            if (! Schema::hasColumn('detail_pesanan', 'nama_produk_snapshot')) {
                $table->string('nama_produk_snapshot', 150)->default('');
            }

            if (! Schema::hasColumn('detail_pesanan', 'nama_bahan_snapshot')) {
                $table->string('nama_bahan_snapshot', 100)->default('');
            }

            if (! Schema::hasColumn('detail_pesanan', 'satuan_snapshot')) {
                $table->enum('satuan_snapshot', ['meter', 'lembar', 'pcs', 'rim'])->default('meter');
            }

            if (! Schema::hasColumn('detail_pesanan', 'nama_file_desain')) {
                $table->string('nama_file_desain', 255)->nullable();
            }

            if (! Schema::hasColumn('detail_pesanan', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }

            if (! Schema::hasColumn('detail_pesanan', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });

        Schema::table('detail_pesanan_finishing', function (Blueprint $table): void {
            if (! Schema::hasColumn('detail_pesanan_finishing', 'nama_finishing_snapshot')) {
                $table->string('nama_finishing_snapshot', 100)->default('');
            }
        });

        Schema::table('pengaturan_payment_gateway', function (Blueprint $table): void {
            if (! Schema::hasColumn('pengaturan_payment_gateway', 'merchant_name')) {
                $table->string('merchant_name', 100)->default('Nekad Romo');
            }

            if (! Schema::hasColumn('pengaturan_payment_gateway', 'signature_key')) {
                $table->string('signature_key', 255)->nullable();
            }

            if (! Schema::hasColumn('pengaturan_payment_gateway', 'enabled_payments')) {
                $table->json('enabled_payments')->nullable();
            }

            if (! Schema::hasColumn('pengaturan_payment_gateway', 'expiry_hours')) {
                $table->unsignedSmallInteger('expiry_hours')->default(24);
            }

            if (! Schema::hasColumn('pengaturan_payment_gateway', 'last_test_status')) {
                $table->string('last_test_status', 30)->nullable();
            }

            if (! Schema::hasColumn('pengaturan_payment_gateway', 'last_tested_at')) {
                $table->timestamp('last_tested_at')->nullable();
            }

            if (! Schema::hasColumn('pengaturan_payment_gateway', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }

            if (! Schema::hasColumn('pengaturan_payment_gateway', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });

        Schema::table('pengaturan_toko', function (Blueprint $table): void {
            if (! Schema::hasColumn('pengaturan_toko', 'jam_operasional')) {
                $table->string('jam_operasional', 100)->default('Senin - Sabtu, 08.00 - 20.00 WIB');
            }

            if (! Schema::hasColumn('pengaturan_toko', 'instagram')) {
                $table->string('instagram', 100)->nullable();
            }

            if (! Schema::hasColumn('pengaturan_toko', 'deskripsi')) {
                $table->text('deskripsi')->nullable();
            }

            if (! Schema::hasColumn('pengaturan_toko', 'biaya_kirim')) {
                $table->decimal('biaya_kirim', 12, 2)->default(15000);
            }
        });
    }

    /**
     * Kolom status pada database lama masih ENUM, sedangkan aplikasi memakai
     * nilai status yang lebih panjang sehingga perlu diperlebar menjadi VARCHAR.
     */
    private function widenStatusColumns(): void
    {
        $columns = [
            ['pesanan', 'status_pembayaran', 'Menunggu Pembayaran'],
            ['transaksi_gateway', 'transaction_status', 'pending'],
        ];

        foreach ($columns as [$tableName, $columnName, $default]) {
            if (! Schema::hasColumn($tableName, $columnName)) {
                continue;
            }

            if (! str_contains($this->columnType($tableName, $columnName), 'enum')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($columnName, $default): void {
                $table->string($columnName, 30)->default($default)->change();
            });
        }
    }

    private function addMissingUniqueIndexes(): void
    {
        if (! Schema::hasTable('produk_finishing')) {
            return;
        }

        $hasDuplicates = DB::table('produk_finishing')
            ->select('id_produk', 'id_finishing')
            ->groupBy('id_produk', 'id_finishing')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($hasDuplicates) {
            return;
        }

        foreach (Schema::getIndexes('produk_finishing') as $index) {
            $columns = array_map(
                fn (array|string $column): string => is_array($column) ? (string) ($column['name'] ?? '') : $column,
                $index['columns'] ?? [],
            );

            if (($index['unique'] ?? false) === true && $columns === ['id_produk', 'id_finishing']) {
                return;
            }
        }

        Schema::table('produk_finishing', function (Blueprint $table): void {
            $table->unique(['id_produk', 'id_finishing']);
        });
    }

    /**
     * Baris lama belum memiliki timestamp; isi dengan tanggal yang tersedia
     * agar pengurutan "terbaru" tidak menghasilkan NULL.
     */
    private function backfillTimestamps(): void
    {
        if (Schema::hasColumn('pesanan', 'created_at')) {
            DB::table('pesanan')->whereNull('created_at')->update([
                'created_at' => DB::raw('tgl_pesan'),
                'updated_at' => DB::raw('tgl_pesan'),
            ]);
        }

        foreach (['produk', 'bahan', 'finishing', 'kategori_produk', 'pengaturan_toko', 'pengaturan_payment_gateway'] as $tableName) {
            if (Schema::hasColumn($tableName, 'created_at')) {
                DB::table($tableName)->whereNull('created_at')->update([
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
};
