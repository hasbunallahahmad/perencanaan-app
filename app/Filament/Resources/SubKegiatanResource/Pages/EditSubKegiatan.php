<?php

namespace App\Filament\Resources\SubKegiatanResource\Pages;

use App\Filament\Resources\SubKegiatanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSubKegiatan extends EditRecord
{
    protected static string $resource = SubKegiatanResource::class;

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

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Sub Kegiatan berhasil diperbarui';
    }
}
