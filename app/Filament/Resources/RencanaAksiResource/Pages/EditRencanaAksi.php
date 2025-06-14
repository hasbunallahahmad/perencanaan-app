<?php

namespace App\Filament\Resources\RencanaAksiResource\Pages;

use App\Filament\Resources\RencanaAksiResource;
use App\Services\YearContext;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditRencanaAksi extends EditRecord
{
    protected static string $resource = RencanaAksiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Rencana Aksi berhasil diperbarui')
            ->body('Perubahan data telah disimpan');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Ensure tahun matches current active year
        $data['tahun'] = YearContext::getActiveYear();

        // Ensure arrays are properly formatted
        $data['rencana_aksi_list'] = $data['rencana_aksi_list'] ?? [];
        $data['rencana_pelaksanaan'] = $data['rencana_pelaksanaan'] ?? [];
        $data['jenis_anggaran'] = $data['jenis_anggaran'] ?? [];
        $data['narasumber'] = $data['narasumber'] ?? [];

        return $data;
    }

    public function getTitle(): string
    {
        return 'Edit Rencana Aksi - Tahun ' . YearContext::getActiveYear();
    }
}
