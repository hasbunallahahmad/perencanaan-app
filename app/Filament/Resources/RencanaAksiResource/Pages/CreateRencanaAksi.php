<?php

namespace App\Filament\Resources\RencanaAksiResource\Pages;

use App\Filament\Resources\RencanaAksiResource;
use App\Services\YearContext;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateRencanaAksi extends CreateRecord
{
    protected static string $resource = RencanaAksiResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Rencana Aksi berhasil dibuat')
            ->body('Data rencana aksi telah disimpan untuk tahun ' . YearContext::getActiveYear());
    }
    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url($this->getResource()::getUrl('index'))
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
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
        return 'Tambah Rencana Aksi - Tahun ' . YearContext::getActiveYear();
    }

    // Untuk Filament 3, gunakan pendekatan yang berbeda untuk menyembunyikan form actions
    protected function hasFormActions(): bool
    {
        return false;
    }

    // Override method ini jika masih muncul tombol default
    public function getFormActions(): array
    {
        return [];
    }
}
