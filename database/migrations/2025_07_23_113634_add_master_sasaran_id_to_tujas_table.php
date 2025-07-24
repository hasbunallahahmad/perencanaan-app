<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tujas', function (Blueprint $table) {
            // Hanya menambahkan foreign key ke master_sasaran saja
            $table->unsignedBigInteger('master_sasaran_id')->nullable()->after('master_tujuan_sasaran_id');
            $table->foreign('master_sasaran_id')
                ->references('id')
                ->on('master_sasaran')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('tujas', function (Blueprint $table) {
            // Drop foreign key constraint untuk master_sasaran saja
            $table->dropForeign(['master_sasaran_id']);

            // Drop column master_sasaran_id saja
            $table->dropColumn('master_sasaran_id');
        });
    }
};
