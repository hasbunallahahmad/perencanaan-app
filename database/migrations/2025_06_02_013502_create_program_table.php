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
        Schema::create('program', function (Blueprint $table) {
            $table->id('id_program');
            $table->string('kode_program', 20)->unique();
            $table->text('nama_program');
            $table->unsignedBigInteger('organisasi_id');
            $table->timestamps();
            $table->foreign('organisasi_id')->references('id')->on('organisasis')->onDelete('cascade');
            $table->index('organisasi_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program');
    }
};
