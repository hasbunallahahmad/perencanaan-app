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
        Schema::table('realisasi', function (Blueprint $table) {
            // Tambahkan kolom untuk setiap jenis narasumber
            $table->integer('jumlah_dprd')->default(0)->after('tempat');
            $table->integer('jumlah_kepala_dinas')->default(0)->after('jumlah_dprd');
            $table->integer('jumlah_kepala_daerah')->default(0)->after('jumlah_kepala_dinas');

            // Optional: tambahkan kolom total untuk kemudahan query
            $table->integer('total_narasumber')->default(0)->after('jumlah_kepala_daerah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('realisasi', function (Blueprint $table) {
            $table->dropColumn([
                'jumlah_dprd',
                'jumlah_kepala_dinas',
                'jumlah_kepala_daerah',
                'total_narasumber'
            ]);
        });
    }
};
