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
        Schema::table('master_tujuan_sasarans', function (Blueprint $table) {
            $table->dropColumn(['sasaran', 'indikator']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_tujuan_sasarans', function (Blueprint $table) {
            $table->text('sasaran')->nullable();
            $table->text('indikator')->nullable();
        });
    }
};
