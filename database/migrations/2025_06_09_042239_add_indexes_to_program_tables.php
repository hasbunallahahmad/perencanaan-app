<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('program', function (Blueprint $table) {
            // Index untuk filter dan sorting yang sering digunakan
            $table->index(['tahun', 'kode_program'], 'idx_program_tahun_kode');
            $table->index(['organisasi_id'], 'idx_program_organisasi');
            $table->index(['kode_program'], 'idx_program_kode');
            $table->index(['created_at'], 'idx_program_created');
        });

        Schema::table('kegiatan', function (Blueprint $table) {
            $table->index(['id_program'], 'idx_kegiatan_program');
        });

        Schema::table('sub_kegiatan', function (Blueprint $table) {
            $table->index(['id_kegiatan'], 'idx_sub_kegiatan_kegiatan');
            $table->index(['anggaran'], 'idx_sub_kegiatan_anggaran');
            $table->index(['realisasi'], 'idx_sub_kegiatan_realisasi');
        });
    }

    public function down()
    {
        Schema::table('program', function (Blueprint $table) {
            $table->dropIndex('idx_program_tahun_kode');
            $table->dropIndex('idx_program_organisasi');
            $table->dropIndex('idx_program_kode');
            $table->dropIndex('idx_program_created');
        });

        Schema::table('kegiatan', function (Blueprint $table) {
            $table->dropIndex('idx_kegiatan_program');
        });

        Schema::table('sub_kegiatan', function (Blueprint $table) {
            $table->dropIndex('idx_sub_kegiatan_kegiatan');
            $table->dropIndex('idx_sub_kegiatan_anggaran');
            $table->dropIndex('idx_sub_kegiatan_realisasi');
        });
    }
};
