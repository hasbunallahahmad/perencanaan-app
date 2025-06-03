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
        Schema::create('kegiatan', function (Blueprint $table) {
            $table->id('id_kegiatan');
            $table->string('kode_kegiatan', 30)->unique();
            $table->text('nama_kegiatan');
            $table->unsignedBigInteger('id_program');
            $table->timestamps();
            $table->foreign('id_program')->references('id_program')->on('program')->onDelete('cascade');
            $table->index('id_program');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kegiatan');
    }
};
