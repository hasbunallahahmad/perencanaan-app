<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sub_kegiatan', function (Blueprint $table) {
            $table->bigInteger('anggaran')->default(0)->after('id_kegiatan');
            $table->bigInteger('realisasi')->default(0)->after('anggaran');
        });
    }

    public function down()
    {
        Schema::table('sub_kegiatan', function (Blueprint $table) {
            $table->dropColumn(['anggaran', 'realisasi']);
        });
    }
};
