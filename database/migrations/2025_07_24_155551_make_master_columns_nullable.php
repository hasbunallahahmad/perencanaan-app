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
        Schema::table('realisasi_tujuan_sasaran', function (Blueprint $table) {
            // Ubah kolom master_sasaran_id menjadi nullable
            $table->unsignedBigInteger('master_sasaran_id')->nullable()->change();

            // Ubah kolom master_tujuan_sasarans_id menjadi nullable juga
            $table->unsignedBigInteger('master_tujuan_sasarans_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('realisasi_tujuan_sasaran', function (Blueprint $table) {
            // Kembalikan ke NOT NULL jika rollback
            $table->unsignedBigInteger('master_sasaran_id')->nullable(false)->change();
            $table->unsignedBigInteger('master_tujuan_sasarans_id')->nullable(false)->change();
        });
    }
};
