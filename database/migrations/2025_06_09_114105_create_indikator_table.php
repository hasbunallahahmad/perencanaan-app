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
        Schema::create('indikator', function (Blueprint $table) {
            $table->id('id_indikator');
            $table->unsignedBigInteger('id_sub_kegiatan');
            $table->string('kode_indikator', 50)->nullable();
            $table->text('nama_indikator');
            $table->string('satuan', 100)->nullable();
            $table->decimal('target_indikator', 15, 2)->nullable();
            $table->decimal('capaian_indikator', 15, 2)->default(0);
            $table->year('tahun');
            $table->text('keterangan')->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraint
            $table->foreign('id_sub_kegiatan')
                ->references('id_sub_kegiatan')
                ->on('sub_kegiatan')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            // Indexes untuk optimasi query dan mencegah N+1 Problem
            $table->index('id_sub_kegiatan', 'idx_sub_kegiatan');
            $table->index('tahun', 'idx_tahun');
            $table->index('status', 'idx_status');
            $table->index(['id_sub_kegiatan', 'tahun', 'status'], 'idx_sub_kegiatan_tahun_status');
            $table->index(['tahun', 'status'], 'idx_tahun_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indikator');
    }
};
