<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubKegiatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subKegiatan = [
            // Kegiatan 2.08.01.2.01 - Perencanaan, Penganggaran, dan Evaluasi Kinerja Perangkat Daerah (id_kegiatan = 3)
            [
                'kode_sub_kegiatan' => '2.08.01.2.01.01',
                'nama_sub_kegiatan' => 'Penyusunan Dokumen Perencanaan Perangkat Daerah',
                'anggaran' => 53235200,
                'realisasi' => 0,
                'id_kegiatan' => 3, // Changed from 1 to 3
            ],
            [
                'kode_sub_kegiatan' => '2.08.01.2.01.02',
                'nama_sub_kegiatan' => 'Koordinasi dan Penyusunan Dokumen RKA-SKPD',
                'anggaran' => 3289000,
                'realisasi' => 0,
                'id_kegiatan' => 3, // Changed from 1 to 3
            ],
            [
                'kode_sub_kegiatan' => '2.08.01.2.01.03',
                'nama_sub_kegiatan' => 'Koordinasi dan Penyusunan Dokumen Perubahan RKA-SKPD',
                'anggaran' => 3744000,
                'realisasi' => 0,
                'id_kegiatan' => 3, // Changed from 1 to 3
            ],
            [
                'kode_sub_kegiatan' => '2.08.01.2.01.04',
                'nama_sub_kegiatan' => 'Koordinasi dan Penyusunan DPA-SKPD',
                'anggaran' => 2530000,
                'realisasi' => 0,
                'id_kegiatan' => 3, // Changed from 1 to 3
            ],
            [
                'kode_sub_kegiatan' => '2.08.01.2.01.05',
                'nama_sub_kegiatan' => 'Koordinasi dan Penyusunan Perubahan DPA-SKPD',
                'anggaran' => 2080000,
                'realisasi' => 0,
                'id_kegiatan' => 3, // Changed from 1 to 3
            ],
            [
                'kode_sub_kegiatan' => '2.08.01.2.01.06',
                'nama_sub_kegiatan' => 'Koordinasi dan Penyusunan Laporan Capaian Kinerja dan Ikhtisar Realisasi Kinerja SKPD',
                'anggaran' => 4271200,
                'realisasi' => 2652400,
                'id_kegiatan' => 3, // Changed from 1 to 3
            ],
            [
                'kode_sub_kegiatan' => '2.08.01.2.01.07',
                'nama_sub_kegiatan' => 'Evaluasi Kinerja Perangkat Daerah',
                'anggaran' => 2586000,
                'realisasi' => 405000,
                'id_kegiatan' => 3, // Changed from 1 to 3
            ],
            [
                'kode_sub_kegiatan' => '2.08.01.2.01.08',
                'nama_sub_kegiatan' => 'Penyelenggaraan Wilidata Pendukung Statistik Sektoral Daerah',
                'anggaran' => 50000000,
                'realisasi' => 0,
                'id_kegiatan' => 3, // Changed from 1 to 3
            ],

            // Kegiatan 2.08.01.2.02 - Administrasi Keuangan Perangkat Daerah (id_kegiatan = 4)
            [
                'kode_sub_kegiatan' => '2.08.01.2.02.01',
                'nama_sub_kegiatan' => 'Penyediaan Gaji dan Tunjangan ASN',
                'anggaran' => 12386804720,
                'realisasi' => 3748315548,
                'id_kegiatan' => 4, // Changed from 2 to 4
            ],
            [
                'kode_sub_kegiatan' => '2.08.01.2.02.02',
                'nama_sub_kegiatan' => 'Penyediaan Administrasi Pelaksanaan Tugas ASN',
                'anggaran' => 290984000,
                'realisasi' => 40970000,
                'id_kegiatan' => 4, // Changed from 2 to 4
            ],
            [
                'kode_sub_kegiatan' => '2.08.01.2.02.05',
                'nama_sub_kegiatan' => 'Koordinasi dan Penyusunan Laporan Keuangan Akhir Tahun SKPD',
                'anggaran' => 1348800,
                'realisasi' => 83800,
                'id_kegiatan' => 4, // Changed from 2 to 4
            ],
            [
                'kode_sub_kegiatan' => '2.08.01.2.02.06',
                'nama_sub_kegiatan' => 'Pengelolaan dan Penyiapan Bahan Tanggapan Pemeriksaan',
                'anggaran' => 1500000,
                'realisasi' => 0,
                'id_kegiatan' => 4, // Changed from 2 to 4
            ],
            [
                'kode_sub_kegiatan' => '2.08.01.2.02.07',
                'nama_sub_kegiatan' => 'Koordinasi dan Penyusunan Laporan Keuangan Bulanan/Triwulanan/Semesteran SKPD',
                'anggaran' => 2023200,
                'realisasi' => 0,
                'id_kegiatan' => 4, // Changed from 2 to 4
            ],
            [
                'kode_sub_kegiatan' => '2.08.01.2.02.08',
                'nama_sub_kegiatan' => 'Penyusunan Pelaporan dan Analisis Prognosis Realisasi Anggaran',
                'anggaran' => 899200,
                'realisasi' => 0,
                'id_kegiatan' => 4, // Changed from 2 to 4
            ],

            // Kegiatan 2.08.01.2.05 - Administrasi Kepegawaian Perangkat Daerah (id_kegiatan = 5)
            [
                'kode_sub_kegiatan' => '2.08.01.2.05.05',
                'nama_sub_kegiatan' => 'Monitoring, Evaluasi, dan Penilaian Kinerja Pegawai',
                'anggaran' => 178752760,
                'realisasi' => 37400000,
                'id_kegiatan' => 5, // Changed from 3 to 5
            ],
            [
                'kode_sub_kegiatan' => '2.08.01.2.05.10',
                'nama_sub_kegiatan' => 'Sosialisasi Peraturan Perundang-Undangan',
                'anggaran' => 173500000,
                'realisasi' => 150694000,
                'id_kegiatan' => 5, // Changed from 3 to 5
            ],

            // Kegiatan 2.08.01.2.06 - Administrasi Umum Perangkat Daerah (id_kegiatan = 6)
            [
                'kode_sub_kegiatan' => '2.08.01.2.06.01',
                'nama_sub_kegiatan' => 'Penyediaan Komponen Instalasi Listrik/Penerapan Bangunan Kantor',
                'anggaran' => 6999950,
                'realisasi' => 2212000,
                'id_kegiatan' => 6, // Changed from 4 to 6
            ],
            [
                'kode_sub_kegiatan' => '2.08.01.2.06.02',
                'nama_sub_kegiatan' => 'Penyediaan Peralatan dan Perlengkapan Kantor',
                'anggaran' => 212673880,
                'realisasi' => 5425000,
                'id_kegiatan' => 6, // Changed from 4 to 6
            ],
            [
                'kode_sub_kegiatan' => '2.08.01.2.06.03',
                'nama_sub_kegiatan' => 'Penyediaan Peralatan Rumah Tangga',
                'anggaran' => 37621250,
                'realisasi' => 10402980,
                'id_kegiatan' => 6, // Changed from 4 to 6
            ],
            [
                'kode_sub_kegiatan' => '2.08.01.2.06.04',
                'nama_sub_kegiatan' => 'Penyediaan Bahan Logistik Kantor',
                'anggaran' => 64789880,
                'realisasi' => 9012250,
                'id_kegiatan' => 6, // Changed from 4 to 6
            ],
            [
                'kode_sub_kegiatan' => '2.08.01.2.06.05',
                'nama_sub_kegiatan' => 'Penyediaan Barang Cetakan dan Penggandaan',
                'anggaran' => 26729926,
                'realisasi' => 9776600,
                'id_kegiatan' => 6, // Changed from 4 to 6
            ],
            [
                'kode_sub_kegiatan' => '2.08.01.2.06.08',
                'nama_sub_kegiatan' => 'Fasilitasi Kunjungan Tamu',
                'anggaran' => 71380500,
                'realisasi' => 4202500,
                'id_kegiatan' => 6, // Changed from 4 to 6
            ],
            [
                'kode_sub_kegiatan' => '2.08.01.2.06.09',
                'nama_sub_kegiatan' => 'Penyelenggaraan Rapat Koordinasi dan Konsultasi SKPD',
                'anggaran' => 530882704,
                'realisasi' => 18900000,
                'id_kegiatan' => 6, // Changed from 4 to 6
            ],
            [
                'kode_sub_kegiatan' => '2.08.01.2.06.11',
                'nama_sub_kegiatan' => 'Dukungan Pelaksanaan Sistem Pemerintahan Berbasis Elektronik pada SKPD',
                'anggaran' => 338723370,
                'realisasi' => 0,
                'id_kegiatan' => 6, // Changed from 4 to 6
            ],

            // Kegiatan 2.08.01.2.07 - Pengadaan Barang Milik Daerah Penunjang Urusan Pemerintah Daerah (id_kegiatan = 7)
            [
                'kode_sub_kegiatan' => '2.08.01.2.07.05',
                'nama_sub_kegiatan' => 'Pengadaan Mebel',
                'anggaran' => 79775940,
                'realisasi' => 0,
                'id_kegiatan' => 7, // Changed from 5 to 7
            ],
            [
                'kode_sub_kegiatan' => '2.08.01.2.07.06',
                'nama_sub_kegiatan' => 'Pengadaan Peralatan dan Mesin Lainnya',
                'anggaran' => 97663384,
                'realisasi' => 0,
                'id_kegiatan' => 7, // Changed from 5 to 7
            ],
            [
                'kode_sub_kegiatan' => '2.08.01.2.07.10',
                'nama_sub_kegiatan' => 'Pengadaan Sarana dan Prasarana Gedung Kantor atau Bangunan Lainnya',
                'anggaran' => 14635700,
                'realisasi' => 0,
                'id_kegiatan' => 7, // Changed from 5 to 7
            ],

            // Kegiatan 2.08.01.2.08 - Penyediaan Jasa Penunjang Urusan Pemerintahan Daerah (id_kegiatan = 8)
            [
                'kode_sub_kegiatan' => '2.08.01.2.08.02',
                'nama_sub_kegiatan' => 'Penyediaan Jasa Komunikasi, Sumber Daya Air dan Listrik',
                'anggaran' => 321915600,
                'realisasi' => 52179306,
                'id_kegiatan' => 8, // Changed from 6 to 8
            ],
            [
                'kode_sub_kegiatan' => '2.08.01.2.08.04',
                'nama_sub_kegiatan' => 'Penyediaan Jasa Pelayanan Umum Kantor',
                'anggaran' => 791042280,
                'realisasi' => 381815702,
                'id_kegiatan' => 8, // Changed from 6 to 8
            ],

            // Kegiatan 2.08.01.2.09 - Pemeliharaan Barang Milik Daerah Penunjang Urusan Pemerintahan Daerah (id_kegiatan = 9)
            [
                'kode_sub_kegiatan' => '2.08.01.2.09.01',
                'nama_sub_kegiatan' => 'Penyediaan Jasa Pemeliharaan, Biaya Pemeliharaan dan Pajak Kendaraan Perorangan Dinas atau Kendaraan Dinas Jabatan',
                'anggaran' => 43959999,
                'realisasi' => 6960026,
                'id_kegiatan' => 9, // Changed from 7 to 9
            ],
            [
                'kode_sub_kegiatan' => '2.08.01.2.09.02',
                'nama_sub_kegiatan' => 'Penyediaan Jasa Pemeliharaan, Biaya Pemeliharaan, Pajak, dan Perizinan Kendaraan Dinas Operasional atau Lapangan',
                'anggaran' => 474399969,
                'realisasi' => 103711591,
                'id_kegiatan' => 9, // Changed from 7 to 9
            ],
            [
                'kode_sub_kegiatan' => '2.08.01.2.09.06',
                'nama_sub_kegiatan' => 'Pemeliharaan Peralatan dan Mesin Lainnya',
                'anggaran' => 59243241,
                'realisasi' => 11380000,
                'id_kegiatan' => 9, // Changed from 7 to 9
            ],
            [
                'kode_sub_kegiatan' => '2.08.01.2.09.09',
                'nama_sub_kegiatan' => 'Pemeliharaan/Rehabilitasi Gedung Kantor dan Bangunan Lainnya',
                'anggaran' => 241831000,
                'realisasi' => 89347550,
                'id_kegiatan' => 9, // Changed from 7 to 9
            ],
            [
                'kode_sub_kegiatan' => '2.08.01.2.09.10',
                'nama_sub_kegiatan' => 'Pemeliharaan/Rehabilitasi Sarana dan Prasarana Gedung Kantor atau Bangunan Lainnya',
                'anggaran' => 47005413,
                'realisasi' => 16538450,
                'id_kegiatan' => 9, // Changed from 7 to 9
            ],

            // Kegiatan 2.08.02.2.01 - Pelembagaan Pengarusutamaan Gender (PUG) (id_kegiatan = 10)
            [
                'kode_sub_kegiatan' => '2.08.02.2.01.01',
                'nama_sub_kegiatan' => 'Koordinasi dan Sinkronisasi Perumusan Kebijakan Pelaksanaan PUG',
                'anggaran' => 70590400,
                'realisasi' => 17150000,
                'id_kegiatan' => 10, // Changed from 8 to 10
            ],
            [
                'kode_sub_kegiatan' => '2.08.02.2.01.02',
                'nama_sub_kegiatan' => 'Koordinasi dan Sinkronisasi Pelaksanaan PUG Kewenangan Kabupaten/Kota',
                'anggaran' => 100000000,
                'realisasi' => 0,
                'id_kegiatan' => 10, // Changed from 8 to 10
            ],

            // Kegiatan 2.08.02.2.02 - Pemberdayaan Perempuan Bidang Politik, Hukum, Sosial, dan Ekonomi (id_kegiatan = 11)
            [
                'kode_sub_kegiatan' => '2.08.02.2.02.01',
                'nama_sub_kegiatan' => 'Sosialisasi Peningkatan Partisipasi Perempuan di Bidang Politik, Hukum, Sosial, dan Ekonomi',
                'anggaran' => 100000000,
                'realisasi' => 0,
                'id_kegiatan' => 11, // Changed from 9 to 11
            ],

            // Kegiatan 2.08.02.2.03 - Penguatan dan Pengembangan Penyedia Layanan Pemberdayaan Perempuan (id_kegiatan = 12)
            [
                'kode_sub_kegiatan' => '2.08.02.2.03.01',
                'nama_sub_kegiatan' => 'dvokasi Kebijakan dan Pendampingan kepada Lembaga Penyedia LayananPemberdayaan Perempuan Kewenangan Kabupaten/Kota',
                'anggaran' => 979409600,
                'realisasi' => 98588000,
                'id_kegiatan' => 12, // Changed from 10 to 12
            ],
            [
                'kode_sub_kegiatan' => '2.08.02.2.03.03',
                'nama_sub_kegiatan' => 'Pengembangan Komunikasi, Informasi, dan Edukasi (KIE) Pemberdayaan Perempuan Kewenangan Kabupaten/Kota',
                'anggaran' => 125000000,
                'realisasi' => 0,
                'id_kegiatan' => 12, // Changed from 10 to 12
            ],

            //Kegiatan 2.08.03.2.01 - Pencegahan Kekerasan terhadap Perempuan (id_kegiatan = 13)
            [
                'kode_sub_kegiatan' => '2.08.03.2.01.01',
                'nama_sub_kegiatan' => 'Koordinasi dan Sinkronisasi Pelaksanaan Kebijakan, Program dan Kegiatan Pencegahan Kekerasan terhadap Perempuan Lingkup Daerah Kabupaten/Kota',
                'anggaran' => 220000000,
                'realisasi' => 69092300,
                'id_kegiatan' => 13,
            ],
            //Kegiatan 2.08.03.2.02 - Penyediaan Layanan Rujukan lanjutan Bagi Perempuan Korban kekerasan yang memerlukan Koordinasi Kewenangan Kabupaten/Kota (id_kegiatan = 14)
            [
                'kode_sub_kegiatan' => '2.08.03.2.02.01',
                'nama_sub_kegiatan' => 'Penyediaan Layanan Pengaduan Masyarakat bagi Perempuan Korban Kekerasan Kewenangan Kabupaten/Kota',
                'anggaran' => 633611720,
                'realisasi' => 77614110,
                'id_kegiatan' => 14,
            ],
            //Kegiatan 2.08.03.2.03 - Penguatan dan Pengembangan Lembaga Penyedia Layanan Perlindungan Perempuan (id_kegiatan = 15)
            [
                'kode_sub_kegiatan' => '2.08.03.2.03.01',
                'nama_sub_kegiatan' => 'Advokasi Kebijakan dan Pendampingan Penyediaan Sarana Prasarana Layanan bagi Perempuan Korban Kekerasan Kewenangan Kabupaten/Kota',
                'anggaran' => 202186050,
                'realisasi' => 1250000,
                'id_kegiatan' => 15,
            ],
            [
                'kode_sub_kegiatan' => '2.08.03.2.03.03',
                'nama_sub_kegiatan' => 'Penyediaan Kebutuhan Spesifik bagi Perempuan dalam Situasi Darurat dan Kondisi Khusus Kewenangan Kabupaten/Kota',
                'anggaran' => 149313950,
                'realisasi' => 4168000,
                'id_kegiatan' => 15,
            ],
            [
                'kode_sub_kegiatan' => '2.08.03.2.03.04',
                'nama_sub_kegiatan' => 'Penguatan Jejaring antar Lembaga Penyedia Layanan Perlindungan Perempuan Kewenangan Kabupaten/Kota',
                'anggaran' => 140000000,
                'realisasi' => 1275000,
                'id_kegiatan' => 15,
            ],
            //Kegiatan 2.08.04.2.01 - Peningkatan Kualitas Keluarga dalam Mewujudkan Kesetaraan Gender (KG) dan Hak Anak Tingkat Daerah Kabupaten/Kota(id_kegiatan = 16)
            [
                'kode_sub_kegiatan' => '2.08.04.2.01.01',
                'nama_sub_kegiatan' => 'Advokasi Kebijakan dan Pendampingan untuk Mewujudkan KG dan Perlindungan Anak Kewenangan Kabupaten/Kota',
                'anggaran' => 200000000,
                'realisasi' => 20460000,
                'id_kegiatan' => 16,
            ],
            [
                'kode_sub_kegiatan' => '2.08.04.2.01.02',
                'nama_sub_kegiatan' => 'Pelaksanaan Komunikasi, Informasi dan Edukasi KG dan Perlindungan Anak bagi Keluarga Kewenangan Kabupaten/Kota',
                'anggaran' => 100000000,
                'realisasi' => 1954000,
                'id_kegiatan' => 16,
            ],
            [
                'kode_sub_kegiatan' => '2.08.04.2.01.03',
                'nama_sub_kegiatan' => 'Pengembangan Kegiatan Masyarakat untuk Peningkatan Kualitas Keluarga Kewenangan Kabupaten/Kota',
                'anggaran' => 100000000,
                'realisasi' => 8494000,
                'id_kegiatan' => 16,
            ],
            //Kegiatan 2.08.04.2.02 - Penguatan dan Pengembangan Lembaga Penyedia Layanan Peningkatan Kualitas Keluarga dalam Mewujudkan KG dan Hak Anak yang Wilayah Kerjanya dalam Daerah Kabupaten/Kota(id_kegiatan = 17)
            [
                'kode_sub_kegiatan' => '2.08.04.2.02.01',
                'nama_sub_kegiatan' => 'Advokasi Kebijkaan dan Pendampingan Pengembangan Lembaga Penyedia Layanan Peningkatan Kualitas Keluarga Tingkat Daerah Kabupaten/Kota',
                'anggaran' => 154828200,
                'realisasi' => 35495900,
                'id_kegiatan' => 17,
            ],
            //Kegiatan 2.08.05.2.01 - Pengumpulan, Pengolahan Analisis dan penyajian Data Gender dan Anak Dalam Kelembagaan Data di Tingkat Daerah Kabupaten/Kota (id_kegiatan = 18)
            [
                'kode_sub_kegiatan' => '2.08.05.2.01.01',
                'nama_sub_kegiatan' => 'Penyediaan Data Gender dan Anak di Kewenangan Kabupaten/Kota',
                'anggaran' => 86277990,
                'realisasi' => 3410000,
                'id_kegiatan' => 18,
            ],
            [
                'kode_sub_kegiatan' => '2.08.05.2.01.02',
                'nama_sub_kegiatan' => 'Penyajian dan Pemanfaatan Data Gender dan Anak dalam Kelembagaan Data di Kewenangan Kabupaten/Kota',
                'anggaran' => 196118680,
                'realisasi' => 4100000,
                'id_kegiatan' => 18,
            ],
            // Kegiatan 2.08.06.2.01 - Pelembagaan PHA pada Lembaga Pemerintah,Nonpemerintah, dan Dunia Usaha Kewenangan Kabupaten/Kota (id_kegiatan = 19)
            [
                'kode_sub_kegiatan' => '2.08.06.2.01.01',
                'nama_sub_kegiatan' => 'Advokasi Kebijakan dan Pendampingan Pemenuhan Hak Anak pada Lembaga Pemerintah, Non Pemerintah, Media dan Dunia Usaha Kewenangan Kabupaten/Kota',
                'anggaran' => 20000000,
                'realisasi' => 0,
                'id_kegiatan' => 19,
            ],
            [
                'kode_sub_kegiatan' => '2.08.06.2.01.02',
                'nama_sub_kegiatan' => 'Koordinasi dan Sinkronisasi Pelembagaan Pemenuhan Hak Anak kewenangan Kabupaten/Kota',
                'anggaran' => 250000000,
                'realisasi' => 2745000,
                'id_kegiatan' => 19,
            ],
            //Kegiatan 2.08.06.2.02 - Penguatan dan Pengembangan Lembaga Penyedia Layanan Peningkatan Kualitas Hidup Anak Kewenangan Kabupaten/Kota (id_kegiatan = 20)
            [
                'kode_sub_kegiatan' => '2.08.06.2.02.01',
                'nama_sub_kegiatan' => 'Penyediaan Layanan Peningkatan Kualtias Hidup Anak Kewenangan Kabupaten/Kota',
                'anggaran' => 258397480,
                'realisasi' => 87162200,
                'id_kegiatan' => 20,
            ],
            [
                'kode_sub_kegiatan' => '2.08.06.2.02.02',
                'nama_sub_kegiatan' => 'Koordinasi dan Sinkronisasi Pelaksanaan Pendampingan Peningkatan Kualitas Hidup Anak Tingkat Daerah Kabupaten/Kota',
                'anggaran' => 139919780,
                'realisasi' => 32650000,
                'id_kegiatan' => 20,
            ],
            [
                'kode_sub_kegiatan' => '2.08.06.2.02.03',
                'nama_sub_kegiatan' => 'Pengembangan Komunikasi, Informasi dan Edukasi Pemenuhan Hak Anak bagi Lembaga Penyedia Layanan Peningkatan Kualitas Hidup Anak Tingkat Daerah Kabupaten/Kota',
                'anggaran' => 110080220,
                'realisasi' => 16739997,
                'id_kegiatan' => 20,
            ],
            [
                'kode_sub_kegiatan' => '2.08.06.2.02.04',
                'nama_sub_kegiatan' => 'Penguatan Jejaring antar Lembaga Penyedia Layanan Peningkatan Kualitas Hidup Anak Tingkat Daerah Kabupaten/Kota',
                'anggaran' => 130000000,
                'realisasi' => 0,
                'id_kegiatan' => 20,
            ],
            // Kegiatan 2.08.07.2.01 - Pencegahan Kekerasan Terhadap Anak yang Melibatkan Para Pihak Lingkup Daerah Kabupaten/Kota (id_kegiatan = 21)
            [
                'kode_sub_kegiatan' => '2.08.07.2.01.06',
                'nama_sub_kegiatan' => 'Koordinasi dan sinkronisasi pencegahan kekerasan terhadap anak kewenangan kabupaten/kota',
                'anggaran' => 340000000,
                'realisasi' => 14950000,
                'id_kegiatan' => 21,
            ],
            // Kegiatan 2.13.04.2.01 - Pembinaan dan Pengawasan Penyelenggaraan Administrasi Pemerintahan Desa (id_kegiatan = 22)
            [
                'kode_sub_kegiatan' => '2.13.04.2.01.02',
                'nama_sub_kegiatan' => 'Fasilitasi Penyusunan Produk Hukum Desa',
                'anggaran' => 47007550,
                'realisasi' => 15900000,
                'id_kegiatan' => 22,
            ],
            [
                'kode_sub_kegiatan' => '2.13.04.2.01.04',
                'nama_sub_kegiatan' => 'Fasilitasi Pengelolaan Keuangan Desa',
                'anggaran' => 23987300,
                'realisasi' => 0,
                'id_kegiatan' => 22,
            ],
            [
                'kode_sub_kegiatan' => '2.13.04.2.01.05',
                'nama_sub_kegiatan' => 'Pembinaan Peningkatan Kapasitas Aparatur Pemerintahan Desa',
                'anggaran' => 81197000,
                'realisasi' => 0,
                'id_kegiatan' => 22,
            ],
            [
                'kode_sub_kegiatan' => '2.13.04.2.01.11',
                'nama_sub_kegiatan' => 'Fasilitasi Penyusunan Profil Desa',
                'anggaran' => 42886730,
                'realisasi' => 0,
                'id_kegiatan' => 22,
            ],
            [
                'kode_sub_kegiatan' => '2.13.04.2.01.18',
                'nama_sub_kegiatan' => 'Fasilitasi Evaluasi Perkembangan Desa sertaa Lomba Desa dan Kelurahan',
                'anggaran' => 546469150,
                'realisasi' => 8300000,
                'id_kegiatan' => 22,
            ],
            // Kegiatan 2.13.05.2.01 - Pemberdayaan Lembaga Kemasyarakatan yang Bergerak di Bidang Pemberdayaan Desa dan Lembaga Adat Tingkat Daerah Kabupaten/Kota serta Pemberdayaan Masyarakat Hukum Adat yang Masyarakat Pelakunya Hukum Adat yang Sama dalam Daerah Kabupaten/Kota (id_kegiatan = 23)
            [
                'kode_sub_kegiatan' => '2.13.05.2.01.02',
                'nama_sub_kegiatan' => 'Fasilitasi Penataan, Pemberdayaan dan Pendayagunaan Kelembagaan Kemasyarakatan Desa/Kelurahan (RT,RW,PKK,Posyandu,LPM dan Karang Taruna),Lembaga Adat Desa/Kelurahan dan Masyarakat Hukum Adat',
                'anggaran' => 151026360,
                'realisasi' => 7425000,
                'id_kegiatan' => 23,
            ],
            [
                'kode_sub_kegiatan' => '2.13.05.2.01.03',
                'nama_sub_kegiatan' => 'Peningkatan Kapasitas Kelembagaan Lembaga Kemasyarakatan Desa/Kelurahan (RT,RW,PKK,Posyandu,LPM dan Karang Taruna), Lembaga Adat Desa/Kelurahan dan Masyarakat Hukum Adat',
                'anggaran' => 144926650,
                'realisasi' => 0,
                'id_kegiatan' => 23,
            ],
            [
                'kode_sub_kegiatan' => '2.13.05.2.01.05',
                'nama_sub_kegiatan' => 'Fasilitasi Pengembangan Usaha Ekonomi Masyarakat dan Pemerintah Desa dalam Meningkatkan Pendapatan Asli Desa',
                'anggaran' => 1081275230,
                'realisasi' => 349659281,
                'id_kegiatan' => 23,
            ],
            [
                'kode_sub_kegiatan' => '2.13.05.2.01.06',
                'nama_sub_kegiatan' => 'Fasilitasi Pemerintah Desa dalam Pemanfaatan Teknologi Tepat Guna',
                'anggaran' => 100988610,
                'realisasi' => 17700000,
                'id_kegiatan' => 23,
            ],
            [
                'kode_sub_kegiatan' => '2.13.05.2.01.07',
                'nama_sub_kegiatan' => 'Fasilitasi Bulan Bhakti Gotong Royong Masyarakat',
                'anggaran' => 100027160,
                'realisasi' => 0,
                'id_kegiatan' => 23,
            ],
            [
                'kode_sub_kegiatan' => '2.13.05.2.01.09',
                'nama_sub_kegiatan' => 'Fasilitasi Tim Penggerak PKK dalam Penyelenggaraan Gerakan Pemberdayaan dan Masyarakat dan Kesejahteraan Keluarga',
                'anggaran' => 100027160,
                'realisasi' => 0,
                'id_kegiatan' => 23,
            ],
        ];

        DB::table('sub_kegiatan')->insert($subKegiatan);
    }
}
