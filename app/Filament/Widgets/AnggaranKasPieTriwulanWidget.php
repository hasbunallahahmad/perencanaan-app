<?php

namespace App\Filament\Widgets;

use App\Models\RealisasiAnggaranKas;
use App\Models\RencanaAnggaranKas;
use App\Services\YearContext;
use Filament\Widgets\ChartWidget;

class AnggaranKasPieTriwulanWidget extends ChartWidget
{
    protected static ?string $heading = 'Distribusi Realisasi Anggaran per Triwulan';

    protected static ?int $sort = 4;

    // Ubah dari 'full' ke 'half' untuk membuat berdampingan
    protected int | string | array $columnSpan = 'half';

    protected static ?string $pollingInterval = '30s';

    protected function getData(): array
    {
        $activeYear = YearContext::getActiveYear();

        // Ambil total rencana anggaran
        $totalRencana = RencanaAnggaranKas::where('status', 'approved')
            ->where('tahun', $activeYear)
            ->orderBy('created_at', 'desc')
            ->first()
            ->jumlah_rencana ?? 0;

        // Ambil data realisasi per triwulan
        $realisasiPerTriwulan = [];
        $labels = [];
        $colors = [
            'rgba(239, 68, 68, 0.8)',   // Merah untuk TW1
            'rgba(245, 158, 11, 0.8)',  // Orange untuk TW2
            'rgba(16, 185, 129, 0.8)',  // Hijau untuk TW3
            'rgba(59, 130, 246, 0.8)',  // Biru untuk TW4
        ];

        $totalRealisasi = 0;

        for ($i = 1; $i <= 4; $i++) {
            $realisasi = RealisasiAnggaranKas::where('tahun', $activeYear)
                ->where('triwulan', $i)
                ->where('status', 'completed')
                ->sum('jumlah_realisasi');

            if ($realisasi > 0) {
                $realisasiPerTriwulan[] = $realisasi;
                $labels[] = "Triwulan $i";
                $totalRealisasi += $realisasi;
            }
        }

        // Tambahkan sisa anggaran jika ada
        $sisaAnggaran = $totalRencana - $totalRealisasi;
        if ($sisaAnggaran > 0) {
            $realisasiPerTriwulan[] = $sisaAnggaran;
            $labels[] = 'Sisa Anggaran';
            $colors[] = 'rgba(156, 163, 175, 0.8)'; // Abu-abu untuk sisa anggaran
        }

        // Pastikan ada data untuk ditampilkan
        if (empty($realisasiPerTriwulan)) {
            $realisasiPerTriwulan = [$totalRencana];
            $labels = ['Belum Ada Realisasi'];
            $colors = ['rgba(156, 163, 175, 0.8)'];
        }

        return [
            'datasets' => [
                [
                    'data' => $realisasiPerTriwulan,
                    'backgroundColor' => array_slice($colors, 0, count($realisasiPerTriwulan)),
                    'borderColor' => array_map(function ($color) {
                        return str_replace('0.8', '1', $color);
                    }, array_slice($colors, 0, count($realisasiPerTriwulan))),
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
        return 'Distribusi Realisasi Anggaran per Triwulan - Tahun ' . $activeYear;
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

        $realisasiPerTriwulan = [];
        for ($i = 1; $i <= 4; $i++) {
            $realisasi = RealisasiAnggaranKas::where('tahun', $activeYear)
                ->where('triwulan', $i)
                ->where('status', 'completed')
                ->sum('jumlah_realisasi');
            $realisasiPerTriwulan[$i] = $realisasi;
        }

        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'right',
                    'labels' => [
                        'padding' => 15,
                        'usePointStyle' => true,
                        'font' => [
                            'size' => 12
                        ]
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
                    'enabled' => true,
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
            'elements' => [
                'arc' => [
                    'hoverBackgroundColor' => array_map(function ($color) {
                        return str_replace('0.8', '1', $color);
                    }, [
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(156, 163, 175, 0.8)'
                    ]),
                    'hoverBorderWidth' => 3,
                ]
            ]
        ];
    }

    // Method untuk custom tooltip
    protected function getExtraJs(): string
    {
        $activeYear = YearContext::getActiveYear();
        $totalRencana = RencanaAnggaranKas::where('status', 'approved')
            ->where('tahun', $activeYear)
            ->sum('jumlah_rencana');

        $realisasiPerTriwulan = [];
        for ($i = 1; $i <= 4; $i++) {
            $realisasi = RealisasiAnggaranKas::where('tahun', $activeYear)
                ->where('triwulan', $i)
                ->where('status', 'completed')
                ->sum('jumlah_realisasi');
            $realisasiPerTriwulan[$i] = $realisasi;
        }

        return "
        if (window.chart) {
            window.chart.options.plugins.tooltip.callbacks = {
                title: function(context) {
                    return 'Distribusi Anggaran Tahun {$activeYear}';
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
                    const label = context.label;
                    const value = context.raw || 0;
                    const totalRencana = {$totalRencana};
                    const realisasiData = " . json_encode($realisasiPerTriwulan) . ";
                    
                    if (label.includes('Triwulan')) {
                        const triwulan = label.split(' ')[1];
                        const targetPerTriwulan = totalRencana / 4;
                        const pencapaian = targetPerTriwulan > 0 ? ((value / targetPerTriwulan) * 100).toFixed(1) : 0;
                        
                        return [
                            'Target per TW: Rp ' + new Intl.NumberFormat('id-ID').format(targetPerTriwulan),
                            'Pencapaian: ' + pencapaian + '% dari target',
                            'Status: ' + (value >= targetPerTriwulan ? '✅ Tercapai' : '⚠️ Belum tercapai')
                        ];
                    } else if (label === 'Sisa Anggaran') {
                        const totalRealisasi = Object.values(realisasiData).reduce((a, b) => a + b, 0);
                        const sisaPersentase = totalRencana > 0 ? (((totalRencana - totalRealisasi) / totalRencana) * 100).toFixed(1) : 0;
                        
                        return [
                            'Total Rencana: Rp ' + new Intl.NumberFormat('id-ID').format(totalRencana),
                            'Total Realisasi: Rp ' + new Intl.NumberFormat('id-ID').format(totalRealisasi),
                            'Sisa: ' + sisaPersentase + '% dari rencana'
                        ];
                    } else if (label === 'Belum Ada Realisasi') {
                        return [
                            'Total Rencana: Rp ' + new Intl.NumberFormat('id-ID').format(totalRencana),
                            'Belum ada realisasi untuk tahun ini'
                        ];
                    }
                    return '';
                }
            };
        }
        ";
    }
}
