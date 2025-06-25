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
        Schema::create('master_indikator', function (Blueprint $table) {
            $table->id();
            $table->text('nama_indikator');
            $table->timestamps();
            $table->softDeletes();

            // Index untuk performa query
            $table->index(['deleted_at']);
            $table->fullText('nama_indikator'); // Untuk pencarian text
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_indikator');
    }
};
