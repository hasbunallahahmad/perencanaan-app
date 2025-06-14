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
        Schema::create('realisasi', function (Blueprint $table) {
            $table->id();

            // Foreign key ke tabel rencana_aksi
            $table->foreignId('rencana_aksi_id')
                ->constrained('rencana_aksi')
                ->onDelete('cascade');

            // Data aksi
            $table->string('nama_aksi');
            $table->date('tanggal');
            $table->string('tempat');
            $table->string('narasumber')->nullable();

            // Data peserta
            $table->integer('laki_laki')->default(0);
            $table->integer('perempuan')->default(0);
            $table->integer('jumlah_peserta')->default(0);
            $table->string('asal_peserta')->nullable();

            // Data anggaran dan dokumentasi
            $table->decimal('realisasi_anggaran', 15, 2)->nullable();
            $table->text('foto_link_gdrive')->nullable();

            // Data tahun dan keterangan
            $table->year('tahun');
            $table->text('keterangan')->nullable();

            $table->timestamps();

            // Indexes untuk performa query
            $table->index('tahun');
            $table->index(['rencana_aksi_id', 'tahun']);
            $table->index('tanggal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('realisasi');
    }
};
