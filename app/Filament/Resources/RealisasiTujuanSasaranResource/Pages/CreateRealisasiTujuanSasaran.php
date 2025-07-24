<?php

namespace App\Filament\Resources\RealisasiTujuanSasaranResource\Pages;

use App\Filament\Resources\RealisasiTujuanSasaranResource;
use App\Services\YearContext;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class CreateRealisasiTujuanSasaran extends CreateRecord
{
    protected static string $resource = RealisasiTujuanSasaranResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set tahun dari YearContext jika tidak diisi
        if (empty($data['tahun'])) {
            $data['tahun'] = YearContext::getActiveYear();
        }

        // Validasi berdasarkan jenis_data yang dipilih
        if (empty($data['jenis_data'])) {
            Notification::make()
                ->title('Error Validasi')
                ->body('Harus memilih jenis data: Tujuan atau Sasaran.')
                ->danger()
                ->send();

            $this->halt();
        }

        // Validasi untuk data tujuan
        if ($data['jenis_data'] === 'tujuan') {
            if (empty($data['master_tujuan_sasarans_id'])) {
                Notification::make()
                    ->title('Error Validasi')
                    ->body('Harus memilih Tujuan.')
                    ->danger()
                    ->send();

                $this->halt();
            }

            // JANGAN set null, hapus key dari array jika kolom tidak nullable
            if (array_key_exists('master_sasaran_id', $data)) {
                unset($data['master_sasaran_id']);
            }
        }

        // Validasi untuk data sasaran
        if ($data['jenis_data'] === 'sasaran') {
            if (empty($data['master_sasaran_id'])) {
                Notification::make()
                    ->title('Error Validasi')
                    ->body('Harus memilih Sasaran.')
                    ->danger()
                    ->send();

                $this->halt();
            }

            // JANGAN set null, hapus key dari array jika kolom tidak nullable
            if (array_key_exists('master_tujuan_sasarans_id', $data)) {
                unset($data['master_tujuan_sasarans_id']);
            }
        }

        // Hapus jenis_data dari data yang akan disimpan
        unset($data['jenis_data']);

        // Set default values untuk kolom yang required tapi tidak diisi
        $data['created_by'] = Auth::id() ?? 1;
        $data['updated_by'] = Auth::id() ?? 1;

        return $data;
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Realisasi berhasil ditambahkan')
            ->body('Data realisasi untuk tahun ' . YearContext::getActiveYear() . ' telah berhasil disimpan.');
    }

    public function getTitle(): string
    {
        return 'Tambah Realisasi - Tahun ' . YearContext::getActiveYear();
    }
}
