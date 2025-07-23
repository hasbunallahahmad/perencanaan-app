<?php

namespace App\Filament\Resources\RealisasiAnggaranKasResource\Pages;

use App\Models\RealisasiAnggaranKas;
use App\Models\RencanaAnggaranKas;
use App\Services\YearContext;
use Filament\Resources\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

class TableFormRealisasi extends Page
{
  protected static string $resource = \App\Filament\Resources\RealisasiAnggaranKasResource::class;

  protected static string $view = 'filament.resources.realisasi-anggaran-kas.pages.table-form';

  protected static ?string $title = 'Input Realisasi Anggaran Kas';

  public int $selectedYear;
  public Collection $rencanaAnggaranKas;
  public array $realisasiData = [];

  public function mount(): void
  {
    $this->selectedYear = YearContext::getActiveYear();
    $this->loadData();
  }

  public function updatedSelectedYear(): void
  {
    $this->loadData();
  }

  public function loadData(): void
  {
    $this->rencanaAnggaranKas = RencanaAnggaranKas::where('tahun', $this->selectedYear)
      ->where('status', 'approved')
      ->get();

    // Initialize realisasi data
    $this->realisasiData = [];
    foreach ($this->rencanaAnggaranKas as $rencana) {
      $existingRealisasi = RealisasiAnggaranKas::where('rencana_anggaran_kas_id', $rencana->id)
        ->get()
        ->keyBy('triwulan');

      $this->realisasiData[$rencana->id] = [
        'tw1' => $existingRealisasi->get(1)?->jumlah_realisasi ?? 0,
        'tw2' => $existingRealisasi->get(2)?->jumlah_realisasi ?? 0,
        'tw3' => $existingRealisasi->get(3)?->jumlah_realisasi ?? 0,
        'tw4' => $existingRealisasi->get(4)?->jumlah_realisasi ?? 0,
      ];
    }
  }

  public function save(): void
  {
    try {
      foreach ($this->realisasiData as $rencanaId => $data) {
        $rencana = RencanaAnggaranKas::find($rencanaId);

        for ($tw = 1; $tw <= 4; $tw++) {
          $realisasiAmount = (float) ($data["tw{$tw}"] ?? 0);

          if ($realisasiAmount > 0) {
            $percentage = $rencana->jumlah_rencana > 0
              ? round(($realisasiAmount / $rencana->jumlah_rencana) * 100, 2)
              : 0;

            RealisasiAnggaranKas::updateOrCreate(
              [
                'rencana_anggaran_kas_id' => $rencanaId,
                'triwulan' => $tw,
              ],
              [
                'tahun' => $this->selectedYear,
                'jumlah_realisasi' => $realisasiAmount,
                'persentase_realisasi' => $percentage,
                'tanggal_realisasi' => now(),
                'status' => 'completed',
                'deskripsi' => "Realisasi Triwulan {$tw} - {$rencana->jenis_anggaran_text}",
              ]
            );
          } else {
            // Delete if amount is 0
            RealisasiAnggaranKas::where('rencana_anggaran_kas_id', $rencanaId)
              ->where('triwulan', $tw)
              ->delete();
          }
        }
      }

      Notification::make()
        ->title('Berhasil!')
        ->body('Data realisasi anggaran kas berhasil disimpan.')
        ->success()
        ->send();

      $this->loadData(); // Refresh data

    } catch (\Exception $e) {
      Notification::make()
        ->title('Error!')
        ->body('Terjadi kesalahan: ' . $e->getMessage())
        ->danger()
        ->send();
    }
  }

  public function resetForm(): void
  {
    $this->loadData();

    Notification::make()
      ->title('Form direset!')
      ->body('Data form telah dikembalikan ke nilai awal.')
      ->info()
      ->send();
  }

  public function getTotalRealisasi(int $rencanaId): float
  {
    $data = $this->realisasiData[$rencanaId] ?? [];
    return array_sum($data);
  }

  public function getPersentase(int $rencanaId): float
  {
    $rencana = $this->rencanaAnggaranKas->find($rencanaId);
    if (!$rencana || $rencana->jumlah_rencana <= 0) {
      return 0;
    }

    $totalRealisasi = $this->getTotalRealisasi($rencanaId);
    return round(($totalRealisasi / $rencana->jumlah_rencana) * 100, 2);
  }
}
