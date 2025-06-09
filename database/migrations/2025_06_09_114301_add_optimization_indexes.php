<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan index untuk optimasi query dan mencegah N+1 Problem
     */
    public function up(): void
    {
        // Optimasi untuk tabel program
        Schema::table('program', function (Blueprint $table) {
            if (!$this->indexExists('program', 'idx_bidang_tahun')) {
                $table->index(['organisasi_id', 'tahun'], 'idx_bidang_tahun');
            }
            if (!$this->indexExists('program', 'idx_tahun')) {
                $table->index('tahun', 'idx_tahun');
            }
        });

        // Optimasi untuk tabel kegiatan
        Schema::table('kegiatan', function (Blueprint $table) {
            if (!$this->indexExists('kegiatan', 'idx_program')) {
                $table->index('id_program', 'idx_program');
            }
        });

        // Optimasi untuk tabel sub_kegiatan
        Schema::table('sub_kegiatan', function (Blueprint $table) {
            if (!$this->indexExists('sub_kegiatan', 'idx_kegiatan')) {
                $table->index('id_kegiatan', 'idx_kegiatan');
            }
        });

        // Optimasi untuk tabel bidangs (sesuai nama tabel yang benar)
        Schema::table('bidangs', function (Blueprint $table) {
            if (!$this->indexExists('bidangs', 'idx_nama')) {
                $table->index('nama', 'idx_nama');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('program', function (Blueprint $table) {
            if ($this->indexExists('program', 'idx_bidang_tahun')) {
                $table->dropIndex('idx_bidang_tahun');
            }
        });

        Schema::table('program', function (Blueprint $table) {
            if ($this->indexExists('program', 'idx_tahun')) {
                $table->dropIndex('idx_tahun');
            }
        });

        Schema::table('kegiatan', function (Blueprint $table) {
            if ($this->indexExists('kegiatan', 'idx_program')) {
                $table->dropIndex('idx_program');
            }
        });

        Schema::table('sub_kegiatan', function (Blueprint $table) {
            if ($this->indexExists('sub_kegiatan', 'idx_kegiatan')) {
                $table->dropIndex('idx_kegiatan');
            }
        });

        Schema::table('bidangs', function (Blueprint $table) {
            if ($this->indexExists('bidangs', 'idx_nama')) {
                $table->dropIndex('idx_nama');
            }
        });
    }

    /**
     * Check if index exists using raw SQL query
     */
    private function indexExists(string $table, string $index): bool
    {
        try {
            $database = config('database.connections.mysql.database');
            $result = DB::select("
                SELECT COUNT(*) as count 
                FROM information_schema.statistics 
                WHERE table_schema = ? 
                AND table_name = ? 
                AND index_name = ?
            ", [$database, $table, $index]);

            return $result[0]->count > 0;
        } catch (\Exception $e) {
            // If we can't check, assume index doesn't exist
            return false;
        }
    }
};
