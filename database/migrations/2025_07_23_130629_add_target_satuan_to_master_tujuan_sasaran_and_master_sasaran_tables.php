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
        // Add target and satuan to master_tujuan_sasaran table
        Schema::table('master_tujuan_sasarans', function (Blueprint $table) {
            $table->decimal('target', 15, 3)->nullable()->after('indikator_tujuan');
            $table->string('satuan', 50)->nullable()->after('target');
        });

        // Add target and satuan to master_sasaran table
        Schema::table('master_sasaran', function (Blueprint $table) {
            $table->decimal('target', 15, 3)->nullable()->after('indikator_sasaran');
            $table->string('satuan', 50)->nullable()->after('target');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_tujuan_sasaran', function (Blueprint $table) {
            $table->dropColumn(['target', 'satuan']);
        });

        Schema::table('master_sasaran', function (Blueprint $table) {
            $table->dropColumn(['target', 'satuan']);
        });
    }
};
