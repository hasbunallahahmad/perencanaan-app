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
        Schema::table('realisasi_anggaran_kas', function (Blueprint $table) {
            // Tambah kolom untuk rencana per triwulan
            $table->decimal('rencana_tw_1', 15, 2)->nullable()->after('jumlah_realisasi');
            $table->decimal('rencana_tw_2', 15, 2)->nullable()->after('rencana_tw_1');
            $table->decimal('rencana_tw_3', 15, 2)->nullable()->after('rencana_tw_2');
            $table->decimal('rencana_tw_4', 15, 2)->nullable()->after('rencana_tw_3');

            // Tambah kolom untuk realisasi per triwulan
            $table->decimal('realisasi_tw_1', 15, 2)->nullable()->after('rencana_tw_4');
            $table->decimal('realisasi_tw_2', 15, 2)->nullable()->after('realisasi_tw_1');
            $table->decimal('realisasi_tw_3', 15, 2)->nullable()->after('realisasi_tw_2');
            $table->decimal('realisasi_tw_4', 15, 2)->nullable()->after('realisasi_tw_3');

            // Tambah kolom untuk tanggal realisasi per triwulan
            $table->date('tanggal_realisasi_tw_1')->nullable()->after('realisasi_tw_4');
            $table->date('tanggal_realisasi_tw_2')->nullable()->after('tanggal_realisasi_tw_1');
            $table->date('tanggal_realisasi_tw_3')->nullable()->after('tanggal_realisasi_tw_2');
            $table->date('tanggal_realisasi_tw_4')->nullable()->after('tanggal_realisasi_tw_3');

            // Tambah kolom untuk total realisasi sampai dengan
            $table->decimal('realisasi_sd_tw', 15, 2)->nullable()->after('tanggal_realisasi_tw_4');
            $table->decimal('persentase_total', 5, 2)->nullable()->after('realisasi_sd_tw');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('realisasi_anggaran_kas', function (Blueprint $table) {
            $table->dropColumn([
                'rencana_tw_1',
                'rencana_tw_2',
                'rencana_tw_3',
                'rencana_tw_4',
                'realisasi_tw_1',
                'realisasi_tw_2',
                'realisasi_tw_3',
                'realisasi_tw_4',
                'tanggal_realisasi_tw_1',
                'tanggal_realisasi_tw_2',
                'tanggal_realisasi_tw_3',
                'tanggal_realisasi_tw_4',
                'realisasi_sd_tw',
                'persentase_total',
            ]);
        });
    }
};
