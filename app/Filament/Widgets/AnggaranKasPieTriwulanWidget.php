<?php

namespace App\Filament\Widgets;

use App\Models\RealisasiAnggaranKas;
use App\Models\RencanaAnggaranKas;
use App\Services\YearContext;
use Filament\Widgets\ChartWidget;

class AnggaranKasPieTriwulanWidget extends ChartWidget
{
    protected static ?string $heading = 'Realisasi Anggaran Berdasarkan Input Terakhir';

    protected static ?int $sort = 4;

    // Ubah dari 'full' ke 'half' untuk membuat berdampingan
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

            // Ambil rencana anggaran untuk mendapatkan pagu
            $rencanaAnggaran = RencanaAnggaranKas::where('status', 'approved')
                ->where('tahun', $activeYear)
                ->orderBy('created_at', 'desc')
                ->first();

            $totalRencana = $rencanaAnggaran ? $rencanaAnggaran->jumlah_rencana : 0;
            $triwulanLabel = 'Belum Ada Realisasi';
        } else {
            // Hitung total realisasi dari semua triwulan
            $totalRealisasi = ($latestRealisasi->realisasi_tw_1 ?? 0) +
                ($latestRealisasi->realisasi_tw_2 ?? 0) +
                ($latestRealisasi->realisasi_tw_3 ?? 0) +
                ($latestRealisasi->realisasi_tw_4 ?? 0);

            // Ambil total rencana dari relasi atau hitung dari triwulan
            if ($latestRealisasi->rencanaAnggaranKas) {
                $totalRencana = $latestRealisasi->rencanaAnggaranKas->jumlah_rencana ?? 0;
            } else {
                // Fallback: hitung dari rencana per triwulan
                $totalRencana = ($latestRealisasi->rencana_tw_1 ?? 0) +
                    ($latestRealisasi->rencana_tw_2 ?? 0) +
                    ($latestRealisasi->rencana_tw_3 ?? 0) +
                    ($latestRealisasi->rencana_tw_4 ?? 0);
            }

            // Tentukan sampai triwulan berapa yang sudah ada realisasi
            $lastTriwulan = 0;
            if ($latestRealisasi->realisasi_tw_4 > 0) $lastTriwulan = 4;
            elseif ($latestRealisasi->realisasi_tw_3 > 0) $lastTriwulan = 3;
            elseif ($latestRealisasi->realisasi_tw_2 > 0) $lastTriwulan = 2;
            elseif ($latestRealisasi->realisasi_tw_1 > 0) $lastTriwulan = 1;

            $triwulanLabel = $lastTriwulan > 0 ? "Realisasi s/d TW $lastTriwulan" : 'Belum Ada Realisasi';
        }

        // Hitung sisa anggaran
        $sisaAnggaran = $totalRencana - $totalRealisasi;
        $sisaAnggaran = max(0, $sisaAnggaran);

        // Data untuk pie chart - hanya 2 warna
        if ($totalRealisasi > 0) {
            $labels = [$triwulanLabel, 'Sisa Anggaran'];
            $data = [$totalRealisasi, $sisaAnggaran];
        } else {
            // Jika tidak ada data sama sekali, tampilkan pesan yang tepat
            if ($totalRencana > 0) {
                $labels = ['Belum Ada Realisasi'];
                $data = [$totalRencana];
            } else {
                $labels = ['Tidak Ada Data'];
                $data = [1]; // Minimal value untuk menampilkan chart
            }
        }

        $colors = [
            'rgba(16, 185, 129, 0.8)',  // Hijau untuk realisasi
            'rgba(245, 102, 39, 0.8)',  // Orange untuk sisa anggaran
            'rgba(156, 163, 175, 0.8)', // Abu-abu untuk tidak ada data
        ];

        $borderColors = [
            'rgb(16, 185, 129)',
            'rgb(245, 102, 39)',
            'rgb(156, 163, 175)',
        ];

        return [
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($data)),
                    'borderColor' => array_slice($borderColors, 0, count($data)),
                    'borderWidth' => 2,
                    'hoverOffset' => 4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    public function getHeading(): string
    {
        $activeYear = YearContext::getActiveYear();

        // Ambil realisasi terakhir untuk mendapatkan triwulan
        $latestRealisasi = RealisasiAnggaranKas::where('tahun', $activeYear)
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->first();

        if ($latestRealisasi) {
            // Tentukan sampai triwulan berapa yang sudah ada realisasi
            $lastTriwulan = 0;
            if ($latestRealisasi->realisasi_tw_4 > 0) $lastTriwulan = 4;
            elseif ($latestRealisasi->realisasi_tw_3 > 0) $lastTriwulan = 3;
            elseif ($latestRealisasi->realisasi_tw_2 > 0) $lastTriwulan = 2;
            elseif ($latestRealisasi->realisasi_tw_1 > 0) $lastTriwulan = 1;

            if ($lastTriwulan > 0) {
                return "Realisasi Anggaran s/d TW $lastTriwulan - $activeYear";
            }
        }

        return "Realisasi Anggaran - $activeYear";
    }

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                    'labels' => [
                        'padding' => 20,
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
                    'hoverBackgroundColor' => [
                        'rgba(16, 185, 129, 1)',
                        'rgba(245, 102, 39, 1)',
                        'rgba(156, 163, 175, 1)',
                    ],
                    'hoverBorderColor' => [
                        'rgb(16, 185, 129)',
                        'rgb(245, 102, 39)',
                        'rgb(156, 163, 175)',
                    ],
                    'hoverBorderWidth' => 3,
                ]
            ]
        ];
    }

    // Method untuk custom tooltip
    protected function getExtraJs(): string
    {
        $activeYear = YearContext::getActiveYear();

        // Ambil realisasi terakhir untuk JavaScript
        $latestRealisasi = RealisasiAnggaranKas::where('tahun', $activeYear)
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->first();

        if ($latestRealisasi) {
            // Hitung total realisasi dari semua triwulan
            $totalRealisasi = ($latestRealisasi->realisasi_tw_1 ?? 0) +
                ($latestRealisasi->realisasi_tw_2 ?? 0) +
                ($latestRealisasi->realisasi_tw_3 ?? 0) +
                ($latestRealisasi->realisasi_tw_4 ?? 0);

            // Ambil total rencana
            if ($latestRealisasi->rencanaAnggaranKas) {
                $totalRencana = $latestRealisasi->rencanaAnggaranKas->jumlah_rencana ?? 0;
                $jenisAnggaran = $latestRealisasi->rencanaAnggaranKas->jenis_anggaran_text ?? 'N/A';
            } else {
                $totalRencana = ($latestRealisasi->rencana_tw_1 ?? 0) +
                    ($latestRealisasi->rencana_tw_2 ?? 0) +
                    ($latestRealisasi->rencana_tw_3 ?? 0) +
                    ($latestRealisasi->rencana_tw_4 ?? 0);
                $jenisAnggaran = 'N/A';
            }

            // Tentukan triwulan terakhir
            $lastTriwulan = 0;
            if ($latestRealisasi->realisasi_tw_4 > 0) $lastTriwulan = 4;
            elseif ($latestRealisasi->realisasi_tw_3 > 0) $lastTriwulan = 3;
            elseif ($latestRealisasi->realisasi_tw_2 > 0) $lastTriwulan = 2;
            elseif ($latestRealisasi->realisasi_tw_1 > 0) $lastTriwulan = 1;

            $tanggalInput = $latestRealisasi->created_at->format('d/m/Y H:i');
        } else {
            $totalRealisasi = 0;
            $rencanaAnggaran = RencanaAnggaranKas::where('status', 'approved')
                ->where('tahun', $activeYear)
                ->orderBy('created_at', 'desc')
                ->first();
            $totalRencana = $rencanaAnggaran ? $rencanaAnggaran->jumlah_rencana : 0;
            $lastTriwulan = 0;
            $jenisAnggaran = 'N/A';
            $tanggalInput = 'N/A';
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
                    const label = context.label;
                    const totalRencana = {$totalRencana};
                    const totalRealisasi = {$totalRealisasi};
                    const lastTriwulan = {$lastTriwulan};
                    const jenisAnggaran = '{$jenisAnggaran}';
                    const tanggalInput = '{$tanggalInput}';
                    
                    if (label === 'Tidak Ada Data') {
                        return [
                            '───────────────────────',
                            'Silakan input rencana anggaran',
                            'terlebih dahulu untuk tahun ini'
                        ];
                    }
                    
                    if (label.includes('Realisasi TW') || label.includes('Realisasi s/d TW')) {
                        const efisiensi = totalRencana > 0 ? ((totalRealisasi / totalRencana) * 100).toFixed(1) : 0;
                        
                        return [
                            '───────────────────────',
                            'Jenis Anggaran: ' + jenisAnggaran,
                            'Total Rencana: Rp ' + new Intl.NumberFormat('id-ID').format(totalRencana),
                            'Efisiensi: ' + efisiensi + '%',
                            'Tanggal Input: ' + tanggalInput,
                            'Data dari: Input terakhir user'
                        ];
                    } else if (label === 'Sisa Anggaran') {
                        const sisaPersentase = totalRencana > 0 ? (((totalRencana - totalRealisasi) / totalRencana) * 100).toFixed(1) : 0;
                        
                        return [
                            '───────────────────────',
                            'Total Rencana: Rp ' + new Intl.NumberFormat('id-ID').format(totalRencana),
                            'Sisa: ' + sisaPersentase + '% dari rencana',
                            'Data dari: Input terakhir user'
                        ];
                    } else if (label === 'Belum Ada Realisasi') {
                        return [
                            '───────────────────────',
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
