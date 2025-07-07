<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('realisasi_anggaran_kas', function (Blueprint $table) {
            // Tambah kolom persentase_realisasi jika belum ada
            if (!Schema::hasColumn('realisasi_anggaran_kas', 'persentase_realisasi')) {
                $table->decimal('persentase_realisasi', 5, 2)->default(0)->after('jumlah_realisasi');
            }
        });

        // Tambahkan indexes untuk performa - gunakan try-catch untuk mencegah error jika index sudah ada
        try {
            Schema::table('realisasi_anggaran_kas', function (Blueprint $table) {
                // Index untuk filtering berdasarkan tahun dan triwulan
                $table->index(['tahun', 'triwulan'], 'idx_tahun_triwulan');
            });
        } catch (\Exception $e) {
            // Index mungkin sudah ada, skip
        }

        try {
            Schema::table('realisasi_anggaran_kas', function (Blueprint $table) {
                // Index untuk status filtering
                $table->index('status', 'idx_status');
            });
        } catch (\Exception $e) {
            // Index mungkin sudah ada, skip
        }

        try {
            Schema::table('realisasi_anggaran_kas', function (Blueprint $table) {
                // Index untuk kategori filtering
                $table->index('kategori', 'idx_kategori');
            });
        } catch (\Exception $e) {
            // Index mungkin sudah ada, skip
        }

        try {
            Schema::table('realisasi_anggaran_kas', function (Blueprint $table) {
                // Index untuk tanggal realisasi sorting
                $table->index('tanggal_realisasi', 'idx_tanggal_realisasi');
            });
        } catch (\Exception $e) {
            // Index mungkin sudah ada, skip
        }

        try {
            Schema::table('realisasi_anggaran_kas', function (Blueprint $table) {
                // Index untuk created_at sorting
                $table->index('created_at', 'idx_created_at');
            });
        } catch (\Exception $e) {
            // Index mungkin sudah ada, skip
        }

        // Tambahkan foreign key constraint - gunakan try-catch untuk mencegah error jika FK sudah ada
        try {
            Schema::table('realisasi_anggaran_kas', function (Blueprint $table) {
                $table->foreign('rencana_anggaran_kas_id', 'fk_realisasi_rencana')
                    ->references('id')
                    ->on('rencana_anggaran_kas')
                    ->onDelete('cascade');
            });
        } catch (\Exception $e) {
            // Foreign key mungkin sudah ada, skip
        }

        // Update existing records untuk mengisi persentase_realisasi
        $this->updateExistingPercentages();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('realisasi_anggaran_kas', function (Blueprint $table) {
            // Drop foreign key - gunakan try-catch untuk mencegah error
            try {
                $table->dropForeign('fk_realisasi_rencana');
            } catch (\Exception $e) {
                // Foreign key mungkin tidak ada, skip
            }

            // Drop indexes - gunakan try-catch untuk mencegah error
            try {
                $table->dropIndex('idx_tahun_triwulan');
            } catch (\Exception $e) {
                // Index mungkin tidak ada, skip
            }

            try {
                $table->dropIndex('idx_status');
            } catch (\Exception $e) {
                // Index mungkin tidak ada, skip
            }

            try {
                $table->dropIndex('idx_kategori');
            } catch (\Exception $e) {
                // Index mungkin tidak ada, skip
            }

            try {
                $table->dropIndex('idx_tanggal_realisasi');
            } catch (\Exception $e) {
                // Index mungkin tidak ada, skip
            }

            try {
                $table->dropIndex('idx_created_at');
            } catch (\Exception $e) {
                // Index mungkin tidak ada, skip
            }

            // Drop kolom persentase_realisasi
            if (Schema::hasColumn('realisasi_anggaran_kas', 'persentase_realisasi')) {
                $table->dropColumn('persentase_realisasi');
            }
        });
    }

    /**
     * Update existing records to fill persentase_realisasi
     */
    private function updateExistingPercentages(): void
    {
        // Gunakan DB facade untuk raw SQL
        \Illuminate\Support\Facades\DB::statement("
            UPDATE realisasi_anggaran_kas r
            INNER JOIN rencana_anggaran_kas rk ON r.rencana_anggaran_kas_id = rk.id
            SET r.persentase_realisasi = CASE 
                WHEN rk.jumlah_rencana > 0 THEN ROUND((r.jumlah_realisasi / rk.jumlah_rencana) * 100, 2)
                ELSE 0
            END
            WHERE r.persentase_realisasi = 0
        ");
    }
};
