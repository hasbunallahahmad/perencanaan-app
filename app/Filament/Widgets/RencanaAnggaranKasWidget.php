<?php

namespace App\Filament\Widgets;

use App\Models\RencanaAnggaranKas;
use App\Services\YearContext;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class RencanaAnggaranKasWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getHeading(): ?string
    {
        $currentYear = YearContext::getActiveYear();
        $hasData = YearContext::hasDataForYear($currentYear);

        if (!$hasData) {
            return "Rencana Anggaran Kas {$currentYear} - Belum ada data";
        }

        $latestData = RencanaAnggaranKas::getWidgetData($currentYear);
        $status = $latestData['status'] ?? 'Belum ada data';

        return "Rencana Anggaran Kas {$currentYear} - Status: {$status}";
    }

    protected function getStats(): array
    {
        $currentYear = YearContext::getActiveYear();
        $hasData = YearContext::hasDataForYear($currentYear);

        // Jika tidak ada data untuk tahun aktif, tampilkan pesan kosong
        if (!$hasData) {
            return $this->getEmptyStats($currentYear);
        }

        // Mendapatkan data anggaran terbaru
        $latestData = RencanaAnggaranKas::getWidgetData($currentYear);

        // Jika model method tidak mengembalikan data yang valid
        if (empty($latestData) || !isset($latestData['total_anggaran'])) {
            return $this->getEmptyStats($currentYear);
        }

        // Mendapatkan ringkasan anggaran
        $ringkasan = RencanaAnggaranKas::getRingkasanAnggaran($currentYear);

        // Menghitung persentase perubahan dari anggaran murni ke anggaran terbaru
        $anggaranMurni = $ringkasan['anggaran_murni']['total'] ?? 0;
        $anggaranTerbaru = $latestData['total_anggaran'] ?? 0;

        $perubahanPersentase = 0;
        if ($anggaranMurni > 0 && $anggaranTerbaru > 0) {
            $perubahanPersentase = (($anggaranTerbaru - $anggaranMurni) / $anggaranMurni) * 100;
        }

        return [
            Stat::make('Anggaran Terbaru', $this->formatCurrency($anggaranTerbaru))
                ->description($latestData['jenis_anggaran'] ?? 'Belum ada data')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color($this->getColorForChange($perubahanPersentase))
                ->chart($this->getChartData($ringkasan)),

            Stat::make('Anggaran Murni', $this->formatCurrency($anggaranMurni))
                ->description('Anggaran dasar tahun ' . $currentYear)
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),

            Stat::make('Perubahan Anggaran', $this->formatPercentage($perubahanPersentase))
                ->description($perubahanPersentase >= 0 ? 'Kenaikan' : 'Penurunan')
                ->descriptionIcon($perubahanPersentase >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($this->getColorForChange($perubahanPersentase)),

            Stat::make('Status Anggaran', $latestData['status'] ?? 'Belum ada data')
                ->description('Terakhir diperbarui: ' . $this->formatLastUpdate($latestData['tanggal_update'] ?? null))
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),
        ];
    }

    /**
     * Mengembalikan stats kosong ketika tidak ada data
     */
    private function getEmptyStats(int $year): array
    {
        $availableYears = YearContext::getYearsWithData();
        $nextAvailableYear = YearContext::getNextYearWithData($year);
        $previousAvailableYear = YearContext::getPreviousYearWithData($year);

        $suggestionText = 'Tidak ada data untuk tahun ini';
        if ($nextAvailableYear) {
            $suggestionText = "Data tersedia untuk tahun {$nextAvailableYear}";
        } elseif ($previousAvailableYear) {
            $suggestionText = "Data tersedia untuk tahun {$previousAvailableYear}";
        } elseif (!empty($availableYears)) {
            $suggestionText = "Data tersedia untuk tahun: " . implode(', ', array_slice($availableYears, 0, 3));
        }

        return [
            Stat::make('Anggaran Terbaru', '-')
                ->description($suggestionText)
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),

            Stat::make('Anggaran Murni', '-')
                ->description('Belum ada data anggaran')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('gray'),

            Stat::make('Perubahan Anggaran', '-')
                ->description('Tidak dapat dihitung')
                ->descriptionIcon('heroicon-m-minus')
                ->color('gray'),

            Stat::make('Status Anggaran', 'Belum ada data')
                ->description('Silakan buat anggaran untuk tahun ' . $year)
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('gray'),
        ];
    }

    /**
     * Format currency dengan handling untuk nilai 0
     */
    private function formatCurrency(float $amount): string
    {
        if ($amount == 0) {
            return 'Rp 0';
        }

        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    /**
     * Format percentage dengan handling untuk nilai 0
     */
    private function formatPercentage(float $percentage): string
    {
        if ($percentage == 0) {
            return '0%';
        }

        return number_format(abs($percentage), 1) . '%';
    }

    /**
     * Format tanggal update terakhir
     */
    private function formatLastUpdate(?string $date): string
    {
        if (!$date) {
            return 'Belum ada data';
        }

        try {
            return Carbon::parse($date)->format('d M Y');
        } catch (\Exception $e) {
            return 'Format tanggal tidak valid';
        }
    }

    /**
     * Mendapatkan warna berdasarkan perubahan
     */
    private function getColorForChange(float $percentage): string
    {
        if ($percentage == 0) {
            return 'gray';
        }

        return $percentage >= 0 ? 'success' : 'danger';
    }

    /**
     * Mendapatkan data chart dengan handling untuk nilai kosong
     */
    private function getChartData(array $ringkasan): array
    {
        $data = [
            $ringkasan['anggaran_murni']['total'] ?? 0,
            $ringkasan['pergeseran']['total'] ?? 0,
            $ringkasan['perubahan']['total'] ?? 0,
        ];

        // Jika semua data 0, kembalikan array kosong untuk menyembunyikan chart
        if (array_sum($data) == 0) {
            return [];
        }

        return $data;
    }

    protected function getPollingInterval(): ?string
    {
        return '30s'; // Refresh setiap 30 detik
    }

    /**
     * Dapat ditambahkan untuk refresh widget ketika year context berubah
     */
    public static function canView(): bool
    {
        return true;
    }
}
