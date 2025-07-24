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
        Schema::create('realisasi_tujuan_sasaran', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('master_tujuan_sasaran_id')
                ->constrained('master_tujuan_sasarans')
                ->onDelete('cascade');
            $table->foreignId('master_sasaran_id')
                ->constrained('master_sasaran')
                ->onDelete('cascade');

            // Period info
            $table->year('tahun');
            $table->enum('triwulan', ['TW1', 'TW2', 'TW3', 'TW4'])->nullable();

            // Target and realization data
            $table->decimal('target_tahun', 10, 2)->nullable();
            $table->decimal('realisasi_tahun_lalu', 10, 2)->nullable();
            $table->decimal('penetapan_target', 10, 2)->nullable();

            // Quarterly realizations
            $table->decimal('realisasi_tw1', 10, 2)->nullable();
            $table->decimal('realisasi_tw2', 10, 2)->nullable();
            $table->decimal('realisasi_tw3', 10, 2)->nullable();
            $table->decimal('realisasi_tw4', 10, 2)->nullable();

            // Quarterly verifications
            $table->boolean('verifikasi_tw1')->default(false);
            $table->boolean('verifikasi_tw2')->default(false);
            $table->boolean('verifikasi_tw3')->default(false);
            $table->boolean('verifikasi_tw4')->default(false);

            // Supporting documents (JSON)
            $table->json('dokumen_tw1')->nullable();
            $table->json('dokumen_tw2')->nullable();
            $table->json('dokumen_tw3')->nullable();
            $table->json('dokumen_tw4')->nullable();

            // Status for each quarter
            $table->enum('status_tw1', ['pending', 'verified', 'rejected'])->default('pending');
            $table->enum('status_tw2', ['pending', 'verified', 'rejected'])->default('pending');
            $table->enum('status_tw3', ['pending', 'verified', 'rejected'])->default('pending');
            $table->enum('status_tw4', ['pending', 'verified', 'rejected'])->default('pending');

            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            // Indexes with custom names to avoid length issues
            $table->index(['tahun', 'triwulan'], 'idx_realisasi_tahun_triwulan');
            $table->index(['master_tujuan_sasaran_id', 'master_sasaran_id', 'tahun'], 'idx_realisasi_master_tahun');

            // Unique constraint to prevent duplicate entries
            $table->unique(['master_tujuan_sasaran_id', 'master_sasaran_id', 'tahun'], 'unique_realisasi_per_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('realisasi_tujuan_sasaran');
    }
};
