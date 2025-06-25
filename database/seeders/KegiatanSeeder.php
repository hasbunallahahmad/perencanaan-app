<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KegiatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kegiatan = [
            // Program 2.08.01 - PROGRAM PENUNJANG URUSAN PEMERINTAHAN DAERAH KABUPATEN/KOTA
            [
                'kode_kegiatan' => '2.08.01.2.01',
                'nama_kegiatan' => 'Perencanaan, Penganggaran, dan Evaluasi Kinerja Perangkat Daerah',
                'id_program' => 1, // Sesuaikan dengan ID program yang sudah dibuat
            ],
            [
                'kode_kegiatan' => '2.08.01.2.02',
                'nama_kegiatan' => 'Administrasi Keuangan Perangkat Daerah',
                'id_program' => 1,
            ],
            [
                'kode_kegiatan' => '2.08.01.2.05',
                'nama_kegiatan' => 'Administrasi Kepegawaian Perangkat Daerah',
                'id_program' => 1,
            ],
            [
                'kode_kegiatan' => '2.08.01.2.06',
                'nama_kegiatan' => 'Administrasi Umum Perangkat Daerah',
                'id_program' => 1,
            ],
            [
                'kode_kegiatan' => '2.08.01.2.07',
                'nama_kegiatan' => 'Pengadaan Barang Milik Daerah Penunjang Urusan Pemerintah Daerah',
                'id_program' => 1,
            ],
            [
                'kode_kegiatan' => '2.08.01.2.08',
                'nama_kegiatan' => 'Penyediaan Jasa Penunjang Urusan Pemerintahan Daerah',
                'id_program' => 1,
            ],
            [
                'kode_kegiatan' => '2.08.01.2.09',
                'nama_kegiatan' => 'Pemeliharaan Barang Milik Daerah Penunjang Urusan Pemerintahan Daerah',
                'id_program' => 1,
            ],

            // Program 2.08.02 - PROGRAM PENGARUSUTAMAAN GENDER DAN PEMBERDAYAAN PEREMPUAN
            [
                'kode_kegiatan' => '2.08.02.2.01',
                'nama_kegiatan' => 'Pelembagaan Pengarusutamaan Gender (PUG) pada Lembaga Pemerintah Kewenangan Kabupaten/Kota',
                'id_program' => 2,
            ],
            [
                'kode_kegiatan' => '2.08.02.2.02',
                'nama_kegiatan' => 'Pemberdayaan Perempuan Bidang Politik, Hukum, Sosial, dan Ekonomi pada Organisasi Kemasyarakatan Kewenangan Kabupaten/Kota',
                'id_program' => 2,
            ],
            [
                'kode_kegiatan' => '2.08.02.2.03',
                'nama_kegiatan' => 'Penguatan dan Pengembangan Penyedia Layanan Pemberdayaan Perempuan Kewenagan Kabupaten/Kota',
                'id_program' => 2,
            ],

            // Program 2.08.03 - PROGRAM PERLINDUNGAN PEREMPUAN
            [
                'kode_kegiatan' => '2.08.03.2.01',
                'nama_kegiatan' => 'Pencegahan Kekerasan terhadap Perempuan Lingkup Daerah Kabupaten/Kota',
                'id_program' => 3,
            ],
            [
                'kode_kegiatan' => '2.08.03.2.02',
                'nama_kegiatan' => 'Penyediaan Layanan Rujukan Lanjutan bagi Perempuan Korban Kekerasan yang Memerlukan Koordinasi Kewenangan Kabupaten/Kota',
                'id_program' => 3,
            ],
            [
                'kode_kegiatan' => '2.08.03.2.03',
                'nama_kegiatan' => 'Penguatan dan Pengembangan Lembaga Penyedia Layanan Perlindungan Perempuan Tingkat Daerah Kabupaten/Kota',
                'id_program' => 3,
            ],

            // Program 2.08.04 - PROGRAM PENINGKATAN KUALITAS KELUARGA
            [
                'kode_kegiatan' => '2.08.04.2.01',
                'nama_kegiatan' => 'Peningkatan Kualitas Keluarga dalam Mewujudkan Kesetaraan Gender (KG) dan Hak Anak Tingkat Daerah Kabupaten/Kota',
                'id_program' => 4,
            ],
            [
                'kode_kegiatan' => '2.08.04.2.02',
                'nama_kegiatan' => 'Penguatan dan Pengembangan Lembaga Penyedia Layanan Peningkatan Kualitas Keluarga dalam Mewujudkan KG dan Hak Anak yang Wilayah Kerjanya dalam Daerah Kabupaten/Kota',
                'id_program' => 4,
            ],

            // Program 2.08.05 - PROGRAM PENGELOLAAN SISTEM DATA GENDER DAN ANAK
            [
                'kode_kegiatan' => '2.08.05.2.01',
                'nama_kegiatan' => 'Pengumpulan, Pengolahan Analisis dan Penyajian Data Gender dan Anak Dalam Kelembagaan Data di Tingkat Daerah Kabupaten/Kota',
                'id_program' => 5,
            ],

            // Program 2.08.06 - PROGRAM PEMENUHAN HAK ANAK (PHA)
            [
                'kode_kegiatan' => '2.08.06.2.01',
                'nama_kegiatan' => 'Pelembagaan PHA pada Lembaga Pemerintah, Nonpemerintah, dan Dunia Usaha Kewenangan Kabupaten/Kota',
                'id_program' => 6,
            ],
            [
                'kode_kegiatan' => '2.08.06.2.02',
                'nama_kegiatan' => 'Penguatan dan Pengembangan Lembaga Penyedia Layanan Peningkatan Kualitas Hidup Anak Kewenangan Kabupaten/Kota',
                'id_program' => 6,
            ],

            // Program 2.08.07 - PROGRAM PERLINDUNGAN KHUSUS ANAK
            [
                'kode_kegiatan' => '2.08.07.2.01',
                'nama_kegiatan' => 'Pencegahan Kekerasan Terhadap Anak yang Melibatkan Para Pihak Lingkup Daerah Kabupaten/Kota',
                'id_program' => 7,
            ],

            // Program 2.13.04 - PROGRAM ADMINISTRASI PEMERINTAHAN DESA
            [
                'kode_kegiatan' => '2.13.04.2.01',
                'nama_kegiatan' => 'Pembinaan dan Pengawasan Penyelenggaraan Administrasi Pemerintahan Desa',
                'id_program' => 8,
            ],

            // Program 2.13.05 - PROGRAM PEMBERDAYAAN LEMBAGA KEMASYARAKATAN, LEMBAGA ADAT DAN MASYARAKAT HUKUM ADAT
            [
                'kode_kegiatan' => '2.13.05.2.01',
                'nama_kegiatan' => 'Pemberdayaan Lembaga Kemasyarakatan yang Bergerak di Bidang Pemberdayaan Desa dan Lembaga Adat Tingkat Daerah Kabupaten/Kota serta Pemberdayaan Masyarakat Hukum Adat yang Masyarakat Pelakunya Hukum Adat yang Sama dalam Daerah Kabupaten/Kota',
                'id_program' => 9,
            ],
        ];

        DB::table('kegiatan')->insert($kegiatan);
    }
}
