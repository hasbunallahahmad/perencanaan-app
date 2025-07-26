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
        // Do nothing - column already exists with correct structure
        if (!Schema::hasColumn('kegiatan', 'tahun')) {
            Schema::table('kegiatan', function (Blueprint $table) {
                $table->year('tahun')->after('id_program')->default(2025)->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kegiatan', function (Blueprint $table) {
            $table->dropColumn('tahun');
        });
    }
};
