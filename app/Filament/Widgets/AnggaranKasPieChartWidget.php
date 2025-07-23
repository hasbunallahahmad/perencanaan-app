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

        // Ambil realisasi terakhir berdasarkan input user terakhir
        $latestRealisasi = RealisasiAnggaranKas::where('tahun', $activeYear)
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->first();

        // Jika tidak ada realisasi, set default values
        if (!$latestRealisasi) {
            $totalRealisasi = 0;

            // Perbaikan: Tambahkan null check
            $rencanaAnggaran = RencanaAnggaranKas::where('status', 'approved')
                ->where('tahun', $activeYear)
                ->orderBy('created_at', 'desc')
                ->first();

            $totalRencana = $rencanaAnggaran ? $rencanaAnggaran->jumlah_rencana : 0;
        } else {
            // Ambil total realisasi dari input terakhir user
            $totalRealisasi = $latestRealisasi->jumlah_realisasi ?? 0;

            // Perbaikan: Tambahkan null check untuk relasi
            $totalRencana = 0;
            if ($latestRealisasi->rencanaAnggaranKas) {
                $totalRencana = $latestRealisasi->rencanaAnggaranKas->jumlah_rencana ?? 0;
            }
        }

        // Hitung sisa anggaran
        $sisaAnggaran = $totalRencana - $totalRealisasi;

        // Pastikan sisa anggaran tidak negatif
        $sisaAnggaran = max(0, $sisaAnggaran);

        // Data untuk pie chart
        if ($totalRealisasi > 0) {
            $labels = ['Realisasi', 'Sisa Anggaran'];
            $data = [$totalRealisasi, $sisaAnggaran];
        } else {
            // Perbaikan: Jika tidak ada data sama sekali, tampilkan pesan yang tepat
            if ($totalRencana > 0) {
                $labels = ['Belum Ada Realisasi'];
                $data = [$totalRencana];
            } else {
                $labels = ['Tidak Ada Data'];
                $data = [1]; // Minimal value untuk menampilkan chart
            }
        }

        $colors = [
            'rgb(16, 185, 129)',      // Hijau untuk realisasi
            'rgb(59, 130, 246)',      // Biru untuk sisa anggaran
            'rgb(156, 163, 175)',     // Abu-abu untuk tidak ada data
        ];
        $backgroundColors = [
            'rgba(16, 185, 129, 0.8)',
            'rgba(59, 130, 246, 0.8)',
            'rgba(156, 163, 175, 0.8)',
        ];

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => array_slice($backgroundColors, 0, count($data)),
                    'borderColor' => array_slice($colors, 0, count($data)),
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
        return 'Perbandingan Anggaran & Realisasi - Tahun ' . $activeYear;
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        $activeYear = YearContext::getActiveYear();

        // Ambil realisasi terakhir untuk tooltip
        $latestRealisasi = RealisasiAnggaranKas::where('tahun', $activeYear)
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->first();

        $totalRealisasi = $latestRealisasi ? ($latestRealisasi->jumlah_realisasi ?? 0) : 0;

        // Perbaikan: Tambahkan null check untuk rencana anggaran
        $totalRencana = 0;
        if ($latestRealisasi && $latestRealisasi->rencanaAnggaranKas) {
            $totalRencana = $latestRealisasi->rencanaAnggaranKas->jumlah_rencana ?? 0;
        } else {
            $rencanaAnggaran = RencanaAnggaranKas::where('status', 'approved')
                ->where('tahun', $activeYear)
                ->orderBy('created_at', 'desc')
                ->first();
            $totalRencana = $rencanaAnggaran ? $rencanaAnggaran->jumlah_rencana : 0;
        }

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
                        'rgba(156, 163, 175, 1)',
                    ],
                    'hoverBorderColor' => [
                        'rgb(16, 185, 129)',
                        'rgb(59, 130, 246)',
                        'rgb(156, 163, 175)',
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

        // Ambil realisasi terakhir untuk JavaScript
        $latestRealisasi = RealisasiAnggaranKas::where('tahun', $activeYear)
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->first();

        $totalRealisasi = $latestRealisasi ? ($latestRealisasi->jumlah_realisasi ?? 0) : 0;

        // Perbaikan: Tambahkan null check untuk rencana anggaran
        $totalRencana = 0;
        if ($latestRealisasi && $latestRealisasi->rencanaAnggaranKas) {
            $totalRencana = $latestRealisasi->rencanaAnggaranKas->jumlah_rencana ?? 0;
        } else {
            $rencanaAnggaran = RencanaAnggaranKas::where('status', 'approved')
                ->where('tahun', $activeYear)
                ->orderBy('created_at', 'desc')
                ->first();
            $totalRencana = $rencanaAnggaran ? $rencanaAnggaran->jumlah_rencana : 0;
        }

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
                    
                    if (label === 'Tidak Ada Data') {
                        return 'Belum ada data untuk tahun ini';
                    }
                    
                    return [
                        label + ': Rp ' + formattedValue,
                        'Persentase: ' + percentage + '%'
                    ];
                },
                afterLabel: function(context) {
                    const totalRencana = {$totalRencana};
                    const totalRealisasi = {$totalRealisasi};
                    
                    if (context.label === 'Tidak Ada Data') {
                        return [
                            '───────────────────────',
                            'Silakan input rencana anggaran',
                            'terlebih dahulu untuk tahun ini'
                        ];
                    }
                    
                    if (context.label === 'Realisasi') {
                        const efisiensi = totalRencana > 0 ? ((totalRealisasi / totalRencana) * 100).toFixed(1) : 0;
                        return [
                            '───────────────────────',
                            'Total Rencana: Rp ' + new Intl.NumberFormat('id-ID').format(totalRencana),
                            'Efisiensi: ' + efisiensi + '%',
                            'Data dari: Input terakhir user'
                        ];
                    } else if (context.label === 'Sisa Anggaran') {
                        const sisaPersentase = totalRencana > 0 ? (((totalRencana - totalRealisasi) / totalRencana) * 100).toFixed(1) : 0;
                        return [
                            '───────────────────────',
                            'Total Rencana: Rp ' + new Intl.NumberFormat('id-ID').format(totalRencana),
                            'Sisa: ' + sisaPersentase + '% dari rencana',
                            'Data dari: Input terakhir user'
                        ];
                    } else if (context.label === 'Belum Ada Realisasi') {
                        return [
                            '───────────────────────',
                            'Total Rencana: Rp ' + new Intl.NumberFormat('id-ID').format(totalRencana),
                            'Belum ada realisasi untuk tahun ini',
                            'Data dari: Rencana anggaran'
                        ];
                    }
                    return '';
                }
            };
        }
        ";
    }
}
