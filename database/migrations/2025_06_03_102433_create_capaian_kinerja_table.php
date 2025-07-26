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
        Schema::create('capaian_kinerja', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_program');
            $table->unsignedBigInteger('id_kegiatan');
            $table->unsignedBigInteger('id_sub_kegiatan');

            $table->year('tahun');
            $table->integer('target_dokumen')->default(0);
            $table->decimal('target_nilai', 15, 2)->default(0);
            $table->decimal('tw1', 15, 2)->default(0);
            $table->decimal('tw2', 15, 2)->default(0);
            $table->decimal('tw3', 15, 2)->default(0);
            $table->decimal('tw4', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('persentase', 5, 2)->default(0);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('id_program')->references('id_program')->on('program')->onDelete('cascade');
            $table->foreign('id_kegiatan')->references('id_kegiatan')->on('kegiatan')->onDelete('cascade');
            $table->foreign('id_sub_kegiatan')->references('id_sub_kegiatan')->on('sub_kegiatan')->onDelete('cascade');

            // Index dengan nama yang lebih pendek
            $table->index(['id_program', 'id_kegiatan', 'id_sub_kegiatan', 'tahun'], 'idx_capaian_kinerja_composite');
            $table->index('tahun', 'idx_capaian_kinerja_tahun');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capaian_kinerja');
    }
};
