<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BidangSeeder extends Seeder
{
    public function run()
    {
        $bidangs = [
            [
                'nama' => 'Sekretariat',
                'kode' => 'SEKT',
                'deskripsi' => 'Unit pelaksana tugas administrasi dan pelayanan teknis',
                'organisasi_id' => 1,
                'is_sekretariat' => 1,
                'aktif' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nama' => 'Bidang Pemberdayaan Perempuan dan Pengarusutamaan Gender',
                'kode' => 'PPPUG',
                'deskripsi' => 'Melaksanakan pemberdayaan perempuan dan pengarusutamaan gender',
                'organisasi_id' => 1,
                'is_sekretariat' => 0,
                'aktif' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nama' => 'Bidang Pemenuhan Hak Anak',
                'kode' => 'PHA',
                'deskripsi' => 'Melaksanakan pemenuhan hak-hak anak',
                'organisasi_id' => 1,
                'is_sekretariat' => 0,
                'aktif' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nama' => 'Bidang Perlindungan Perempuan dan Anak',
                'kode' => 'PPA',
                'deskripsi' => 'Melaksanakan perlindungan perempuan dan anak',
                'organisasi_id' => 1,
                'is_sekretariat' => 0,
                'aktif' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nama' => 'Bidang Pemberdayaan Masyarakat dan Data Informasi',
                'kode' => 'PERMASDATIN',
                'deskripsi' => 'Melaksanakan pemberdayaan masyarakat dan pengelolaan data informasi',
                'organisasi_id' => 1,
                'is_sekretariat' => 0,
                'aktif' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'nama' => 'Unit Pelaksana Teknis Daerah (UPTD)',
                'kode' => 'UPTD',
                'deskripsi' => 'Unit pelaksana teknis daerah di bawah Dinas PP-PA',
                'organisasi_id' => 1,
                'is_sekretariat' => 0,
                'aktif' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('bidangs')->insert($bidangs);
    }
}
