<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tambah index untuk optimasi query Filament OrganisasiResource

        // Index composite untuk filter aktif + kota
        if (!$this->indexExists('organisasis', 'idx_organisasis_aktif_kota')) {
            DB::statement('CREATE INDEX idx_organisasis_aktif_kota ON organisasis(aktif, kota)');
        }

        // Index untuk search nama + filter aktif
        if (!$this->indexExists('organisasis', 'idx_organisasis_nama_aktif')) {
            DB::statement('CREATE INDEX idx_organisasis_nama_aktif ON organisasis(nama, aktif)');
        }

        // Index untuk sorting created_at + nama
        if (!$this->indexExists('organisasis', 'idx_organisasis_created_nama')) {
            DB::statement('CREATE INDEX idx_organisasis_created_nama ON organisasis(created_at DESC, nama ASC)');
        }

        // Index untuk bidangs - optimasi count sekretariat
        if (!$this->indexExists('bidangs', 'idx_bidangs_sekretariat_aktif')) {
            DB::statement('CREATE INDEX idx_bidangs_sekretariat_aktif ON bidangs(is_sekretariat, aktif)');
        }

        // Index untuk users - optimasi count per organisasi (tanpa kolom aktif)
        if (!$this->indexExists('users', 'idx_users_organisasi')) {
            DB::statement('CREATE INDEX idx_users_organisasi ON users(organisasi_id)');
        }

        // Index untuk users - optimasi berdasarkan bidang
        if (!$this->indexExists('users', 'idx_users_bidang')) {
            DB::statement('CREATE INDEX idx_users_bidang ON users(bidang_id)');
        }

        // Index untuk users - optimasi berdasarkan seksi
        if (!$this->indexExists('users', 'idx_users_seksi')) {
            DB::statement('CREATE INDEX idx_users_seksi ON users(seksi_id)');
        }

        // Index composite untuk users - organisasi + bidang
        if (!$this->indexExists('users', 'idx_users_organisasi_bidang')) {
            DB::statement('CREATE INDEX idx_users_organisasi_bidang ON users(organisasi_id, bidang_id)');
        }
        // Update table statistics
        DB::statement('ANALYZE TABLE organisasis, bidangs, users, program');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes yang ditambahkan
        $indexes = [
            'organisasis' => [
                'idx_organisasis_aktif_kota',
                'idx_organisasis_nama_aktif',
                'idx_organisasis_created_nama'
            ],
            'bidangs' => [
                'idx_bidangs_sekretariat_aktif'
            ],
            'users' => [
                'idx_users_organisasi',
                'idx_users_bidang',
                'idx_users_seksi',
                'idx_users_organisasi_bidang'
            ],
            'program' => [
                'idx_program_organisasi_tahun_aktif'
            ]
        ];

        foreach ($indexes as $table => $tableIndexes) {
            foreach ($tableIndexes as $index) {
                if ($this->indexExists($table, $index)) {
                    DB::statement("DROP INDEX {$index} ON {$table}");
                }
            }
        }
    }

    /**
     * Check if index exists
     */
    private function indexExists(string $table, string $index): bool
    {
        $result = DB::select("
            SELECT COUNT(*) as count 
            FROM information_schema.STATISTICS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = ? 
            AND INDEX_NAME = ?
        ", [$table, $index]);

        return $result[0]->count > 0;
    }
};
