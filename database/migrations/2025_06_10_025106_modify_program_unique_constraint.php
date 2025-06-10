<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('program', function (Blueprint $table) {
            // Hapus unique constraint lama pada kode_program
            $table->dropUnique('program_kode_program_unique');

            // Buat unique constraint baru untuk kombinasi kode_program + tahun
            $table->unique(['kode_program', 'tahun'], 'program_kode_tahun_unique');
        });
    }

    public function down()
    {
        Schema::table('program', function (Blueprint $table) {
            // Rollback: hapus composite unique dan kembalikan unique pada kode_program
            $table->dropUnique('program_kode_tahun_unique');
            $table->unique('kode_program', 'program_kode_program_unique');
        });
    }
};
