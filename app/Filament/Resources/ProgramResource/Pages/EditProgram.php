<?php

namespace App\Filament\Resources\ProgramResource\Pages;

use App\Filament\Resources\ProgramResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditProgram extends EditRecord
{
    protected static string $resource = ProgramResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('Lihat')
                ->icon('heroicon-o-eye'),
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading('Hapus Program')
                ->modalDescription('Apakah Anda yakin ingin menghapus program ini? Data kegiatan yang terkait juga akan terhapus.')
                ->modalSubmitActionLabel('Ya, Hapus'),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Program berhasil diperbarui')
            ->body('Program ' . $this->record->nama_program . ' telah berhasil diperbarui.');
    }
}
