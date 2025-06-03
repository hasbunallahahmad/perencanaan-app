<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organisasi;
use App\Models\Bidang;
use App\Models\Seksi;

class DinasSeeder extends Seeder
{
    public function run()
    {
        // Membuat Organisasi - Dinas PP-PA Kota Semarang
        $dinas = Organisasi::create([
            'nama' => 'Dinas Pemberdayaan Perempuan dan Perlindungan Anak Kota Semarang',
            'deskripsi' => 'Dinas Pemberdayaan Perempuan dan Perlindungan Anak Kota Semarang',
            'alamat' => 'Jl. Pemuda No. 148, Sekayu, Semarang Tengah, Kota Semarang',
            'kota' => 'Semarang',
            'aktif' => true
        ]);

        // 1. SEKRETARIAT
        $sekretariat = Bidang::create([
            'nama' => 'Sekretariat',
            'kode' => 'SEKT',
            'deskripsi' => 'Unit pelaksana tugas administrasi dan pelayanan teknis',
            'organisasi_id' => $dinas->id,
            'is_sekretariat' => true,
            'aktif' => true
        ]);

        // Subbagian Sekretariat
        $subbagianSekretariat = [
            'Subbagian Perencanaan dan Evaluasi',
            'Subbagian Umum dan Kepegawaian',
            'Subbagian Keuangan dan Aset'
        ];

        foreach ($subbagianSekretariat as $nama) {
            Seksi::create([
                'nama' => $nama,
                'deskripsi' => 'Melaksanakan tugas-tugas ' . strtolower($nama),
                'bidang_id' => $sekretariat->id,
                'jenis' => 'subbagian',
                'aktif' => true
            ]);
        }

        // 2. BIDANG PEMBERDAYAAN PEREMPUAN DAN PENGARUSUTAMAAN GENDER
        $bidangPP = Bidang::create([
            'nama' => 'Bidang Pemberdayaan Perempuan dan Pengarusutamaan Gender',
            'kode' => 'PP-PUG',
            'deskripsi' => 'Melaksanakan pemberdayaan perempuan dan pengarusutamaan gender',
            'organisasi_id' => $dinas->id,
            'is_sekretariat' => false,
            'aktif' => true
        ]);

        $seksiPP = [
            'Seksi Pengarusutamaan Gender',
            'Seksi Peningkatan Kualitas Keluarga',
            'Seksi Peningkatan Kualitas Hidup Perempuan'
        ];

        foreach ($seksiPP as $nama) {
            Seksi::create([
                'nama' => $nama,
                'deskripsi' => 'Melaksanakan kegiatan ' . strtolower($nama),
                'bidang_id' => $bidangPP->id,
                'jenis' => 'seksi',
                'aktif' => true
            ]);
        }

        // 3. BIDANG PEMENUHAN HAK ANAK
        $bidangHA = Bidang::create([
            'nama' => 'Bidang Pemenuhan Hak Anak',
            'kode' => 'PHA',
            'deskripsi' => 'Melaksanakan pemenuhan hak-hak anak',
            'organisasi_id' => $dinas->id,
            'is_sekretariat' => false,
            'aktif' => true
        ]);

        $seksiHA = [
            'Seksi Hak Sipil, Informasi & Partisipasi',
            'Seksi Pengasuhan dan Lingkungan',
            'Seksi Pendidikan dan Kesehatan'
        ];

        foreach ($seksiHA as $nama) {
            Seksi::create([
                'nama' => $nama,
                'deskripsi' => 'Melaksanakan kegiatan ' . strtolower($nama),
                'bidang_id' => $bidangHA->id,
                'jenis' => 'seksi',
                'aktif' => true
            ]);
        }

        // 4. BIDANG PERLINDUNGAN PEREMPUAN DAN ANAK
        $bidangPerlindungan = Bidang::create([
            'nama' => 'Bidang Perlindungan Perempuan dan Anak',
            'kode' => 'PPA',
            'deskripsi' => 'Melaksanakan perlindungan perempuan dan anak',
            'organisasi_id' => $dinas->id,
            'is_sekretariat' => false,
            'aktif' => true
        ]);

        $seksiPerlindungan = [
            'Seksi Pencegahan dan Penanganan Kekerasan',
            'Seksi Perlindungan Perempuan dan Khusus Anak',
            'Seksi Jejaring Perlindungan Perempuan'
        ];

        foreach ($seksiPerlindungan as $nama) {
            Seksi::create([
                'nama' => $nama,
                'deskripsi' => 'Melaksanakan kegiatan ' . strtolower($nama),
                'bidang_id' => $bidangPerlindungan->id,
                'jenis' => 'seksi',
                'aktif' => true
            ]);
        }

        // 5. BIDANG PEMBERDAYAAN MASYARAKAT DAN DATA INFORMASI
        $bidangPemberdayaan = Bidang::create([
            'nama' => 'Bidang Pemberdayaan Masyarakat dan Data Informasi',
            'kode' => 'PM-DI',
            'deskripsi' => 'Melaksanakan pemberdayaan masyarakat dan pengelolaan data informasi',
            'organisasi_id' => $dinas->id,
            'is_sekretariat' => false,
            'aktif' => true
        ]);

        $seksiPemberdayaan = [
            'Seksi Perkembangan Kelurahan',
            'Seksi Pengembangan Usaha Ekonomi Masyarakat & Teknologi Tepat Guna',
            'Seksi Data dan Informasi'
        ];

        foreach ($seksiPemberdayaan as $nama) {
            Seksi::create([
                'nama' => $nama,
                'deskripsi' => 'Melaksanakan kegiatan ' . strtolower($nama),
                'bidang_id' => $bidangPemberdayaan->id,
                'jenis' => 'seksi',
                'aktif' => true
            ]);
        }

        // 6. KELOMPOK JABATAN FUNGSIONAL (jika diperlukan sebagai unit tersendiri)
        $kelompokJabatan = Bidang::create([
            'nama' => 'Kelompok Jabatan Fungsional',
            'kode' => 'KJF',
            'deskripsi' => 'Kelompok jabatan fungsional yang melaksanakan tugas sesuai dengan keahliannya',
            'organisasi_id' => $dinas->id,
            'is_sekretariat' => false,
            'aktif' => true
        ]);

        // 7. UPTD (Unit Pelaksana Teknis Daerah) - jika perlu dimasukkan sebagai bagian dari struktur
        $uptd = Bidang::create([
            'nama' => 'Unit Pelaksana Teknis Daerah (UPTD)',
            'kode' => 'UPTD',
            'deskripsi' => 'Unit pelaksana teknis daerah di bawah Dinas PP-PA',
            'organisasi_id' => $dinas->id,
            'is_sekretariat' => false,
            'aktif' => true
        ]);

        // Output informasi setelah seeding
        $this->command->info('✅ Seeder Dinas PP-PA Kota Semarang berhasil dijalankan!');
        $this->command->info('📊 Data yang berhasil dibuat:');
        $this->command->info('   - 1 Organisasi: ' . $dinas->nama);
        $this->command->info('   - ' . Bidang::count() . ' Bidang/Unit');
        $this->command->info('   - ' . Seksi::count() . ' Seksi/Subbagian');

        // Tampilkan struktur yang dibuat
        $this->command->info('');
        $this->command->info('🏢 Struktur Organisasi yang Dibuat:');
        $this->command->info('└── ' . $dinas->nama);

        $bidangs = Bidang::with('seksis')->where('organisasi_id', $dinas->id)->get();
        foreach ($bidangs as $bidang) {
            $this->command->info('    ├── ' . $bidang->nama . ' (' . $bidang->kode . ')');
            foreach ($bidang->seksis as $seksi) {
                $this->command->info('    │   └── ' . $seksi->nama);
            }
        }
    }
}
