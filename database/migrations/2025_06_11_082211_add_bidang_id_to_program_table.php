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
            $table->unsignedBigInteger('bidang_id')
                ->nullable()
                ->after('id_program');

            $table->foreign('bidang_id', 'fk_program_bidang')
                ->references('id')
                ->on('bidangs') // Correctly reference 'bidangs' table
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('program', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign('fk_program_bidang');
            // Then drop the column
            $table->dropColumn('bidang_id');
        });
    }
};
