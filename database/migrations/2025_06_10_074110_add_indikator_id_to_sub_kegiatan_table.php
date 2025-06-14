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
            $table->unsignedBigInteger('indikator_id')->nullable()->after('nama_sub_kegiatan');


            $table->foreign('indikator_id')
                ->references('id')
                ->on('master_indikator')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sub_kegiatan', function (Blueprint $table) {

            $table->dropForeign(['indikator_id']);


            $table->dropColumn('indikator_id');
        });
    }
};
