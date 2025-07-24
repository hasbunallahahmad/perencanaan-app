<?php

namespace App\Filament\Resources\TujuanSasaranResource\Pages;

use App\Filament\Resources\TujuanSasaranResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTujuanSasaran extends EditRecord
{
    protected static string $resource = TujuanSasaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
            Actions\Action::make('reset_realisasi')
                ->label('Reset Realisasi')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Reset Realisasi')
                ->modalDescription('Apakah Anda yakin ingin mengatur ulang semua realisasi ke 0?')
                ->action(fn() => $this->record->resetRealisasi())
                ->successNotificationTitle('Realisasi berhasil direset')
                ->after(fn() => $this->fillForm()),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Tujuan & Sasaran berhasil diperbarui';
    }
}
