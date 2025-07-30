<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeksiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $seksis = [
            [
                'nama' => 'Sub Bagian Perencanaan dan Evaluasi',
                'kode' => 'PEREVA',
                'deskripsi' => 'Sub bagian yang bertugas dalam perencanaan dan evaluasi program',
                'bidang_id' => 7,
                'jenis' => 'subbagian',
                'aktif' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nama' => 'Sub Bagian Umum dan Kepegawaian',
                'kode' => 'UMPEG',
                'deskripsi' => 'Sub bagian yang bertugas dalam urusan umum dan kepegawaian',
                'bidang_id' => 7,
                'jenis' => 'subbagian',
                'aktif' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nama' => 'Sub Bagian Keuangan dan Aset',
                'kode' => 'KEU',
                'deskripsi' => 'Sub bagian yang bertugas dalam keuangan dan aset',
                'bidang_id' => 7,
                'jenis' => 'subbagian',
                'aktif' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nama' => 'Seksi Pengarusutamaan Gender',
                'kode' => 'PUG',
                'deskripsi' => 'Seksi yang bertugas dalam pengarusutamaan gender',
                'bidang_id' => 8,
                'jenis' => 'seksi',
                'aktif' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nama' => 'Seksi Peningkatan Kualitas Keluarga',
                'kode' => 'PKK',
                'deskripsi' => 'Seksi yang bertugas dalam peningkatan kualitas keluarga',
                'bidang_id' => 8,
                'jenis' => 'seksi',
                'aktif' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nama' => 'Seksi Peningkatan Kualitas Hidup Perempuan',
                'kode' => 'PKHP',
                'deskripsi' => 'Seksi yang bertugas dalam peningkatan kualitas hidup perempuan',
                'bidang_id' => 8,
                'jenis' => 'seksi',
                'aktif' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nama' => 'Seksi Hak Sipil, Informasi dan Partisipasi',
                'kode' => 'HSIP',
                'deskripsi' => 'Seksi yang bertugas dalam hak sipil, informasi dan partisipasi',
                'bidang_id' => 9,
                'jenis' => 'seksi',
                'aktif' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nama' => 'Seksi Pengasuhan dan Lingkungan',
                'kode' => 'PL',
                'deskripsi' => 'Seksi yang bertugas dalam Pengasuhan dan Lingkungan',
                'bidang_id' => 9,
                'jenis' => 'seksi',
                'aktif' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nama' => 'Seksi Pendidikan dan Kesehatan',
                'kode' => 'PK',
                'deskripsi' => 'Seksi yang bertugas dalam Pendidikan dan Kesehatan',
                'bidang_id' => 9,
                'jenis' => 'seksi',
                'aktif' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nama' => 'Seksi Pencegahan dan Penanganan Kekerasan',
                'kode' => 'PPK',
                'deskripsi' => 'Seksi yang bertugas dalam Pencegahan dan Penanganan Kekerasan',
                'bidang_id' => 10,
                'jenis' => 'seksi',
                'aktif' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nama' => 'Seksi Perlindungan Perempuan dan Khusus Anak',
                'kode' => 'PPKA',
                'deskripsi' => 'Seksi yang bertugas dalam Perlindungan Perempuan dan Khusus Anak',
                'bidang_id' => 10,
                'jenis' => 'seksi',
                'aktif' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nama' => 'Seksi Jejaring Perlindungan Perempuan',
                'kode' => 'JPP',
                'deskripsi' => 'Seksi yang bertugas dalam Jejaring Perlindungan Perempuan',
                'bidang_id' => 10,
                'jenis' => 'seksi',
                'aktif' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nama' => 'Seksi Perkembangan Kelurahan',
                'kode' => 'PKEL',
                'deskripsi' => 'Seksi yang bertugas dalam Perkembangan Kelurahan',
                'bidang_id' => 11,
                'jenis' => 'seksi',
                'aktif' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nama' => 'Seksi Pengembangan Usaha Ekonomi Masyarakat dan Teknologi Tepat Guna',
                'kode' => 'PEMTG',
                'deskripsi' => 'Seksi yang bertugas dalam Pengembangan Usaha Ekonomi Masyarakat dan Teknologi Tepat Guna',
                'bidang_id' => 11,
                'jenis' => 'seksi',
                'aktif' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nama' => 'Seksi Data dan informasi',
                'kode' => 'Datin',
                'deskripsi' => 'Seksi yang bertugas dalam Data dan informasi',
                'bidang_id' => 11,
                'jenis' => 'seksi',
                'aktif' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
        ];

        DB::table('seksis')->insert($seksis);
    }
}
