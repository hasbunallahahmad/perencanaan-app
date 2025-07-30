<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('realisasi_anggaran_kas', function (Blueprint $table) {
            $table->date('tanggal_realisasi')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('realisasi_anggaran_kas', function (Blueprint $table) {
            $table->date('tanggal_realisasi')->nullable(false)->change();
        });
    }
};
