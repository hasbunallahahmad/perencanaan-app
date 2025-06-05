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
        Schema::create('capaian_kinerja_kegiatan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_program');
            $table->unsignedBigInteger('id_kegiatan');
            $table->integer('tahun');
            $table->string('target_dokumen')->nullable();
            $table->decimal('target_nilai', 15, 2)->nullable();
            $table->decimal('tw1', 15, 2)->default(0);
            $table->decimal('tw2', 15, 2)->default(0);
            $table->decimal('tw3', 15, 2)->default(0);
            $table->decimal('tw4', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('persentase', 5, 2)->default(0);
            $table->enum('status_perencanaan', ['draft', 'approved', 'rejected'])->default('draft');
            $table->enum('status_realisasi', ['not_started', 'in_progress', 'completed'])->default('not_started');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('id_program')->references('id_program')->on('program')->onDelete('cascade');
            $table->foreign('id_kegiatan')->references('id_kegiatan')->on('kegiatan')->onDelete('cascade');

            // Indexes
            $table->index(['id_program', 'id_kegiatan', 'tahun']);
            $table->index('tahun');
            $table->index('status_perencanaan');
            $table->index('status_realisasi');

            // Unique constraint
            $table->unique(['id_program', 'id_kegiatan', 'tahun'], 'unique_program_kegiatan_tahun');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capaian_kinerja_kegiatan');
    }
};
