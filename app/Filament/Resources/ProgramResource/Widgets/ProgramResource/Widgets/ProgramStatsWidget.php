<?php

namespace App\Filament\Resources\ProgramResource\Widgets;

use App\Models\Program;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProgramStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $record = $this->record;

        return [
            Stat::make('Total Kegiatan', $record->kegiatan()->count())
                ->description('Kegiatan dalam program ini')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('success'),

            Stat::make('Total Sub Kegiatan', $record->total_sub_kegiatan)
                ->description('Sub kegiatan dalam program ini')
                ->descriptionIcon('heroicon-m-clipboard-document')
                ->color('info'),

            Stat::make('Kategori Program', $record->kategori)
                ->description('Jenis program')
                ->descriptionIcon('heroicon-m-tag')
                ->color($record->badge_color),
        ];
    }
}
