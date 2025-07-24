<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tujas', function (Blueprint $table) {
            $table->foreignId('master_tujuan_sasaran_id')->nullable()->after('id');
            $table->foreign('master_tujuan_sasaran_id')->references('id')->on('master_tujuan_sasarans')->onDelete('cascade');

            // Ubah kolom tujuan, sasaran, indikator menjadi nullable
            $table->text('tujuan')->nullable()->change();
            $table->text('sasaran')->nullable()->change();
            $table->text('indikator')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tujas', function (Blueprint $table) {
            $table->dropForeign(['master_tujuan_sasaran_id']);
            $table->dropColumn('master_tujuan_sasaran_id');

            // Kembalikan ke required
            $table->text('tujuan')->nullable(false)->change();
            $table->text('sasaran')->nullable(false)->change();
            $table->text('indikator')->nullable(false)->change();
        });
    }
};
