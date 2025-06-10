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
        Schema::table('program', function (Blueprint $table) {
            $table->unsignedBigInteger('indikator_id_2')->nullable()->after('indikator_id');
            $table->foreign('indikator_id_2')->references('id')->on('master_indikator')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('program', function (Blueprint $table) {
            $table->dropForeign(['indikator_id_2']);
            $table->dropColumn('indikator_id_2');
        });
    }
};
