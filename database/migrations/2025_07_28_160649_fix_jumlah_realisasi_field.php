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
        Schema::table('realisasi_anggaran_kas', function (Blueprint $table) {
            // Buat field nullable dengan default 0
            $table->decimal('jumlah_realisasi', 15, 2)->default(0)->change();
        });

        // Update existing records yang null
        DB::table('realisasi_anggaran_kas')
            ->whereNull('jumlah_realisasi')
            ->update(['jumlah_realisasi' => 0]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('realisasi_anggaran_kas', function (Blueprint $table) {
            $table->decimal('jumlah_realisasi', 15, 2)->change();
        });
    }
};
