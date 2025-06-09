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
        Schema::table('tujas', function (Blueprint $table) {
            $table->integer('tahun')->after('sasaran')->default(2025);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tujas', function (Blueprint $table) {
            $table->dropColumn('tahun');
        });
    }
};
