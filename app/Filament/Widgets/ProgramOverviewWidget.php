<?php

namespace App\Filament\Widgets;

use App\Models\Program;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProgramOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $stats = Program::getStatistics();

        return [
            Stat::make('Total Program', $stats['total_program'])
                ->description('Program yang terdaftar')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('primary'),

            Stat::make('Program Aktif', $stats['program_dengan_kegiatan'])
                ->description('Program yang memiliki kegiatan')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Program Tanpa Kegiatan', $stats['program_tanpa_kegiatan'])
                ->description('Program yang belum memiliki kegiatan')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('warning'),

            Stat::make('Program Penunjang', $stats['program_penunjang'])
                ->description('Program penunjang urusan pemerintahan')
                ->descriptionIcon('heroicon-m-cog-6-tooth')
                ->color('gray'),
        ];
    }
}
