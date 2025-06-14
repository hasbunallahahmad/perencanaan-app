<?php

namespace App\Filament\Resources\RencanaAksiResource\Pages;

use App\Filament\Resources\RencanaAksiResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\HtmlString;

class ViewRencanaAksi extends ViewRecord
{
  protected static string $resource = RencanaAksiResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\Action::make('back')
        ->label('Kembali')
        ->color('gray')
        ->icon('heroicon-o-arrow-left')
        ->url($this->getResource()::getUrl('index')),
      Actions\EditAction::make(),
      Actions\DeleteAction::make(),
    ];
  }

  public function infolist(Infolist $infolist): Infolist
  {
    return $infolist
      ->schema([
        // Section Informasi Program - Collapsible
        Infolists\Components\Section::make('Informasi Program')
          ->schema([
            Infolists\Components\Grid::make(2)
              ->schema([
                Infolists\Components\TextEntry::make('bidang.nama')
                  ->label('Bidang')
                  ->weight(FontWeight::SemiBold)
                  ->icon('heroicon-o-building-office'),

                Infolists\Components\TextEntry::make('tahun')
                  ->label('Tahun')
                  ->badge()
                  ->color('primary')
                  ->icon('heroicon-o-calendar'),

                Infolists\Components\TextEntry::make('program.nama_program')
                  ->label('Program')
                  ->columnSpanFull()
                  ->weight(FontWeight::Medium)
                  ->icon('heroicon-o-clipboard-document-list'),

                Infolists\Components\TextEntry::make('kegiatan.nama_kegiatan')
                  ->label('Kegiatan')
                  ->columnSpanFull()
                  ->icon('heroicon-o-cog-6-tooth'),

                Infolists\Components\TextEntry::make('subKegiatan.nama_sub_kegiatan')
                  ->label('Sub Kegiatan')
                  ->columnSpanFull()
                  ->icon('heroicon-o-list-bullet'),
              ]),
          ])
          ->collapsible()
          ->collapsed(true), // Default terbuka

        // Section Rencana Aksi - Collapsible
        Infolists\Components\Section::make('Rencana Aksi')
          ->schema([
            Infolists\Components\RepeatableEntry::make('rencana_aksi_list')
              ->label('')
              ->schema([
                Infolists\Components\TextEntry::make('aksi')
                  ->label('Aksi')
                  ->weight(FontWeight::SemiBold)
                  ->icon('heroicon-o-bolt')
                  ->copyable()
                  ->copyMessage('Aksi berhasil disalin!')
                  ->copyMessageDuration(1500),
              ])
              ->columns(1),
          ])
          ->collapsible()
          ->collapsed(false),

        // Section Jadwal Pelaksanaan dengan Gantt Chart - Collapsible
        Infolists\Components\Section::make('Jadwal Pelaksanaan')
          ->schema([
            // Timeline Visual Gantt Chart menggunakan komponen yang sudah dibuat
            Infolists\Components\ViewEntry::make('gantt_chart')
              ->label('')
              ->view('filament.components.gantt-chart')
              ->viewData(function ($record) {
                return [
                  'rencana_aksi_list' => $record->rencana_aksi_list ?? [],
                  'rencana_pelaksanaan' => $this->formatRencanaPelaksanaan($record->rencana_pelaksanaan ?? []),
                  'tahun' => $record->tahun,
                ];
              }),
          ])
          ->collapsible()
          ->collapsed(false),

        // Section Anggaran & Narasumber - Collapsible
        Infolists\Components\Section::make('Anggaran & Narasumber')
          ->schema([
            Infolists\Components\Grid::make(2)
              ->schema([
                Infolists\Components\TextEntry::make('jenis_anggaran')
                  ->label('Jenis Anggaran')
                  ->badge()
                  ->separator(',')
                  ->color('warning')
                  ->icon('heroicon-o-currency-dollar'),

                Infolists\Components\TextEntry::make('narasumber')
                  ->label('Narasumber')
                  ->badge()
                  ->separator(',')
                  ->color('info')
                  ->icon('heroicon-o-user-group'),
              ]),
          ])
          ->collapsible()
          ->collapsed(true),

        // Section Informasi Sistem - Collapsible
        Infolists\Components\Section::make('Informasi Sistem')
          ->schema([
            Infolists\Components\Grid::make(2)
              ->schema([
                Infolists\Components\TextEntry::make('created_at')
                  ->label('Dibuat')
                  ->dateTime()
                  ->icon('heroicon-o-calendar-days'),

                Infolists\Components\TextEntry::make('updated_at')
                  ->label('Diubah')
                  ->dateTime()
                  ->icon('heroicon-o-pencil-square'),

                Infolists\Components\TextEntry::make('created_by')
                  ->label('Dibuat Oleh')
                  ->default('Sistem')
                  ->icon('heroicon-o-user'),

                Infolists\Components\TextEntry::make('updated_by')
                  ->label('Diubah Oleh')
                  ->default('Sistem')
                  ->icon('heroicon-o-user'),
              ]),
          ])
          ->collapsible()
          ->collapsed(true), // Default tertutup
      ]);
  }

  /**
   * Format rencana pelaksanaan dari array angka menjadi format yang diharapkan
   */
  protected function formatRencanaPelaksanaan($data): array
  {
    if (empty($data) || !is_array($data)) {
      return [];
    }

    // Jika data sudah dalam format yang benar (object/associative array)
    if (is_array($data) && !empty($data) && is_array($data[0] ?? null)) {
      return $data;
    }

    // Jika data adalah array sederhana dari angka/string bulan
    $formatted = [];
    foreach ($data as $bulan) {
      $formatted[] = [
        'bulan' => $bulan,
        'minggu_ke' => null,
        'target' => 'Sesuai rencana aksi',
        'keterangan' => '-'
      ];
    }

    return $formatted;
  }

  /**
   * Format jadwal pelaksanaan untuk ditampilkan sebagai badge
   */
  // protected function formatJadwalPelaksanaan($data): array
  // {
  //   // if (empty($data) || !is_array($data)) {
  //   //   return ['Belum dijadwalkan'];
  //   // }

  //   // $bulanNames = [];
  //   // foreach ($data as $item) {
  //   //   if (is_string($item) || is_numeric($item)) {
  //   //     // Jika item adalah angka/string bulan langsung
  //   //     $bulanNames[] = $this->formatBulanName($item);
  //   //   } elseif (is_array($item) && isset($item['bulan'])) {
  //   //     // Jika item adalah object dengan key bulan
  //   //     $bulanNames[] = $this->formatBulanName($item['bulan']);
  //   //   }
  //   // }
  //   if (empty($data) || !is_array($data)) {
  //     return ['Belum ada jadwal'];
  //   }

  //   $bulanNames = [];
  //   foreach ($data as $item) {
  //     if (is_string($item) || is_numeric($item)) {
  //       $bulanNames[] = $this->formatBulanName($item);
  //     } elseif (is_array($item) && isset($item['bulan'])) {
  //       $bulanNames[] = $this->formatBulanName($item['bulan']);
  //     }
  //   }
  //   // return !empty($bulanNames) ? $bulanNames : ['Belum dijadwalkan'];
  //   // if (empty($data) || !is_array($data)) {
  //   //   return ['Belum dijadwalkan'];
  //   // }

  //   // $bulanNames = [];
  //   // foreach ($data as $item) {
  //   //   $bulan = is_array($item) ? ($item['bulan'] ?? null) : $item;
  //   //   if ($bulan) {
  //   //     $bulanNames[] = $this->formatBulanName($bulan);
  //   //   }
  //   // }

  //   return !empty($bulanNames) ? array_unique($bulanNames) : ['Belum dijadwalkan'];
  // }

  /**
   * Format nama bulan dari angka ke nama Indonesia
   */
  protected function formatBulanName($bulan): string
  {
    if (is_array($bulan)) {
      return 'Format tidak valid';
    }
    $months = [
      '01' => 'Januari',
      '1' => 'Januari',
      '02' => 'Februari',
      '2' => 'Februari',
      '03' => 'Maret',
      '3' => 'Maret',
      '04' => 'April',
      '4' => 'April',
      '05' => 'Mei',
      '5' => 'Mei',
      '06' => 'Juni',
      '6' => 'Juni',
      '07' => 'Juli',
      '7' => 'Juli',
      '08' => 'Agustus',
      '8' => 'Agustus',
      '09' => 'September',
      '9' => 'September',
      '10' => 'Oktober',
      '11' => 'November',
      '12' => 'Desember',
      // Untuk nama bulan yang sudah dalam bahasa Indonesia
      'januari' => 'Januari',
      'februari' => 'Februari',
      'maret' => 'Maret',
      'april' => 'April',
      'mei' => 'Mei',
      'juni' => 'Juni',
      'juli' => 'Juli',
      'agustus' => 'Agustus',
      'september' => 'September',
      'oktober' => 'Oktober',
      'november' => 'November',
      'desember' => 'Desember',
    ];

    $key = strtolower(trim($bulan));
    return $months[$key] ?? ucfirst($bulan);
  }

  // Method untuk mendapatkan progress berdasarkan bulan aktif
  protected function calculateProgress($record): int
  {
    if (empty($record->rencana_pelaksanaan)) {
      return 0;
    }

    $totalBulan = 12;
    $data = $record->rencana_pelaksanaan;

    if (is_array($data)) {
      $bulanAktif = count($data);
    } else {
      $bulanAktif = 0;
    }

    return round(($bulanAktif / $totalBulan) * 100);
  }
}
