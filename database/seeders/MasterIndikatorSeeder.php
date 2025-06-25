<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterIndikator;
use Carbon\Carbon;

class MasterIndikatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $indikators = [
            'Persentase perencanaan serta pelaporan kinerja dan keuangan Dinas Pemberdayaan Perempuan dan Perlindungan Anak yang berkualitas',
            'Persentase kinerja pelayanan umum dan kepegawaian Dinas Pemberdayaan Perempuan dan Perlindungan Anak',
            'Jumlah dokumen dan laporan perencanaan dan evaluasi kinerja yang dihasilkan',
            'Jumlah Dokumen Perencanaan Perangkat Daerah',
            'Jumlah Dokumen RKA-SKPD dan Laporan Hasil Koordinasi Penyusunan Dokumen RKA-SKPD',
            'Jumlah Dokumen Perubahan RKA-SKPD dan Laporan Hasil Koordinasi Penyusunan Dokumen Perubahan RKA-SKPD',
            'Jumlah Dokumen DPA-SKPD dan Laporan Hasil Koordinasi Penyusunan Dokumen DPA-SKPD',
            'Jumlah Dokumen Perubahan DPA-SKPD dan Laporan Hasil Koordinasi Penyusunan Dokumen Perubahan DPA-SKPD',
            'Jumlah Laporan Capaian Kinerja dan Ikhtisar Realisasi Kinerja SKPD dan Laporan Hasil Koordinasi Penyusunan Laporan Capaian Kinerja dan Ikhtisar Realisasi Kinerja SKPD',
            'Jumlah Laporan Evaluasi Kinerja Perangkat Daerah',
            'Persentase kinerja administrasi dan pelaporan keuangan',
            'Jumlah Orang yang Menerima Gaji dan Tunjangan ASN',
            'Jumlah Dokumen Hasil Penyediaan Administrasi Pelaksanaan Tugas ASN',
            'Jumlah Laporan Keuangan Akhir Tahun SKPD dan Laporan Hasil Koordinasi Penyusunan Laporan Keuangan Akhir Tahun SKPD',
            'Jumlah Dokumen Bahan Tanggapan Pemeriksaan dan Tindak Lanjut Pemeriksaan',
            'Jumlah Laporan Keuangan Bulanan/ Triwulanan/ Semesteran SKPD dan Laporan Koordinasi Penyusunan Laporan Keuangan Bulanan/Triwulanan/Semesteran SKPD',
            'Jumlah Dokumen Pelaporan dan Analisis Prognosis Realisasi Anggaran',
            'Cakupan peningkatan Pelayanan sumber daya aparatur',
            'Jumlah Dokumen Monitoring, Evaluasi, dan Penilaian Kinerja Pegawai',
            'Jumlah Orang yang Mengikuti Sosialisasi Peraturan Perundang-Undangan',
            'Cakupan pelaksanaan administrasi umum',
            'Jumlah Paket Komponen Instalasi Listrik/Penerangan Bangunan Kantor yang Disediakan',
            'Jumlah Paket Peralatan dan Perlengkapan Kantor yang Disediakan',
            'Jumlah Paket Peralatan Rumah Tangga yang Disediakan',
            'Jumlah Paket Bahan Logistik Kantor yang Disediakan',
            'Jumlah Paket Barang Cetakan dan Penggandaan yang Disediakan',
            'Jumlah Laporan Fasilitasi Kunjungan Tamu',
            'Jumlah Laporan Penyelenggaraan Rapat Koordinasi dan Konsultasi SKPD',
            'Jumlah Dokumen Dukungan Pelaksanaan Sistem Pemerintahan Berbasis Elektronik pada SKPD',
            'Persentase tersedianya peralatan dan perlengkapan kantor',
            'Jumlah Unit Kendaraan Dinas Operasional atau Lapangan yang Disediakan',
            'Jumlah Paket Mebel yang Disediakan',
            'Jumlah Unit Peralatan dan Mesin Lainnya yang Disediakan',
            'Jumlah Unit Sarana dan Prasarana Gedung Kantor atau Bangunan Lainnya yang Disediakan',
            'Persentase tersedianya kebutuhan jasa kantor',
            'Jumlah Laporan Penyediaan Jasa Komunikasi, Sumber Daya Air dan Listrik yang Disediakan',
            'Jumlah Laporan Penyediaan Jasa Pelayanan Umum Kantor yang Disediakan',
            'Persentase pemeliharaan aset dinas',
            'Jumlah Kendaraan Perorangan Dinas atau Kendaraan Dinas Jabatan yang Dipelihara dan dibayarkan Pajaknya',
            'Jumlah Kendaraan Dinas Operasional atau Lapangan yang Dipelihara dan dibayarkan Pajak dan Perizinannya',
            'Jumlah Peralatan dan Mesin Lainnya yang Dipelihara',
            'Jumlah Gedung Kantor dan Bangunan Lainnya yang Dipelihara/ Direhabilitasi',
            'Jumlah Sarana dan Prasarana Gedung Kantor atau Bangunan Lainnya yang Dipelihara/ Direhabilitasi',
            'Persentase peningkatan Anggaran Responsif Gender (ARG)',
            'Persentase kelurahan ramah perempuan dan peduli anak yang menerapkan kebijakan afirmatif',
            'Jumlah kegiatan pelembagaan PUG',
            'Jumlah Dokumen Hasill Koordinasi dan Sinkronisasi Perumusan Kebijakan Pengarustamaan Gender (PUG)',
            'Jumlah kebijakan penyelenggaraan PUG di tingkat provinsi',
            'Jumlah dokumen hasil monitoring dan evaluasi penyelenggaraan PUG kewenangan Kabupaten/Kota',
            'Jumlah kegiatan pemberdayaan perempuan bidang politik, hukum, sosial, dan ekonomi',
            'Jumlah SDM lembaga masyarakat dan perempuan yang mendapatkan sosialisasi tentang pemberdayaan',
            'jumlah SDM lembaga masyarakat, perempuan potensial, perempuan penyintas kekerasan dan atau rentan lainnya yang mendapatkan bimtek atau pelatihan',
            'Jumlah lembaga layanan pemberdayaan perempuan yang terfasilitasi',
            'Prevalensi kekerasan terhadap perempuan yang tertangani',
            'Jumlah kegiatan pencegahan kekerasan terhadap perempuan',
            'Jumlah pengambil kebijakan dan pemangku kepentingan yang mendapatkan Advokasi dan sosialisasi pencegahan KtP tingkat provinsi, masyarakat, serta Kab/Kota',
            'Jumlah layanan penanganan kasus bagi perempuan korban kekerasan',
            'Jumlah Korban yang mendapatkan Layanan kesehatan yang tidak dijamin BPJS, Jamkesda, dan sumber pendanaan lainnya bagi Perempuan Korban Kekerasan Tingkat  Kabupaten/Kota',
            'Jumlah Perempuan Korban Kekerasan yang mendapatkan layanan gelar kasus bagi Perempuan Korban Kekerasan Tingkat Kabupaten/Kota',
            'Jumlah Korban yang mendapatkan Layanan pendampingan tenaga ahli bagi Perempuan Korban Kekerasan Tingkat Kabupaten/Kota',
            'Jumlah Perempuan Korban Kekerasan yang mendapatkan layanan rumah perlindungan bagi Perempuan Korban Kekerasan Tingkat  Kabupaten/Kota',
            'Jumlah Perempuan Korban Kekerasan yang mendapatkan Layanan medikolegal bagi Perempuan Korban Kekerasan Tingkat Kabupaten/Kota',
            'Jumlah Perempuan Korban Kekerasan yang mendapatkan Layanan  Pengaduan atau Penjangkauan korban Tingkat Kabupaten/Kota',
            'Jumlah Perempuan Korban Kekerasan yang mendapatkan pendampingan korban Tingkat Kabupaten/Kota',
            'Jumlah kegiatan penguatan dan pengembangan lembaga layanan perlindungan perempuan',
            'Jumlah lembaga penyedia Layanan Perlindungan Perempuan yang mendapatkan pendampingan',
            'Jumlah SDM lembaga penyedia Layanan Perlindungan Perempuan yang mendapatkan bimtek',
            'Persentase layanan peningkatan kualitas keluarga',
            'Jumlah kegiatan peningkatan kualitas keluarga dalam mewujudkan kesetaraan gender',
            'Jumlah dokumen monitoring evaluasi dan pelaporan dalam peningkatan kualitas keluarga untuk Mewujudkan KG dan Perlindungan Anak Kewenangan Kabupaten/Kota (Proses perumusan kebijakan yang terintegrasi dan berbasis data untuk meningkatkan dimensi-dimensi kualitas keluarga, termasuk legalitas, ketahanan fisik, ekonomi, sosial psikologis, dan sosial budaya, guna menjamin tercapainya kesetaraan gender dan perlindungan hak anak di tingkat kabupaten/kota, sesuai dengan kebijakan pembangunan daerah yang ditetapkan oleh pemerintah)',
            'Jumlah layanan peningkatan kualitas keluarga',
            'Jumlah keluarga yang mendapatkan layanan penjangkauan Lingkup Kabupaten/Kota',
            'Jumlah keluarga yang mendapatkan layanan konsultasi dan konseling Lingkup Kabupaten/Kota',
            'Jumlah keluarga yang mendapatkan Layanan penerimaan pengaduan Lingkup Kabupaten/Kota',
            'Jumlah keluarga yang mendapatkan Layanan bimbingan masyarakat Lingkup Kabupaten/Kota',
            'Persentase dokumen analisis gender dan anak yang ditetapkan',
            'Jumlah kegiatan penyajian data gender dan anak',
            'Jumlah Dokumen Data Gender dan Anak Kabupaten/Kota yang Tersedia',
            'Jumlah stakeholder yang diadvokasi dan berpartisipasi dalam penyediaan data gender dan anak',
            'Nilai Pemenuhan Hak Anak',
            'Jumlah kegiatan pelembagaan Pemenuhan Hak Anak',
            'Jumlah pemangku kepentingan tingkat kabupaten/kota yang mendapatkan advokasi dan sosialisasi Pelaksanaan Kebijakan Pemenuhan Hak Anak pada Lembaga Pemerintah, Non Pemerintah, Media dan Dunia Usaha Kewenangan Kabupaten/Kota',
            'jumlah dokumen hasil monitoring dan evaluasi Pelaksanaan Kebijakan Pemenuhan Hak Anak pada Lembaga Pemerintah, Non Pemerintah, Media dan Dunia Usaha Kewenangan Kabupaten/Kota',
            'Jumlah kegiatan peningkatan kapasitas lembaga layanan pemenuhan hak anak',
            'jumlah SDM lembaga penyedia layanan Peningkatan Kualitas Hidup Anak Kewenangan Kabupaten/Kota yang mendapatkan bimtek',
            'Jumlah lembaga penyedia layanan Peningkatan Kualitas Hidup Anak tingkat provinsi yang mendapatkan advokasi dan sosialisasi (lembaga pemerintah dan non pemerintah)',
            'jumlah dokumen hasil monitoring dan evaluasi penguatan dan pengembangan lembaga penyedia layanan Peningkatan Kualitas Hidup Anak Kewenangan Kabupaten/Kota',
            'jumlah lembaga penyedia layanan Peningkatan Kualitas Hidup Anak Kewenangan Kabupaten/Kota yang mendapatkan pendampingan',
            'Nilai Perlindungan Khusus Anak',
            'Jumlah kegiatan pencegahan kekerasan terhadap anak',
            'Jumlah pengambil kebijakan dan pemangku kepentingan yang mendapatkan Advokasi dan sosialisasi pencegahan KtA tingkatKab/Kota',
            'Jumlah layanan penanganan bagi anak yang memerlukan perlindungan khusus',
            'Jumlah Anak Korban Kekerasan yang mendapatkan Layanan Pengaduan atau Penjangkauan korban Tingkat Kabupaten/Kota',
            'Jumlah Anak Korban yang mendapatkan Layanan pendampingan tenaga ahli bagi Anak Korban Kekerasan Tingkat Kabupaten/Kota',
            'Jumlah Anak Korban Kekerasan yang mendapatkan Layanan medikolegal bagi Anak Korban Kekerasan Tingkat Kabupaten/Kota',
            'Jumlah Anak Korban Kekerasan yang mendapatkan layanan gelar kasus bagi Perempuan Korban Kekerasan Tingkat Kabupaten/Kota',
            'Jumlah Anak Korban Kekerasan yang mendapatkan layanan rumah perlindungan bagi Perempuan Korban Kekerasan Tingkat Kabupaten/Kota',
            'Jumlah Anak Korban Kekerasan yang mendapatkan pendampingan korban Tingkat Kabupaten/Kota',
            'Jumlah Anak Korban yang mendapatkan Layanan kesehatan yang tidak dijamin BPJS, Jamkesda, dan sumber pendanaan lainnya bagi Anak Korban Kekerasan Tingkat Kabupaten/Kota',
            'Jumlah kegiatan penguatan dan pengembangan lembaga layanan bagi anak yang memerlukan perlindungan khusus',
            'Jumlah KIE Perlindungan khusus anak',
            'jumlah dokumen hasil koordinasi dan sinkronisasi penguatan jejaring antar lembaga penyedia layanan anak yang memerlukan perlindungan khusus tingkat daerah kabupaten/kota',
            'Persentase kelurahan yang menyelenggarakan mekanisme administrasi pemerintahan yang berkualitas',
            'Jumlah kegiatan pembinaan dan pengawasan administrasi kelurahan',
            'Jumlah Dokumen Hasil Fasilitasi Penyusunan Produk Hukum Desa',
            'Jumlah Dokumen Hasil Fasilitasi Pengelolaan Keuangan Desa',
            'Jumlah Aparatur Pemerintah Desa yang Mengikuti Pembinaan Peningkatan Kapasitas',
            'Jumlah Dokumen Profil Desa yang tersusun',
            'Jumlah Dokumen Hasil Evaluasi Perkembangan Desa serta Lomba Desa dan Kelurahan',
            'Persentase fasilitasi kegiatan lembaga kemasyarakatan',
            'Jumlah SDM lembaga kemasyarakatan yang ditingkatkan kapasitasnya',
            'Jumlah Dokumen Hasil Penataan, Pemberdayaan dan Pendayagunaan Kelembagaan Lembaga Kemasyarakatan Desa/Kelurahan (RT, RW, PKK, Posyandu, LPM, dan Karang Taruna), Lembaga Adat Desa/Kelurahan dan Masyarakat Hukum Adat',
            'Jumlah Lembaga Kemasyarakatan Desa/Kelurahan (RT, RW, PKK, Posyandu,LPM,dan Karang Taruna), Lembaga Adat Desa/Kelurahan dan Masyarakat Hukum Adat yang Ditingkatkan Kapasitasnya',
            'Jumlah Dokumen Hasil Fasilitasi Pengembangan Usaha Ekonomi Masyarakat dan Pemerintah Desa dalam Meningkatkan Pendapatan Asli Desa',
            'Jumlah Laporan Hasil Fasilitasi Pemerintah Desa dalam Pemanfaatan Teknologi Tepat Guna',
            'Jumlah Laporan Hasil Fasilitasi Bulan Bhakti Gotong Royong Masyarakat',
            'Jumlah Dokumen Hasil Fasilitasi Tim Penggerak PKK dalam Penyelenggaraan Gerakan Pemberdayaan Masyarakat dan Kesejahteraan Keluarga'
        ];

        $now = Carbon::now();

        $data = collect($indikators)->map(function ($indikator) use ($now) {
            return [
                'nama_indikator' => trim($indikator),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->toArray();

        // Insert dalam batch untuk performa yang lebih baik
        MasterIndikator::insert($data);

        $this->command->info('Master Indikator seeded successfully! Total: ' . count($indikators) . ' records.');
    }
}
