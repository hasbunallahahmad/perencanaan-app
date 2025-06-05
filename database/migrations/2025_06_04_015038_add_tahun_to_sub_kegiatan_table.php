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
        Schema::table('sub_kegiatan', function (Blueprint $table) {
            $table->year('tahun')->default(2025)->after('id_sub_kegiatan');
            $table->index(['tahun']);
            $table->index(['tahun', 'id_kegiatan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sub_kegiatan', function (Blueprint $table) {
            $table->dropIndex(['tahun', 'id_kegiatan']);
            $table->dropIndex(['tahun']);
            $table->dropColumn('tahun');
        });
    }
};
