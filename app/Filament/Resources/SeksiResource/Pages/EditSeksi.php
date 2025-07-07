<?php

namespace App\Filament\Resources\SeksiResource\Pages;

use App\Filament\Resources\SeksiResource;
use App\Services\CacheService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSeksi extends EditRecord
{
    protected static string $resource = SeksiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function afterSave(): void
    {
        $organisasiId = $this->record->bidang->organisasi_id;
        CacheService::clearSeksiCaches($this->record->bidang_id, $organisasiId);
    }
}
