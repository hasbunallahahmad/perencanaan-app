<?php
// database/migrations/xxxx_xx_xx_create_realisasi_anggaran_kas_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('realisasi_anggaran_kas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rencana_anggaran_kas_id')
                ->constrained('rencana_anggaran_kas')
                ->onDelete('cascade');
            $table->year('tahun');
            $table->enum('triwulan', ['1', '2', '3', '4']);
            $table->string('kategori');
            $table->text('deskripsi')->nullable();
            $table->decimal('jumlah_realisasi', 15, 2);
            $table->date('tanggal_realisasi');
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->text('catatan_realisasi')->nullable();
            $table->string('bukti_dokumen')->nullable(); // path file bukti
            $table->timestamps();

            // Index untuk performa
            $table->index(['tahun', 'triwulan']);
            $table->index('kategori');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('realisasi_anggaran_kas');
    }
};
