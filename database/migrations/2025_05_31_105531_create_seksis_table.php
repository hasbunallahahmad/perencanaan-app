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
        Schema::create('seksis', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('kode')->nullable();
            $table->text('deskripsi')->nullable();
            $table->foreignId('bidang_id')->constrained()->onDelete('cascade');
            $table->enum('jenis', ['seksi', 'subbagian'])->default('seksi');
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seksis');
    }
};
