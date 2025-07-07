<?php

namespace App\Filament\Widgets;

use App\Models\RencanaAnggaranKas;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class RencanaAnggaranKasWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getHeading(): ?string
    {
        $currentYear = date('Y');
        $latestData = RencanaAnggaranKas::getWidgetData($currentYear);
        $status = $latestData['status'] ?? 'Belum ada data';

        return "Rencana Anggaran Kas {$currentYear} - Status: {$status}";
    }

    protected function getStats(): array
    {
        $currentYear = date('Y');

        // Mendapatkan data anggaran terbaru
        $latestData = RencanaAnggaranKas::getWidgetData($currentYear);

        // Mendapatkan ringkasan anggaran
        $ringkasan = RencanaAnggaranKas::getRingkasanAnggaran($currentYear);

        // Menghitung persentase perubahan dari anggaran murni ke anggaran terbaru
        $anggaranMurni = $ringkasan['anggaran_murni']['total'] ?? 0;
        $anggaranTerbaru = $latestData['total_anggaran'] ?? 0;

        $perubahanPersentase = 0;
        if ($anggaranMurni > 0) {
            $perubahanPersentase = (($anggaranTerbaru - $anggaranMurni) / $anggaranMurni) * 100;
        }

        return [
            Stat::make('Anggaran Terbaru', 'Rp ' . number_format($anggaranTerbaru, 0, ',', '.'))
                ->description($latestData['jenis_anggaran'] ?? 'Belum ada data')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color($perubahanPersentase >= 0 ? 'success' : 'danger')
                ->chart([
                    $ringkasan['anggaran_murni']['total'] ?? 0,
                    $ringkasan['pergeseran']['total'] ?? 0,
                    $ringkasan['perubahan']['total'] ?? 0,
                ]),

            Stat::make('Anggaran Murni', 'Rp ' . number_format($anggaranMurni, 0, ',', '.'))
                ->description('Anggaran dasar tahun ' . $currentYear)
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),

            Stat::make('Perubahan Anggaran', number_format(abs($perubahanPersentase), 1) . '%')
                ->description($perubahanPersentase >= 0 ? 'Kenaikan' : 'Penurunan')
                ->descriptionIcon($perubahanPersentase >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($perubahanPersentase >= 0 ? 'success' : 'danger'),

            Stat::make('Status Anggaran', $latestData['status'] ?? 'Belum ada data')
                ->description('Terakhir diperbarui: ' .
                    ($latestData['tanggal_update'] ?
                        Carbon::parse($latestData['tanggal_update'])->format('d M Y') :
                        'Belum ada data'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),
        ];
    }

    protected function getPollingInterval(): ?string
    {
        return '30s'; // Refresh setiap 30 detik
    }
}
