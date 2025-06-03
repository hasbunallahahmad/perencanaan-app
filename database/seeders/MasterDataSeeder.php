<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Organisasi;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil ID organisasi yang sudah dibuat oleh DinasSeeder
        $organisasi = Organisasi::where('nama', 'like', '%Dinas Pemberdayaan Perempuan dan Perlindungan Anak%')->first();

        if (!$organisasi) {
            $this->command->error('❌ Organisasi tidak ditemukan! Pastikan DinasSeeder sudah dijalankan terlebih dahulu.');
            return;
        }

        $this->command->info('🚀 Memulai seeding master data untuk: ' . $organisasi->nama);

        // Insert program data
        $programs = [
            ['2.08.01', 'PROGRAM PENUNJANG URUSAN PEMERINTAHAN DAERAH KABUPATEN/KOTA'],
            ['2.08.02', 'PROGRAM PENGARUSUTAMAAN GENDER DAN PEMBERDAYAAN PEREMPUAN'],
            ['2.08.03', 'PROGRAM PERLINDUNGAN PEREMPUAN'],
            ['2.08.04', 'PROGRAM PENINGKATAN KUALITAS KELUARGA'],
            ['2.08.05', 'PROGRAM PENGELOLAAN SISTEM DATA GENDER DAN ANAK'],
            ['2.08.06', 'PROGRAM PEMENUHAN HAK ANAK (PHA)'],
            ['2.08.07', 'PROGRAM PERLINDUNGAN KHUSUS ANAK'],
            ['2.13.04', 'PROGRAM ADMINISTRASI PEMERINTAHAN DESA'],
            ['2.13.05', 'PROGRAM PEMBERDAYAAN LEMBAGA KEMASYARAKATAN, LEMBAGA ADAT DAN MASYARAKAT HUKUM ADAT']
        ];

        $this->command->info('📋 Menyimpan data program...');
        $programIds = [];
        foreach ($programs as $program) {
            $programId = DB::table('program')->insertGetId([
                'kode_program' => $program[0],
                'nama_program' => $program[1],
                'organisasi_id' => $organisasi->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $programIds[$program[0]] = $programId;
        }

        // Insert kegiatan data
        $kegiatans = [
            // Program 2.08.01 - PROGRAM PENUNJANG URUSAN PEMERINTAHAN DAERAH
            ['2.08.01.2.01', 'Perencanaan, Penganggaran, dan Evaluasi Kinerja Perangkat Daerah', '2.08.01'],
            ['2.08.01.2.02', 'Administrasi Keuangan Perangkat Daerah', '2.08.01'],
            ['2.08.01.2.05', 'Administrasi Kepegawaian Perangkat Daerah', '2.08.01'],
            ['2.08.01.2.06', 'Administrasi Umum Perangkat Daerah', '2.08.01'],
            ['2.08.01.2.07', 'Pengadaan Barang Milik Daerah Penunjang Urusan Pemerintah Daerah', '2.08.01'],
            ['2.08.01.2.08', 'Penyediaan Jasa Penunjang Urusan Pemerintahan Daerah', '2.08.01'],
            ['2.08.01.2.09', 'Pemeliharaan Barang Milik Daerah Penunjang Urusan Pemerintahan Daerah', '2.08.01'],

            // Program 2.08.02 - PROGRAM PENGARUSUTAMAAN GENDER DAN PEMBERDAYAAN PEREMPUAN
            ['2.08.02.2.01', 'Pelembagaan Pengarusutamaan Gender (PUG) pada Lembaga Pemerintah Kewenangan Kabupaten/Kota', '2.08.02'],
            ['2.08.02.2.02', 'Pemberdayaan Perempuan Bidang Politik, Hukum, Sosial, dan Ekonomi pada Organisasi Kemasyarakatan Kewenangan Kabupaten/Kota', '2.08.02'],
            ['2.08.02.2.03', 'Penguatan dan Pengembangan Penyedia Layanan Pemberdayaan Perempuan Kewenagan Kabupaten/Kota', '2.08.02'],

            // Program 2.08.03 - PROGRAM PERLINDUNGAN PEREMPUAN
            ['2.08.03.2.01', 'Pencegahan Tindak Kekerasan terhadap Perempuan Kewenangan Kabupaten/Kota', '2.08.03'],
            ['2.08.03.2.02', 'Penanganan Korban Kekerasan terhadap Perempuan Kewenangan Kabupaten/Kota', '2.08.03'],

            // Program 2.08.04 - PROGRAM PENINGKATAN KUALITAS KELUARGA
            ['2.08.04.2.01', 'Penguatan Kapasitas Lembaga dalam Peningkatan Kualitas Keluarga Kewenangan Kabupaten/Kota', '2.08.04'],
            ['2.08.04.2.02', 'Pengembangan Wawasan dan Keterampilan Keluarga dalam Pembangunan Kewenangan Kabupaten/Kota', '2.08.04'],

            // Program 2.08.05 - PROGRAM PENGELOLAAN SISTEM DATA GENDER DAN ANAK
            ['2.08.05.2.01', 'Penguatan Sistem Data Gender dan Anak Kewenangan Kabupaten/Kota', '2.08.05'],

            // Program 2.08.06 - PROGRAM PEMENUHAN HAK ANAK (PHA)
            ['2.08.06.2.01', 'Pemenuhan Hak Sipil dan Kebebasan Anak Kewenangan Kabupaten/Kota', '2.08.06'],
            ['2.08.06.2.02', 'Pemenuhan Hak Lingkungan Keluarga dan Pengasuhan Alternatif Anak Kewenangan Kabupaten/Kota', '2.08.06'],
            ['2.08.06.2.03', 'Pemenuhan Hak Kesehatan Dasar dan Kesejahteraan Anak Kewenangan Kabupaten/Kota', '2.08.06'],
            ['2.08.06.2.04', 'Pemenuhan Hak Pendidikan, Pemanfaatan Waktu Luang dan Kegiatan Budaya Anak Kewenangan Kabupaten/Kota', '2.08.06'],

            // Program 2.08.07 - PROGRAM PERLINDUNGAN KHUSUS ANAK
            ['2.08.07.2.01', 'Pencegahan dari Perlakuan Salah, Eksploitasi, dan Penelantaran terhadap Anak Kewenangan Kabupaten/Kota', '2.08.07'],
            ['2.08.07.2.02', 'Penanganan Anak yang Memerlukan Perlindungan Khusus Kewenangan Kabupaten/Kota', '2.08.07'],

            // Program 2.13.04 - PROGRAM ADMINISTRASI PEMERINTAHAN DESA
            ['2.13.04.2.01', 'Fasilitasi Administrasi Pemerintahan Desa/Kelurahan Kewenangan Kabupaten/Kota', '2.13.04'],

            // Program 2.13.05 - PROGRAM PEMBERDAYAAN LEMBAGA KEMASYARAKATAN
            ['2.13.05.2.01', 'Pemberdayaan Lembaga Kemasyarakatan Kewenangan Kabupaten/Kota', '2.13.05'],
            ['2.13.05.2.02', 'Pemberdayaan Lembaga Adat dan Masyarakat Hukum Adat Kewenangan Kabupaten/Kota', '2.13.05']
        ];

        $this->command->info('🎯 Menyimpan data kegiatan...');
        $kegiatanIds = [];
        foreach ($kegiatans as $kegiatan) {
            $kegiatanId = DB::table('kegiatan')->insertGetId([
                'kode_kegiatan' => $kegiatan[0],
                'nama_kegiatan' => $kegiatan[1],
                'id_program' => $programIds[$kegiatan[2]],
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $kegiatanIds[$kegiatan[0]] = $kegiatanId;
        }

        // Insert sub kegiatan data
        $subKegiatans = [
            // Sub Kegiatan untuk Perencanaan, Penganggaran, dan Evaluasi
            ['2.08.01.2.01.0001', 'Penyusunan Dokumen Perencanaan Perangkat Daerah', '2.08.01.2.01'],
            ['2.08.01.2.01.0002', 'Koordinasi dan Penyusunan Dokumen RKA-SKPD', '2.08.01.2.01'],
            ['2.08.01.2.01.0003', 'Koordinasi dan Penyusunan Dokumen Perubahan RKA-SKPD', '2.08.01.2.01'],
            ['2.08.01.2.01.0004', 'Koordinasi dan Penyusunan DPA-SKPD', '2.08.01.2.01'],
            ['2.08.01.2.01.0005', 'Koordinasi dan Penyusunan Perubahan DPA-SKPD', '2.08.01.2.01'],
            ['2.08.01.2.01.0006', 'Evaluasi Kinerja Perangkat Daerah', '2.08.01.2.01'],

            // Sub Kegiatan untuk Administrasi Keuangan
            ['2.08.01.2.02.0001', 'Penyediaan Gaji dan Tunjangan ASN', '2.08.01.2.02'],
            ['2.08.01.2.02.0002', 'Penyediaan Administrasi Pelaksanaan Tugas ASN', '2.08.01.2.02'],
            ['2.08.01.2.02.0003', 'Koordinasi dan Penyusunan Laporan Capaian Kinerja dan Ikhtisar Realisasi Kinerja SKPD', '2.08.01.2.02'],
            ['2.08.01.2.02.0004', 'Koordinasi dan Penyusunan Laporan Keuangan Akhir Tahun SKPD', '2.08.01.2.02'],

            // Sub Kegiatan untuk Administrasi Kepegawaian
            ['2.08.01.2.05.0001', 'Penyediaan Administrasi Pelaksanaan Tugas ASN', '2.08.01.2.05'],
            ['2.08.01.2.05.0002', 'Koordinasi dan Pelaksanaan Mutasi ASN', '2.08.01.2.05'],
            ['2.08.01.2.05.0003', 'Penyusunan Kebutuhan Pengembangan Kompetensi ASN', '2.08.01.2.05'],

            // Sub Kegiatan untuk Administrasi Umum
            ['2.08.01.2.06.0001', 'Penyediaan Komponen Instalasi Listrik/Penerangan Bangunan Kantor', '2.08.01.2.06'],
            ['2.08.01.2.06.0002', 'Penyediaan Peralatan dan Perlengkapan Kantor', '2.08.01.2.06'],
            ['2.08.01.2.06.0003', 'Penyediaan Peralatan Rumah Tangga', '2.08.01.2.06'],
            ['2.08.01.2.06.0004', 'Penyediaan Bahan Logistik Kantor', '2.08.01.2.06'],
            ['2.08.01.2.06.0005', 'Penyediaan Barang Cetakan dan Penggandaan', '2.08.01.2.06'],
            ['2.08.01.2.06.0006', 'Penyediaan Bahan Bacaan dan Peraturan Perundang-undangan', '2.08.01.2.06'],

            // Sub Kegiatan PUG
            ['2.08.02.2.01.0001', 'Fasilitasi Peningkatan Kapasitas Kelembagaan PUG', '2.08.02.2.01'],
            ['2.08.02.2.01.0002', 'Penyusunan Profil Gender dan Anak', '2.08.02.2.01'],
            ['2.08.02.2.01.0003', 'Sosialisasi dan Advokasi PUG', '2.08.02.2.01'],

            // Sub Kegiatan Pemberdayaan Perempuan
            ['2.08.02.2.02.0001', 'Peningkatan Partisipasi Perempuan dalam Politik dan Kepemimpinan', '2.08.02.2.02'],
            ['2.08.02.2.02.0002', 'Peningkatan Akses Perempuan terhadap Keadilan dan Hukum', '2.08.02.2.02'],
            ['2.08.02.2.02.0003', 'Peningkatan Kapasitas Ekonomi Perempuan', '2.08.02.2.02'],

            // Sub Kegiatan Perlindungan Perempuan
            ['2.08.03.2.01.0001', 'Sosialisasi dan Advokasi Pencegahan Kekerasan terhadap Perempuan', '2.08.03.2.01'],
            ['2.08.03.2.02.0001', 'Penyediaan Layanan Konseling dan Rehabilitasi Korban Kekerasan', '2.08.03.2.02'],
            ['2.08.03.2.02.0002', 'Koordinasi Penanganan Korban Kekerasan terhadap Perempuan', '2.08.03.2.02'],

            // Sub Kegiatan Peningkatan Kualitas Keluarga
            ['2.08.04.2.01.0001', 'Penguatan Kapasitas Lembaga Keluarga', '2.08.04.2.01'],
            ['2.08.04.2.02.0001', 'Pendidikan dan Pelatihan Keterampilan Keluarga', '2.08.04.2.02'],

            // Sub Kegiatan Data Gender dan Anak
            ['2.08.05.2.01.0001', 'Pengembangan Sistem Informasi Gender dan Anak', '2.08.05.2.01'],
            ['2.08.05.2.01.0002', 'Pemutakhiran Data Gender dan Anak', '2.08.05.2.01'],

            // Sub Kegiatan Pemenuhan Hak Anak
            ['2.08.06.2.01.0001', 'Fasilitasi Kepemilikan Akta Kelahiran Anak', '2.08.06.2.01'],
            ['2.08.06.2.02.0001', 'Penguatan Kapasitas Keluarga dalam Pengasuhan Anak', '2.08.06.2.02'],
            ['2.08.06.2.03.0001', 'Advokasi Pemenuhan Hak Kesehatan Anak', '2.08.06.2.03'],
            ['2.08.06.2.04.0001', 'Fasilitasi Partisipasi Anak dalam Pendidikan dan Budaya', '2.08.06.2.04'],

            // Sub Kegiatan Perlindungan Khusus Anak
            ['2.08.07.2.01.0001', 'Sosialisasi Pencegahan Kekerasan terhadap Anak', '2.08.07.2.01'],
            ['2.08.07.2.02.0001', 'Penanganan Anak Berhadapan dengan Hukum', '2.08.07.2.02'],
            ['2.08.07.2.02.0002', 'Penanganan Anak Korban Kekerasan dan Eksploitasi', '2.08.07.2.02']
        ];

        $this->command->info('🎨 Menyimpan data sub kegiatan...');
        $subKegiatanIds = [];
        foreach ($subKegiatans as $subKegiatan) {
            $subKegiatanId = DB::table('sub_kegiatan')->insertGetId([
                'kode_sub_kegiatan' => $subKegiatan[0],
                'nama_sub_kegiatan' => $subKegiatan[1],
                'id_kegiatan' => $kegiatanIds[$subKegiatan[2]],
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $subKegiatanIds[$subKegiatan[0]] = $subKegiatanId;
        }

        // Insert sample serapan anggaran data
        $this->command->info('💰 Menyimpan data serapan anggaran...');
        $sampleSubKegiatanCodes = array_slice(array_keys($subKegiatanIds), 0, 15); // Ambil 15 sub kegiatan pertama

        foreach ($sampleSubKegiatanCodes as $index => $subKegiatanCode) {
            $baseAnggaran = ($index + 1) * 25000000; // Variasi anggaran
            $realisasiPersen = rand(20, 50) / 100; // 20-50% realisasi

            DB::table('serapan_anggaran')->insert([
                'id_sub_kegiatan' => $subKegiatanIds[$subKegiatanCode],
                'tahun' => 2025,
                'bulan' => 4, // April
                'anggaran' => $baseAnggaran,
                'realisasi' => $baseAnggaran * $realisasiPersen,
                'keterangan' => 'Serapan Q1 Tahun 2025',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Output hasil seeding
        $this->command->info('');
        $this->command->info('✅ Master Data Seeder berhasil dijalankan!');
        $this->command->info('📊 Data yang berhasil dibuat:');
        $this->command->info('   - ' . count($programs) . ' Program');
        $this->command->info('   - ' . count($kegiatans) . ' Kegiatan');
        $this->command->info('   - ' . count($subKegiatans) . ' Sub Kegiatan');
        $this->command->info('   - ' . count($sampleSubKegiatanCodes) . ' Data Serapan Anggaran');
        $this->command->info('');
        $this->command->info('🎉 Semua data master telah berhasil disimpan untuk: ' . $organisasi->nama);
    }
}
