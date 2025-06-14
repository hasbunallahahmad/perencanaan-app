<?php
// app/Filament/Widgets/AnggaranKasOverviewWidget.php

namespace App\Filament\Widgets;

use App\Models\RencanaAnggaranKas;
use App\Models\RealisasiAnggaranKas;
use App\Services\YearContext;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AnggaranKasOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        $activeYear = YearContext::getActiveYear();
        $currentQuarter = ceil(date('n') / 3);

        // Get comparison data for active year
        $comparisonData = RealisasiAnggaranKas::getComparisonData($activeYear);

        // Calculate totals
        $totalRencana = collect($comparisonData)->sum('rencana');
        $totalRealisasi = collect($comparisonData)->sum('realisasi');
        $totalSelisih = $totalRealisasi - $totalRencana;
        $overallPercentage = $totalRencana > 0 ? round(($totalRealisasi / $totalRencana) * 100, 2) : 0;

        // Current quarter data
        $currentQuarterData = $comparisonData["triwulan_$currentQuarter"] ?? [
            'rencana' => 0,
            'realisasi' => 0,
            'selisih' => 0,
            'persentase' => 0
        ];

        return [
            Stat::make('Total Rencana Anggaran ' . $activeYear, 'Rp ' . number_format($totalRencana, 0, ',', '.'))
                ->description('Keseluruhan rencana anggaran kas tahun ' . $activeYear)
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),

            Stat::make('Total Realisasi Anggaran ' . $activeYear, 'Rp ' . number_format($totalRealisasi, 0, ',', '.'))
                ->description($overallPercentage . '% dari total rencana')
                ->descriptionIcon($overallPercentage >= 100 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($overallPercentage >= 100 ? 'success' : ($overallPercentage >= 75 ? 'warning' : 'danger')),

            Stat::make('Selisih Anggaran ' . $activeYear, 'Rp ' . number_format(abs($totalSelisih), 0, ',', '.'))
                ->description($totalSelisih >= 0 ? 'Realisasi melebihi rencana' : 'Realisasi kurang dari rencana')
                ->descriptionIcon($totalSelisih >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($totalSelisih >= 0 ? 'success' : 'danger'),

            Stat::make('Triwulan ' . $currentQuarter . ' - Rencana', 'Rp ' . number_format($currentQuarterData['rencana'], 0, ',', '.'))
                ->description('Rencana anggaran triwulan saat ini (' . $activeYear . ')')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),

            Stat::make('Triwulan ' . $currentQuarter . ' - Realisasi', 'Rp ' . number_format($currentQuarterData['realisasi'], 0, ',', '.'))
                ->description($currentQuarterData['persentase'] . '% dari rencana TW' . $currentQuarter)
                ->descriptionIcon($currentQuarterData['persentase'] >= 100 ? 'heroicon-m-check-circle' : 'heroicon-m-clock')
                ->color($currentQuarterData['persentase'] >= 100 ? 'success' : ($currentQuarterData['persentase'] >= 75 ? 'warning' : 'danger')),

            Stat::make('Status Pencapaian TW' . $currentQuarter, $this->getAchievementStatus($currentQuarterData['persentase']))
                ->description($this->getAchievementDescription($currentQuarterData['persentase']))
                ->descriptionIcon($this->getAchievementIcon($currentQuarterData['persentase']))
                ->color($this->getAchievementColor($currentQuarterData['persentase'])),
        ];
    }

    private function getAchievementStatus(float $percentage): string
    {
        return match (true) {
            $percentage >= 100 => 'Target Tercapai',
            $percentage >= 90 => 'Hampir Tercapai',
            $percentage >= 75 => 'Dalam Progres',
            $percentage >= 50 => 'Perlu Perhatian',
            default => 'Butuh Evaluasi',
        };
    }

    private function getAchievementDescription(float $percentage): string
    {
        return match (true) {
            $percentage >= 100 => 'Realisasi sudah mencapai atau melebihi target',
            $percentage >= 90 => 'Hanya butuh sedikit lagi untuk mencapai target',
            $percentage >= 75 => 'Progres baik, terus tingkatkan',
            $percentage >= 50 => 'Progres lambat, perlu ditingkatkan',
            default => 'Progres sangat lambat, perlu evaluasi menyeluruh',
        };
    }

    private function getAchievementIcon(float $percentage): string
    {
        return match (true) {
            $percentage >= 100 => 'heroicon-m-trophy',
            $percentage >= 90 => 'heroicon-m-star',
            $percentage >= 75 => 'heroicon-m-arrow-trending-up',
            $percentage >= 50 => 'heroicon-m-exclamation-triangle',
            default => 'heroicon-m-x-circle',
        };
    }

    private function getAchievementColor(float $percentage): string
    {
        return match (true) {
            $percentage >= 100 => 'success',
            $percentage >= 90 => 'info',
            $percentage >= 75 => 'warning',
            $percentage >= 50 => 'warning',
            default => 'danger',
        };
    }
}
