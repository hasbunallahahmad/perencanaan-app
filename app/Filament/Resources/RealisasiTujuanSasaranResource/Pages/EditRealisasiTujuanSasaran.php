<?php

namespace App\Filament\Resources\RealisasiTujuanSasaranResource\Pages;

use App\Filament\Resources\RealisasiTujuanSasaranResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditRealisasiTujuanSasaran extends EditRecord
{
    protected static string $resource = RealisasiTujuanSasaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Validasi hanya boleh mengisi salah satu: tujuan atau sasaran
        if (!empty($data['master_tujuan_sasaran_id']) && !empty($data['master_sasaran_id'])) {
            Notification::make()
                ->title('Error Validasi')
                ->body('Tidak boleh mengisi tujuan dan sasaran sekaligus. Pilih salah satu.')
                ->danger()
                ->send();

            $this->halt();
        }

        if (empty($data['master_tujuan_sasaran_id']) && empty($data['master_sasaran_id'])) {
            Notification::make()
                ->title('Error Validasi')
                ->body('Harus memilih salah satu: Tujuan atau Sasaran.')
                ->danger()
                ->send();

            $this->halt();
        }

        return $data;
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Realisasi berhasil diperbarui')
            ->body('Data realisasi telah berhasil diperbarui.');
    }

    public function getTitle(): string
    {
        return 'Edit Realisasi - ' . $this->record->nama;
    }
}
