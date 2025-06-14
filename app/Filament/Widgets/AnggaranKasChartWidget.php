<?php

namespace App\Filament\Widgets;

use App\Models\RealisasiAnggaranKas;
use App\Services\YearContext;
use Filament\Widgets\ChartWidget;

class AnggaranKasChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Grafik Perbandingan Anggaran Kas';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $pollingInterval = '30s';

    protected function getData(): array
    {
        $activeYear = YearContext::getActiveYear();
        $comparisonData = RealisasiAnggaranKas::getComparisonData($activeYear);

        $labels = ['Triwulan I', 'Triwulan II', 'Triwulan III', 'Triwulan IV'];
        $rencanaData = [];
        $realisasiData = [];

        for ($i = 1; $i <= 4; $i++) {
            $data = $comparisonData["triwulan_$i"];
            $rencanaData[] = $data['rencana'];
            $realisasiData[] = $data['realisasi'];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Rencana Anggaran ' . $activeYear,
                    'data' => $rencanaData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                    'fill' => false,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Realisasi Anggaran ' . $activeYear,
                    'data' => $realisasiData,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'borderWidth' => 2,
                    'fill' => false,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    public function getHeading(): string
    {
        $activeYear = YearContext::getActiveYear();
        return 'Grafik Perbandingan Anggaran Kas - Tahun ' . $activeYear;
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) {
                            return context.dataset.label + ": Rp " + new Intl.NumberFormat("id-ID").format(context.raw);
                        }'
                    ]
                ]
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) {
                            return "Rp " + new Intl.NumberFormat("id-ID").format(value);
                        }'
                    ]
                ],
            ],
            'interaction' => [
                'intersect' => false,
                'mode' => 'index',
            ],
            'maintainAspectRatio' => false,
        ];
    }
}
