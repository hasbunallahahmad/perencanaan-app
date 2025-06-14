<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rencana_aksi', function (Blueprint $table) {
            $table->id();

            // bidang_id references bidangs.id (bigint(20) unsigned)
            $table->unsignedBigInteger('bidang_id');
            $table->foreign('bidang_id')->references('id')->on('bidangs')->onDelete('cascade');

            // id_program references program.id_program (bigint(20) unsigned)
            $table->unsignedBigInteger('id_program');
            $table->foreign('id_program')->references('id_program')->on('program')->onDelete('cascade');

            // id_kegiatan references kegiatan.id_kegiatan (bigint(20) unsigned)
            $table->unsignedBigInteger('id_kegiatan');
            $table->foreign('id_kegiatan')->references('id_kegiatan')->on('kegiatan')->onDelete('cascade');

            // id_sub_kegiatan references sub_kegiatan.id_sub_kegiatan (bigint(20) unsigned)
            $table->unsignedBigInteger('id_sub_kegiatan');
            $table->foreign('id_sub_kegiatan')->references('id_sub_kegiatan')->on('sub_kegiatan')->onDelete('cascade');

            // Data Rencana Aksi (array JSON untuk multiple aksi)
            $table->json('rencana_aksi_list');

            // Jenis Anggaran
            $table->enum('jenis_anggaran', ['APBD', 'APBN', 'DAK', 'DBHCHT', 'BANKEU']);

            // Narasumber
            $table->text('narasumber');

            // Rencana Pelaksanaan 12 bulan (JSON array)
            $table->json('rencana_pelaksanaan');

            $table->timestamps();

            // Add indexes for better performance
            $table->index(['bidang_id', 'id_program']);
            $table->index(['id_kegiatan', 'id_sub_kegiatan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rencana_aksi');
    }
};
