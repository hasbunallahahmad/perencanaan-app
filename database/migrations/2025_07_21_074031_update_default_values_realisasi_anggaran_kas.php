<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, update existing NULL values to 0
        DB::table('realisasi_anggaran_kas')->whereNull('rencana_tw_1')->update(['rencana_tw_1' => 0]);
        DB::table('realisasi_anggaran_kas')->whereNull('rencana_tw_2')->update(['rencana_tw_2' => 0]);
        DB::table('realisasi_anggaran_kas')->whereNull('rencana_tw_3')->update(['rencana_tw_3' => 0]);
        DB::table('realisasi_anggaran_kas')->whereNull('rencana_tw_4')->update(['rencana_tw_4' => 0]);

        DB::table('realisasi_anggaran_kas')->whereNull('realisasi_tw_1')->update(['realisasi_tw_1' => 0]);
        DB::table('realisasi_anggaran_kas')->whereNull('realisasi_tw_2')->update(['realisasi_tw_2' => 0]);
        DB::table('realisasi_anggaran_kas')->whereNull('realisasi_tw_3')->update(['realisasi_tw_3' => 0]);
        DB::table('realisasi_anggaran_kas')->whereNull('realisasi_tw_4')->update(['realisasi_tw_4' => 0]);

        DB::table('realisasi_anggaran_kas')->whereNull('realisasi_sd_tw')->update(['realisasi_sd_tw' => 0]);
        DB::table('realisasi_anggaran_kas')->whereNull('persentase_total')->update(['persentase_total' => 0]);
        DB::table('realisasi_anggaran_kas')->whereNull('persentase_realisasi')->update(['persentase_realisasi' => 0]);

        // Then modify the columns to have default values
        Schema::table('realisasi_anggaran_kas', function (Blueprint $table) {
            $table->decimal('rencana_tw_1', 15, 2)->default(0)->change();
            $table->decimal('rencana_tw_2', 15, 2)->default(0)->change();
            $table->decimal('rencana_tw_3', 15, 2)->default(0)->change();
            $table->decimal('rencana_tw_4', 15, 2)->default(0)->change();

            $table->decimal('realisasi_tw_1', 15, 2)->default(0)->change();
            $table->decimal('realisasi_tw_2', 15, 2)->default(0)->change();
            $table->decimal('realisasi_tw_3', 15, 2)->default(0)->change();
            $table->decimal('realisasi_tw_4', 15, 2)->default(0)->change();

            $table->decimal('realisasi_sd_tw', 15, 2)->default(0)->change();
            $table->decimal('persentase_total', 5, 2)->default(0)->change();
            $table->decimal('persentase_realisasi', 5, 2)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('realisasi_anggaran_kas', function (Blueprint $table) {
            $table->decimal('rencana_tw_1', 15, 2)->nullable()->change();
            $table->decimal('rencana_tw_2', 15, 2)->nullable()->change();
            $table->decimal('rencana_tw_3', 15, 2)->nullable()->change();
            $table->decimal('rencana_tw_4', 15, 2)->nullable()->change();

            $table->decimal('realisasi_tw_1', 15, 2)->nullable()->change();
            $table->decimal('realisasi_tw_2', 15, 2)->nullable()->change();
            $table->decimal('realisasi_tw_3', 15, 2)->nullable()->change();
            $table->decimal('realisasi_tw_4', 15, 2)->nullable()->change();

            $table->decimal('realisasi_sd_tw', 15, 2)->nullable()->change();
            $table->decimal('persentase_total', 5, 2)->nullable()->change();
            $table->decimal('persentase_realisasi', 5, 2)->nullable()->change();
        });
    }
};
