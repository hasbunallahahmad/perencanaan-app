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
        Schema::table('kegiatan', function (Blueprint $table) {
            // Hapus unique constraint lama pada kode_program
            $table->dropUnique('kegiatan_kode_kegiatan_unique');

            // Buat unique constraint baru untuk kombinasi kode_program + tahun
            $table->unique(['kode_kegiatan', 'tahun'], 'kegiatan_kode_tahun_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kegiatan', function (Blueprint $table) {
            // Rollback: hapus composite unique dan kembalikan unique pada kode_program
            $table->dropUnique('kegiatan_kode_kegiatan_unique');
            $table->unique('kode_kegiatan', 'kegiatan_kode_tahun_unique');
        });
    }
};
