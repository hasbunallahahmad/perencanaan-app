<?php
// database/migrations/xxxx_xx_xx_create_rencana_anggaran_kas_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rencana_anggaran_kas', function (Blueprint $table) {
            $table->id();
            $table->year('tahun');
            $table->enum('triwulan', ['1', '2', '3', '4']);
            $table->string('kategori');
            $table->text('deskripsi')->nullable();
            $table->decimal('jumlah_rencana', 15, 2);
            $table->date('tanggal_rencana');
            $table->enum('status', ['draft', 'approved', 'rejected'])->default('draft');
            $table->text('catatan')->nullable();
            $table->timestamps();

            // Index untuk performa
            $table->index(['tahun', 'triwulan']);
            $table->index('kategori');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rencana_anggaran_kas');
    }
};
