<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            CREATE VIEW view_indikator_lengkap AS
            SELECT
                i.id_indikator,
                i.kode_indikator,
                i.nama_indikator,
                i.satuan,
                i.target_indikator,
                i.capaian_indikator,
                i.tahun,
                i.status,
                i.keterangan,
                i.created_at,
                i.updated_at,
                sk.id_sub_kegiatan,
                sk.kode_sub_kegiatan,
                sk.nama_sub_kegiatan,
                k.id_kegiatan,
                k.kode_kegiatan,
                k.nama_kegiatan,
                p.id_program,
                p.kode_program,
                p.nama_program,
                b.id,
                b.nama,
                -- Tambahan kolom untuk agregasi yang sering digunakan
                CONCAT(b.nama, ' - ', p.nama_program, ' - ', k.nama_kegiatan, ' - ', sk.nama_sub_kegiatan) as full_hierarchy,
                CASE
                    WHEN i.target_indikator > 0 THEN
                        ROUND((i.capaian_indikator / i.target_indikator) * 100, 2)
                    ELSE 0
                END as persentase_capaian
            FROM indikator i
            JOIN sub_kegiatan sk ON i.id_sub_kegiatan = sk.id_sub_kegiatan
            JOIN kegiatan k ON sk.id_kegiatan = k.id_kegiatan
            JOIN program p ON k.id_program = p.id_program
            JOIN bidangs b ON p.organisasi_id = b.id
            WHERE i.deleted_at IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS view_indikator_lengkap');
    }
};
