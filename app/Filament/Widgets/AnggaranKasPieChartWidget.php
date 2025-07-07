<?php

namespace App\Filament\Widgets;

use App\Models\RealisasiAnggaranKas;
use App\Models\RencanaAnggaranKas;
use App\Services\YearContext;
use Filament\Widgets\ChartWidget;

class AnggaranKasPieChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Perbandingan Anggaran Kas';

    protected static ?int $sort = 3;

    // Ubah dari 'full' ke 'half' atau angka untuk membuat berdampingan
    protected int | string | array $columnSpan = 'half';

    protected static ?string $pollingInterval = '30s';

    protected function getData(): array
    {
        $activeYear = YearContext::getActiveYear();

        // Ambil total rencana anggaran (input terakhir)
        $totalRencana = RencanaAnggaranKas::where('status', 'approved')
            ->where('tahun', $activeYear)
            ->orderBy('created_at', 'desc')
            ->first()
            ->jumlah_rencana ?? 0;

        // Ambil total realisasi anggaran per triwulan
        $totalRealisasi = RealisasiAnggaranKas::where('tahun', $activeYear)
            ->where('status', 'completed')
            ->sum('jumlah_realisasi');

        // Hitung sisa anggaran
        $sisaAnggaran = $totalRencana - $totalRealisasi;

        // Pastikan sisa anggaran tidak negatif
        $sisaAnggaran = max(0, $sisaAnggaran);

        // Data untuk pie chart
        $labels = ['Realisasi', 'Sisa Anggaran'];
        $data = [$totalRealisasi, $sisaAnggaran];
        $colors = [
            'rgb(16, 185, 129)', // Hijau untuk realisasi
            'rgb(59, 130, 246)',  // Biru untuk sisa anggaran
        ];
        $backgroundColors = [
            'rgba(16, 185, 129, 0.8)',
            'rgba(59, 130, 246, 0.8)',
        ];

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $backgroundColors,
                    'borderColor' => $colors,
                    'borderWidth' => 2,
                    'hoverOffset' => 4, // Menambahkan efek hover
                ],
            ],
            'labels' => $labels,
        ];
    }

    public function getHeading(): string
    {
        $activeYear = YearContext::getActiveYear();
        return 'Perbandingan Anggaran Kas - Tahun ' . $activeYear;
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        $activeYear = YearContext::getActiveYear();

        // Ambil data untuk tooltip
        $totalRencana = RencanaAnggaranKas::where('status', 'approved')
            ->where('tahun', $activeYear)
            ->orderBy('created_at', 'desc')
            ->first()
            ->jumlah_rencana ?? 0;

        $totalRealisasi = RealisasiAnggaranKas::where('tahun', $activeYear)
            ->where('status', 'completed')
            ->sum('jumlah_realisasi');

        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                    'labels' => [
                        'padding' => 20,
                        'usePointStyle' => true,
                    ]
                ],
                'tooltip' => [
                    'backgroundColor' => 'rgba(0, 0, 0, 0.8)',
                    'titleColor' => 'white',
                    'bodyColor' => 'white',
                    'borderColor' => 'rgba(255, 255, 255, 0.1)',
                    'borderWidth' => 1,
                    'cornerRadius' => 6,
                    'displayColors' => true,
                    'enabled' => true, // Pastikan tooltip enabled
                    'mode' => 'nearest',
                    'intersect' => true,
                ]
            ],
            'maintainAspectRatio' => false,
            'responsive' => true,
            'interaction' => [
                'intersect' => true,
                'mode' => 'nearest',
            ],
            // Hilangkan onHover karena menyebabkan masalah
            'elements' => [
                'arc' => [
                    'hoverBackgroundColor' => [
                        'rgba(16, 185, 129, 1)',
                        'rgba(59, 130, 246, 1)',
                    ],
                    'hoverBorderColor' => [
                        'rgb(16, 185, 129)',
                        'rgb(59, 130, 246)',
                    ],
                    'hoverBorderWidth' => 3,
                ]
            ]
        ];
    }

    // Menambahkan method untuk custom tooltip jika diperlukan
    protected function getExtraJs(): string
    {
        $activeYear = YearContext::getActiveYear();
        $totalRencana = RencanaAnggaranKas::where('status', 'approved')
            ->where('tahun', $activeYear)
            ->sum('jumlah_rencana');


        $totalRealisasi = RealisasiAnggaranKas::where('tahun', $activeYear)
            ->where('status', 'completed')
            ->sum('jumlah_realisasi');

        return "
        if (window.chart) {
            window.chart.options.plugins.tooltip.callbacks = {
                title: function(context) {
                    return 'Anggaran Kas Tahun {$activeYear}';
                },
                label: function(context) {
                    const label = context.label || '';
                    const value = context.raw || 0;
                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                    const percentage = ((value / total) * 100).toFixed(1);
                    const formattedValue = new Intl.NumberFormat('id-ID').format(value);
                    
                    return [
                        label + ': Rp ' + formattedValue,
                        'Persentase: ' + percentage + '%'
                    ];
                },
                afterLabel: function(context) {
                    const totalRencana = {$totalRencana};
                    const totalRealisasi = {$totalRealisasi};
                    
                    if (context.label === 'Realisasi') {
                        const efisiensi = totalRencana > 0 ? ((totalRealisasi / totalRencana) * 100).toFixed(1) : 0;
                        return [
                            'Total Rencana: Rp ' + new Intl.NumberFormat('id-ID').format(totalRencana),
                            'Efisiensi: ' + efisiensi + '%'
                        ];
                    } else if (context.label === 'Sisa Anggaran') {
                        const sisaPersentase = totalRencana > 0 ? (((totalRencana - totalRealisasi) / totalRencana) * 100).toFixed(1) : 0;
                        return [
                            'Total Rencana: Rp ' + new Intl.NumberFormat('id-ID').format(totalRencana),
                            'Sisa: ' + sisaPersentase + '% dari rencana'
                        ];
                    }
                    return '';
                }
            };
        }
        ";
    }
}
