<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Program;
use App\Models\Kegiatan;
use App\Models\SubKegiatan;
use App\Services\YearContext;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $year = YearContext::getActiveYear();

        $totalAnggaran = Program::forYear($year)->sum('anggaran');
        $totalRealisasi = Program::forYear($year)->sum('realisasi');
        $persentaseRealisasi = $totalAnggaran > 0 ? ($totalRealisasi / $totalAnggaran) * 100 : 0;

        return [
            Stat::make('Total Program', Program::forYear($year)->count())
                ->description('Program tahun ' . $year)
                ->descriptionIcon('heroicon-m-folder')
                ->color('success'),

            Stat::make('Total Anggaran', 'Rp ' . number_format($totalAnggaran, 0, ',', '.'))
                ->description('Anggaran tahun ' . $year)
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),

            Stat::make('Total Realisasi', 'Rp ' . number_format($totalRealisasi, 0, ',', '.'))
                ->description('Realisasi tahun ' . $year)
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('warning'),

            Stat::make('% Realisasi', number_format($persentaseRealisasi, 2) . '%')
                ->description('Persentase realisasi')
                ->descriptionIcon('heroicon-m-calculator')
                ->color($persentaseRealisasi >= 80 ? 'success' : ($persentaseRealisasi >= 50 ? 'warning' : 'danger')),
        ];
    }
}
