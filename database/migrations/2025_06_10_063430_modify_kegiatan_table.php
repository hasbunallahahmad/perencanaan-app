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
        // Hapus kolom indikator_id_2 yang tidak diperlukan
        Schema::table('kegiatan', function (Blueprint $table) {
            // Drop foreign key constraint jika ada
            if (Schema::hasColumn('kegiatan', 'indikator_id_2')) {
                $table->dropForeign(['indikator_id_2']);
                $table->dropColumn('indikator_id_2');
            }
        });

        // Pastikan kolom indikator_id ada dan memiliki foreign key yang benar
        Schema::table('kegiatan', function (Blueprint $table) {
            // Jika kolom indikator_id belum ada, tambahkan
            if (!Schema::hasColumn('kegiatan', 'indikator_id')) {
                $table->unsignedBigInteger('indikator_id')->nullable()->after('id_program');
            }

            // Tambahkan foreign key constraint
            $table->foreign('indikator_id')
                ->references('id')
                ->on('master_indikator')
                ->onDelete('set null');
        });

        // Drop pivot table jika ada (karena kita tidak menggunakan many-to-many)
        Schema::dropIfExists('kegiatan_indikator');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan perubahan jika rollback
        Schema::table('kegiatan', function (Blueprint $table) {
            $table->dropForeign(['indikator_id']);
            $table->unsignedBigInteger('indikator_id_2')->nullable()->after('indikator_id');
            $table->foreign('indikator_id_2')
                ->references('id')
                ->on('master_indikator')
                ->onDelete('set null');
        });

        // Buat ulang pivot table jika diperlukan
        Schema::create('kegiatan_indikator', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kegiatan_id');
            $table->unsignedBigInteger('indikator_id');
            $table->timestamps();

            $table->foreign('kegiatan_id')->references('id_kegiatan')->on('kegiatan')->onDelete('cascade');
            $table->foreign('indikator_id')->references('id')->on('master_indikator')->onDelete('cascade');

            $table->unique(['kegiatan_id', 'indikator_id']);
        });
    }
};
