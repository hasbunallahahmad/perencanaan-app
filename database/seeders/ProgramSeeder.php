<?php

namespace Database\Seeders;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProgramSeeder extends Seeder
{
    public function run()
    {
        $programs = [

            [
                'bidang_id' => 1, // Sekretariat
                'tahun' => 2025,
                'kode_program' => '2.08.01',
                'nama_program' => 'PROGRAM PENUNJANG URUSAN PEMERINTAHAN DAERAH KABUPATEN/KOTA',
                'indikator_id' => null,
                'indikator_id_2' => null,
                'anggaran' => 0,
                'realisasi' => 0,
                'organisasi_id' => 1
            ],
            [
                'bidang_id' => 2, // PPPUG
                'tahun' => 2025,
                'kode_program' => '2.08.02',
                'nama_program' => 'PROGRAM PENGARUSUTAMAAN GENDER DAN PEMBERDAYAAN PEREMPUAN',
                'indikator_id' => null,
                'indikator_id_2' => null,
                'anggaran' => 0,
                'realisasi' => 0,
                'organisasi_id' => 1
            ],
            [
                'bidang_id' => 4, // PPA
                'tahun' => 2025,
                'kode_program' => '2.08.03',
                'nama_program' => 'PROGRAM PERLINDUNGAN PEREMPUAN',
                'indikator_id' => null,
                'indikator_id_2' => null,
                'anggaran' => 0,
                'realisasi' => 0,
                'organisasi_id' => 1
            ],
            [
                'bidang_id' => 2, // PPPUG
                'tahun' => 2025,
                'kode_program' => '2.08.04',
                'nama_program' => 'PROGRAM PENINGKATAN KUALITAS KELUARGA',
                'indikator_id' => null,
                'indikator_id_2' => null,
                'anggaran' => 0,
                'realisasi' => 0,
                'organisasi_id' => 1
            ],
            [
                'bidang_id' => 5, // PERMASDATIN
                'tahun' => 2025,
                'kode_program' => '2.08.05',
                'nama_program' => 'PROGRAM PENGELOLAAN SISTEM DATA GENDER DAN ANAK',
                'indikator_id' => null,
                'indikator_id_2' => null,
                'anggaran' => 0,
                'realisasi' => 0,
                'organisasi_id' => 1
            ],
            [
                'bidang_id' => 3, // PHA
                'tahun' => 2025,
                'kode_program' => '2.08.06',
                'nama_program' => 'PROGRAM PEMENUHAN HAK ANAK (PHA)',
                'indikator_id' => null,
                'indikator_id_2' => null,
                'anggaran' => 0,
                'realisasi' => 0,
                'organisasi_id' => 1
            ],
            [
                'bidang_id' => 4, // PPA
                'tahun' => 2025,
                'kode_program' => '2.08.07',
                'nama_program' => 'PROGRAM PERLINDUNGAN KHUSUS ANAK',
                'indikator_id' => null,
                'indikator_id_2' => null,
                'anggaran' => 0,
                'realisasi' => 0,
                'organisasi_id' => 1
            ],
            [
                'bidang_id' => 5, // PERMASDATIN
                'tahun' => 2025,
                'kode_program' => '2.13.04',
                'nama_program' => 'PROGRAM ADMINISTRASI PEMERINTAHAN DESA',
                'indikator_id' => null,
                'indikator_id_2' => null,
                'anggaran' => 0,
                'realisasi' => 0,
                'organisasi_id' => 1
            ],
            [
                'bidang_id' => 5, // PERMASDATIN
                'tahun' => 2025,
                'kode_program' => '2.13.05',
                'nama_program' => 'PROGRAM PEMBERDAYAAN LEMBAGA KEMASYARAKATAN, LEMBAGA ADAT DAN MASYARAKAT HUKUM ADAT',
                'indikator_id' => null,
                'indikator_id_2' => null,
                'anggaran' => 0,
                'realisasi' => 0,
                'organisasi_id' => 1
            ],

            // Data tambahan untuk tahun 2026 - berdasarkan data yang terlihat di tabel
            [
                'bidang_id' => 1, // Sesuai dengan data di tabel untuk 2.08.01 tahun 2026
                'tahun' => 2026,
                'kode_program' => '2.08.01',
                'nama_program' => 'PROGRAM PENUNJANG URUSAN PEMERINTAHAN DAERAH KABUPATEN/KOTA',
                'indikator_id' => 2,
                'indikator_id_2' => 1,
                'anggaran' => 0,
                'realisasi' => 0,
                'organisasi_id' => 1
            ],
            [
                'bidang_id' => 2, // Sesuai dengan data di tabel untuk 2.08.02 tahun 2026
                'tahun' => 2026,
                'kode_program' => '2.08.02',
                'nama_program' => 'PROGRAM PENGARUSUTAMAAN GENDER DAN PEMBERDAYAAN PEREMPUAN',
                'indikator_id' => 3,
                'indikator_id_2' => 4,
                'anggaran' => 0,
                'realisasi' => 0,
                'organisasi_id' => 1
            ],
            [
                'bidang_id' => 4, // Sesuai dengan data di tabel untuk 2.08.03 tahun 2026
                'tahun' => 2026,
                'kode_program' => '2.08.03',
                'nama_program' => 'PROGRAM PERLINDUNGAN PEREMPUAN',
                'indikator_id' => 5,
                'indikator_id_2' => null,
                'anggaran' => 0,
                'realisasi' => 0,
                'organisasi_id' => 1
            ]
        ];

        // Menggunakan updateOrCreate untuk menghindari duplikasi
        foreach ($programs as $program) {
            DB::table('program')->updateOrInsert(
                [
                    'kode_program' => $program['kode_program'],
                    'tahun' => $program['tahun'],
                    'organisasi_id' => $program['organisasi_id']
                ],
                array_merge($program, [
                    'created_at' => now(),
                    'updated_at' => now()
                ])
            );
        }
    }
}
