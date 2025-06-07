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
        Schema::create('tujas', function (Blueprint $table) {
            $table->id();
            $table->string('tujuan');
            $table->string('sasaran');
            $table->string('indikator');
            $table->decimal('target', 15, 2);
            $table->string('satuan', 50);
            $table->decimal('realisasi_tw_1', 15, 2)->nullable();
            $table->decimal('realisasi_tw_2', 15, 2)->nullable();
            $table->decimal('realisasi_tw_3', 15, 2)->nullable();
            $table->decimal('realisasi_tw_4', 15, 2)->nullable();
            $table->decimal('persentase', 5, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tujas');
    }
};
