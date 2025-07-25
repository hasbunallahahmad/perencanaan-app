<?php

namespace App\Filament\Resources\RealisasiAnggaranKasResource\Pages;

use App\Filament\Resources\RealisasiAnggaranKasResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRealisasiAnggaranKas extends EditRecord
{
    protected static string $resource = RealisasiAnggaranKasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),
            Actions\ViewAction::make(), // PERBAIKAN: Ganti EditAction dengan ViewAction
            Actions\DeleteAction::make(),
        ];
    }

    // PERBAIKAN: Ganti mutateFormDataBeforeFill dengan fillForm untuk kontrol lebih baik
    protected function fillForm(): void
    {
        // Ambil data asli dari record
        $data = $this->record->attributesToArray();

        // Debug: Uncomment untuk melihat data asli
        // \Log::info('Data dari DB:', $data);

        // Pastikan data numerik dalam format yang benar
        $numericFields = [
            'rencana_tw_1',
            'rencana_tw_2',
            'rencana_tw_3',
            'rencana_tw_4',
            'realisasi_tw_1',
            'realisasi_tw_2',
            'realisasi_tw_3',
            'realisasi_tw_4',
            'realisasi_sd_tw',
            'persentase_total',
            'persentase_realisasi',
            'jumlah_realisasi'
        ];

        foreach ($numericFields as $field) {
            if (isset($data[$field])) {
                // Konversi ke float, jika null atau empty maka 0
                $data[$field] = $data[$field] ? (float) $data[$field] : 0;
            }
        }

        // Isi data relasi untuk display (field yang disabled)
        if ($this->record->rencanaAnggaranKas) {
            $rencana = $this->record->rencanaAnggaranKas;
            $data['jenis_anggaran'] = $rencana->jenis_anggaran_text;
            $data['pagu'] = $rencana->jumlah_rencana;
        }

        // Debug: Uncomment untuk melihat data setelah proses
        // \Log::info('Data setelah proses:', $data);

        $this->form->fill($data);
    }

    // PERBAIKAN: Tambaho method untuk memproses data sebelum disimpan
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Konversi semua field numerik ke float
        $numericFields = [
            'rencana_tw_1',
            'rencana_tw_2',
            'rencana_tw_3',
            'rencana_tw_4',
            'realisasi_tw_1',
            'realisasi_tw_2',
            'realisasi_tw_3',
            'realisasi_tw_4'
        ];

        foreach ($numericFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = $data[$field] ? (float) $data[$field] : 0;
            }
        }

        // Hitung ulang total realisasi
        $totalRealisasi = collect([
            $data['realisasi_tw_1'] ?? 0,
            $data['realisasi_tw_2'] ?? 0,
            $data['realisasi_tw_3'] ?? 0,
            $data['realisasi_tw_4'] ?? 0,
        ])->sum();

        // Hitung total rencana
        $totalRencana = collect([
            $data['rencana_tw_1'] ?? 0,
            $data['rencana_tw_2'] ?? 0,
            $data['rencana_tw_3'] ?? 0,
            $data['rencana_tw_4'] ?? 0,
        ])->sum();

        // Update field yang dihitung otomatis
        $data['realisasi_sd_tw'] = $totalRealisasi;
        $data['jumlah_realisasi'] = $totalRealisasi;

        // Hitung persentase total
        if ($totalRencana > 0) {
            $data['persentase_total'] = round(($totalRealisasi / $totalRencana) * 100, 2);
        } else {
            $data['persentase_total'] = 0;
        }

        // Hitung persentase realisasi berdasarkan pagu dari rencana anggaran
        if ($this->record->rencanaAnggaranKas && $this->record->rencanaAnggaranKas->jumlah_rencana > 0) {
            $data['persentase_realisasi'] = round(($totalRealisasi / $this->record->rencanaAnggaranKas->jumlah_rencana) * 100, 2);
        } else {
            $data['persentase_realisasi'] = 0;
        }

        // Set tanggal realisasi jika belum ada
        if (!isset($data['tanggal_realisasi']) || !$data['tanggal_realisasi']) {
            $data['tanggal_realisasi'] = now();
        }

        // Debug: Uncomment untuk melihat data yang akan disimpan
        // \Log::info('Data yang akan disimpan:', $data);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // PERBAIKAN: Tambahkan method untuk menangani setelah record disimpan
    protected function afterSave(): void
    {
        // Refresh data setelah save untuk memastikan perhitungan yang benar
        $this->record->refresh();

        // Kirim notifikasi sukses
        \Filament\Notifications\Notification::make()
            ->title('Berhasil!')
            ->body('Data realisasi anggaran kas berhasil diperbarui.')
            ->success()
            ->send();
    }
}
