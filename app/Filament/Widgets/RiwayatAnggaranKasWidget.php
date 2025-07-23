<?php

namespace App\Filament\Widgets;

use App\Models\RencanaAnggaranKas;
use App\Models\RealisasiAnggaranKas;
use App\Services\YearContext;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RiwayatAnggaranKasWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    public function getHeading(): string
    {
        return 'Riwayat Anggaran Kas';
    }

    protected function getStats(): array
    {
        $currentYear = YearContext::getActiveYear();

        // Mengambil riwayat anggaran dengan data terbaru berdasarkan created_at/updated_at
        $riwayat = $this->getRiwayatAnggaranTerbaru($currentYear);

        $stats = [];

        // Tambahkan stat untuk Total Realisasi berdasarkan input terakhir
        $totalRealisasiTerakhir = $this->getTotalRealisasiTerakhir($currentYear);
        $rencanaLatest = RencanaAnggaranKas::where('tahun', $currentYear)
            ->where('status', 'approved')
            ->orderBy('updated_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        $totalRencana = $rencanaLatest ? $rencanaLatest->jumlah_rencana : 0;

        $persentaseTotal = 0;
        if ($totalRencana > 0) {
            $persentaseTotal = round(($totalRealisasiTerakhir / $totalRencana) * 100, 1);
        }

        $stats[] = Stat::make(
            'Total Realisasi',
            'Rp ' . number_format($totalRealisasiTerakhir, 0, ',', '.') . ' (' . $persentaseTotal . '%)'
        )
            ->description('Dari total rencana: Rp ' . number_format($totalRencana, 0, ',', '.'))
            ->descriptionIcon('heroicon-m-chart-bar')
            ->color($this->getColorByPersentase($persentaseTotal))
            ->chart($this->getTotalRealisasiChart($currentYear))
            ->extraAttributes([
                'class' => 'relative bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20'
            ]);

        // Tambahkan stats untuk setiap jenis anggaran
        foreach ($riwayat as $index => $data) {
            // Ambil data realisasi terakhir untuk jenis anggaran ini
            $realisasiTerakhir = $this->getRealisasiDataTerakhir($data['jenis_anggaran'], $currentYear);

            // Hitung persentase realisasi
            $persentase = 0;
            $jumlahRencana = $data['jumlah_rencana'];
            if ($jumlahRencana > 0) {
                $persentase = round(($realisasiTerakhir / $jumlahRencana) * 100, 1);
            }

            $stats[] = Stat::make(
                $data['jenis_anggaran'],
                'Rp ' . number_format($jumlahRencana, 0, ',', '.')
            )
                ->description('Realisasi: ' . $persentase . '%')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($this->getColorByJenis($data['jenis_anggaran']))
                ->chart($this->getChartData($data['jenis_anggaran'], $currentYear))
                ->extraAttributes([
                    'class' => 'relative'
                ]);
        }

        // Jika tidak ada data, tampilkan pesan
        if (empty($riwayat)) {
            $stats[] = Stat::make('Tidak ada data', 'Rp 0')
                ->description('Belum ada rencana anggaran untuk tahun ' . $currentYear)
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning');
        }

        return $stats;
    }

    /**
     * Mengambil riwayat anggaran dengan data terbaru berdasarkan created_at/updated_at
     */
    private function getRiwayatAnggaranTerbaru(int $tahun): array
    {
        $jenisAnggaranMap = [
            'anggaran_murni' => 'Anggaran Murni',
            'pergeseran' => 'Pergeseran',
            'perubahan' => 'Perubahan'
        ];

        $riwayat = [];

        foreach ($jenisAnggaranMap as $jenisCode => $jenisLabel) {
            // Ambil rencana terakhir untuk setiap jenis anggaran berdasarkan updated_at atau created_at
            $rencanaLatest = RencanaAnggaranKas::where('tahun', $tahun)
                ->where('jenis_anggaran', $jenisCode)
                ->where('status', 'approved')
                ->orderBy('updated_at', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();

            if ($rencanaLatest) {
                $riwayat[] = [
                    'jenis_anggaran' => $jenisLabel,
                    'jumlah_rencana' => $rencanaLatest->jumlah_rencana,
                    'jumlah_formatted' => $this->formatRupiah($rencanaLatest->jumlah_rencana),
                    'created_at' => $rencanaLatest->created_at,
                    'updated_at' => $rencanaLatest->updated_at
                ];
            }
        }

        return $riwayat;
    }

    /**
     * Mendapatkan realisasi terakhir yang diinput (bukan total)
     */
    private function getTotalRealisasiTerakhir(int $tahun): float
    {
        // Ambil realisasi terakhir berdasarkan waktu input terakhir secara keseluruhan
        $realisasiTerakhir = RealisasiAnggaranKas::whereHas('rencanaAnggaranKas', function ($query) use ($tahun) {
            $query->where('tahun', $tahun);
        })
            ->where('status', 'completed')
            ->orderBy('updated_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        return $realisasiTerakhir ? $realisasiTerakhir->jumlah_realisasi : 0;
    }

    /**
     * Mendapatkan data realisasi terakhir berdasarkan jenis anggaran
     */
    private function getRealisasiDataTerakhir(string $jenisAnggaran, int $tahun): float
    {
        // Mapping jenis anggaran ke nilai enum
        $jenisAnggaranMap = [
            'Anggaran Murni' => 'anggaran_murni',
            'Pergeseran' => 'pergeseran',
            'Perubahan' => 'perubahan'
        ];

        $jenisAnggaranCode = $jenisAnggaranMap[$jenisAnggaran] ?? null;

        if (!$jenisAnggaranCode) {
            return 0;
        }

        // Ambil realisasi terakhir untuk jenis anggaran ini
        $realisasiTerakhir = RealisasiAnggaranKas::whereHas('rencanaAnggaranKas', function ($query) use ($jenisAnggaranCode, $tahun) {
            $query->where('jenis_anggaran', $jenisAnggaranCode)
                ->where('tahun', $tahun);
        })
            ->where('status', 'completed')
            ->orderBy('updated_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        return $realisasiTerakhir ? $realisasiTerakhir->jumlah_realisasi : 0;
    }

    /**
     * Mendapatkan data chart untuk total realisasi per triwulan
     */
    private function getTotalRealisasiChart(int $tahun): array
    {
        $chartData = [];

        for ($triwulan = 1; $triwulan <= 4; $triwulan++) {
            $realisasi = RealisasiAnggaranKas::whereHas('rencanaAnggaranKas', function ($query) use ($tahun) {
                $query->where('tahun', $tahun);
            })
                ->where('triwulan', $triwulan)
                ->where('status', 'completed')
                ->sum('jumlah_realisasi');

            $chartData[] = $realisasi;
        }

        return $chartData;
    }

    /**
     * Mendapatkan jumlah rencana anggaran berdasarkan jenis anggaran (rencana terakhir)
     */
    private function getRencanaAmount(string $jenisAnggaran, int $tahun): float
    {
        $jenisAnggaranMap = [
            'Anggaran Murni' => 'anggaran_murni',
            'Pergeseran' => 'pergeseran',
            'Perubahan' => 'perubahan'
        ];

        $jenisAnggaranCode = $jenisAnggaranMap[$jenisAnggaran] ?? null;

        if (!$jenisAnggaranCode) {
            return 0;
        }

        // Ambil rencana terakhir untuk jenis anggaran ini
        $rencanaLatest = RencanaAnggaranKas::where('jenis_anggaran', $jenisAnggaranCode)
            ->where('tahun', $tahun)
            ->where('status', 'approved')
            ->latest('created_at')
            ->first();

        return $rencanaLatest ? $rencanaLatest->jumlah_rencana : 0;
    }

    /**
     * Mendapatkan data chart untuk sparkline
     */
    private function getChartData(string $jenisAnggaran, int $tahun): array
    {
        $jenisAnggaranMap = [
            'Anggaran Murni' => 'anggaran_murni',
            'Pergeseran' => 'pergeseran',
            'Perubahan' => 'perubahan'
        ];

        $jenisAnggaranCode = $jenisAnggaranMap[$jenisAnggaran] ?? null;

        if (!$jenisAnggaranCode) {
            return [0, 0, 0, 0];
        }

        $chartData = [];

        for ($triwulan = 1; $triwulan <= 4; $triwulan++) {
            $realisasi = RealisasiAnggaranKas::whereHas('rencanaAnggaranKas', function ($query) use ($jenisAnggaranCode, $tahun) {
                $query->where('jenis_anggaran', $jenisAnggaranCode)
                    ->where('tahun', $tahun);
            })
                ->where('triwulan', $triwulan)
                ->where('status', 'completed')
                ->sum('jumlah_realisasi');

            $chartData[] = $realisasi;
        }

        return $chartData;
    }

    /**
     * Format angka ke format rupiah
     */
    private function formatRupiah(float $amount): string
    {
        if ($amount >= 1_000_000_000) {
            return 'Rp ' . number_format($amount / 1_000_000_000, 1) . ' M';
        } elseif ($amount >= 1_000_000) {
            return 'Rp ' . number_format($amount / 1_000_000, 1) . ' Jt';
        } elseif ($amount >= 1_000) {
            return 'Rp ' . number_format($amount / 1_000, 1) . ' Rb';
        } else {
            return 'Rp ' . number_format($amount, 0, ',', '.');
        }
    }

    private function getColorByJenis(string $jenis): string
    {
        return match ($jenis) {
            'Anggaran Murni' => 'primary',
            'Pergeseran' => 'warning',
            'Perubahan' => 'success',
            default => 'secondary',
        };
    }

    /**
     * Mendapatkan warna berdasarkan persentase realisasi
     */
    private function getColorByPersentase(float $persentase): string
    {
        if ($persentase >= 80) {
            return 'success';
        } elseif ($persentase >= 60) {
            return 'warning';
        } elseif ($persentase >= 40) {
            return 'danger';
        } else {
            return 'gray';
        }
    }
}
