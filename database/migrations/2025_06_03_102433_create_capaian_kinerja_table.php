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
        if (Schema::hasTable('capaian_kinerja')) {
            Schema::create('capaian_kinerja', function (Blueprint $table) {
                $table->id();
                $table->foreignId('id_program')->constrained('program')->onDelete('cascade');
                $table->foreignId('id_kegiatan')->constrained('kegiatan')->onDelete('cascade');
                $table->foreignId('id_sub_kegiatan')->constrained('sub_kegiatan')->onDelete('cascade');
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

                $table->index(['id_program', 'id_kegiatan', 'id_sub_kegiatan', 'tahun']);
                $table->index('tahun');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capaian_kinerja');
    }
};
