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
        Schema::table('capaian_kinerja', function (Blueprint $table) {
            // Add status columns if they don't exist
            if (!Schema::hasColumn('capaian_kinerja', 'status_perencanaan')) {
                $table->string('status_perencanaan')->default('draft')->after('persentase');
            }

            if (!Schema::hasColumn('capaian_kinerja', 'status_realisasi')) {
                $table->string('status_realisasi')->default('not_started')->after('status_perencanaan');
            }

            // Add timestamps if they don't exist
            if (!Schema::hasColumn('capaian_kinerja', 'created_at')) {
                $table->timestamps();
            }

            // Add indexes for better performance
            $table->index(['id_program', 'tahun'], 'idx_program_tahun');
            $table->index(['id_kegiatan', 'tahun'], 'idx_kegiatan_tahun');
            $table->index(['id_sub_kegiatan', 'tahun'], 'idx_sub_kegiatan_tahun');
            $table->index('persentase', 'idx_persentase');
            $table->index('tahun', 'idx_tahun');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('capaian_kinerja', function (Blueprint $table) {
            // Drop columns
            $table->dropColumn(['status_perencanaan', 'status_realisasi']);

            // Drop indexes
            $table->dropIndex('idx_program_tahun');
            $table->dropIndex('idx_kegiatan_tahun');
            $table->dropIndex('idx_sub_kegiatan_tahun');
            $table->dropIndex('idx_persentase');
            $table->dropIndex('idx_tahun');
        });
    }
};
