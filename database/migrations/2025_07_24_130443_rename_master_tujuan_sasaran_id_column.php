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
        Schema::table('realisasi_tujuan_sasaran', function (Blueprint $table) {
            // Rename kolom dari master_tujuan_sasaran_id ke master_tujuan_sasarans_id
            if (Schema::hasColumn('realisasi_tujuan_sasaran', 'master_tujuan_sasaran_id')) {
                // Drop foreign key constraint dulu jika ada
                $table->dropForeign(['master_tujuan_sasaran_id']);

                // Rename kolom
                $table->renameColumn('master_tujuan_sasaran_id', 'master_tujuan_sasarans_id');

                // Add foreign key constraint dengan nama kolom baru
                $table->foreign('master_tujuan_sasarans_id')->references('id')->on('master_tujuan_sasarans')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('realisasi_tujuan_sasaran', function (Blueprint $table) {
            if (Schema::hasColumn('realisasi_tujuan_sasaran', 'master_tujuan_sasarans_id')) {
                // Drop foreign key constraint
                $table->dropForeign(['master_tujuan_sasarans_id']);

                // Rename kembali ke nama asli
                $table->renameColumn('master_tujuan_sasarans_id', 'master_tujuan_sasaran_id');

                // Add foreign key constraint dengan nama kolom lama
                $table->foreign('master_tujuan_sasaran_id')->references('id')->on('master_tujuan_sasarans')->onDelete('cascade');
            }
        });
    }
};
