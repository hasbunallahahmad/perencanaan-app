<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kegiatan', function (Blueprint $table) {
            // Add tahun column if it doesn't exist
            if (!Schema::hasColumn('kegiatan', 'tahun')) {
                $table->year('tahun')->nullable()->after('kode_kegiatan');
            }
        });

        Schema::table('kegiatan', function (Blueprint $table) {
            // Drop existing unique constraint (try different possible names)
            try {
                $table->dropUnique(['kode_kegiatan']);
            } catch (\Exception $e) {
                // Try other possible constraint names
                try {
                    $table->dropUnique('kegiatan_kode_kegiatan_unique');
                } catch (\Exception $e2) {
                    // Constraint might not exist, continue
                }
            }

            // Create composite unique constraint
            $table->unique(['kode_kegiatan', 'tahun'], 'kegiatan_kode_tahun_unique');
        });
    }

    public function down(): void
    {
        Schema::table('kegiatan', function (Blueprint $table) {
            // Drop composite unique
            $table->dropUnique('kegiatan_kode_tahun_unique');

            // Restore single column unique
            $table->unique('kode_kegiatan', 'kegiatan_kode_kegiatan_unique');
        });
    }
};
