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
        Schema::table('rencana_anggaran_kas', function (Blueprint $table) {
            if (Schema::hasColumn('rencana_anggaran_kas', 'triwulan')) {
                $table->dropColumn('triwulan');
            }
            if (!Schema::hasColumn('rencana_anggaran_kas', 'jenis_anggaran')) {
                $table->enum('jenis_anggaran', ['anggaran_murni', 'pergeseran', 'perubahan'])
                    ->after('tahun')
                    ->default('anggaran_murni');
            }
            $table->index(['tahun', 'jenis_anggaran', 'status']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rencana_anggaran_kas', function (Blueprint $table) {
            $table->dropIndex(['tahun', 'jenis_anggaran', 'status']);
            $table->dropIndex(['created_at']);
            if (Schema::hasColumn('rencana_anggaran_kas', 'jenis_anggaran')) {
                $table->dropColumn('jenis_anggaran');
            }
            if (!Schema::hasColumn('rencana_anggaran_kas', 'triwulan')) {
                $table->string('triwulan', 1)->after('tahun');
            }
        });
    }
};
