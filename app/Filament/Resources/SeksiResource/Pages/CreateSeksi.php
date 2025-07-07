<?php

namespace App\Filament\Resources\SeksiResource\Pages;

use App\Filament\Resources\SeksiResource;
use App\Services\CacheService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSeksi extends CreateRecord
{
    protected static string $resource = SeksiResource::class;
    protected function afterCreate(): void
    {
        $organisasiId = $this->record->bidang->organisasi_id;
        CacheService::clearSeksiCaches($this->record->bidang_id, $organisasiId);
    }
}
