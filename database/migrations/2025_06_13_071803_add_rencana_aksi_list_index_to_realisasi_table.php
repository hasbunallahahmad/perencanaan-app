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
            $table->integer('rencana_aksi_list_index')->nullable()->after('rencana_aksi_id');
            $table->index(['rencana_aksi_id', 'rencana_aksi_list_index'], 'idx_realisasi_rencana_aksi_list');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::table('realisasi', function (Blueprint $table) {
            $table->dropIndex('idx_realisasi_rencana_aksi_list');
            $table->dropColumn('rencana_aksi_list_index');
        });
    }
};
