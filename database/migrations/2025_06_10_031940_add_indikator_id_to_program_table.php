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
            $table->unsignedBigInteger('indikator_id')->nullable()->after('nama_program');
            $table->foreign('indikator_id')
                ->references('id')
                ->on('master_indikator')
                ->onDelete('set null')
                ->onUpdate('cascade');
            $table->index('indikator_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('program', function (Blueprint $table) {
            $table->dropForeign(['indikator_id']);
            $table->dropIndex(['indikator_id']);
            $table->dropColumn('indikator_id');
        });
    }
};
