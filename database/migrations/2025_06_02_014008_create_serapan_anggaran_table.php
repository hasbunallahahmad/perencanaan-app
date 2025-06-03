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
        Schema::create('serapan_anggaran', function (Blueprint $table) {
            $table->id('id_serapan');
            $table->unsignedBigInteger('id_sub_kegiatan');
            $table->integer('tahun');
            $table->integer('bulan');
            $table->decimal('anggaran', 15, 2)->default(0);
            $table->decimal('realisasi', 15, 2)->default(0);
            $table->decimal('persentase_serapan', 5, 2)->storedAs('CASE WHEN anggaran > 0 THEN (realisasi / anggaran) * 100 ELSE 0 END');
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->foreign('id_sub_kegiatan')->references('id_sub_kegiatan')->on('sub_kegiatan')->onDelete('cascade');
            $table->unique(['id_sub_kegiatan', 'tahun', 'bulan'], 'unique_serapan');
            $table->index(['tahun', 'bulan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('serapan_anggaran');
    }
};
