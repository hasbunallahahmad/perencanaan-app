<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('program', function (Blueprint $table) {
            $table->foreignId('bidang_id')
                ->nullable()
                ->after('id_program')
                ->constrained('bidang')
                ->onDelete('set null'); // or 'cascade' based on your business logic
        });
    }

    public function down()
    {
        Schema::table('program', function (Blueprint $table) {
            $table->dropForeign(['bidang_id']);
            $table->dropColumn('bidang_id');
        });
    }
};
