<?php

namespace App\Filament\Resources\MasterTujuanSasaranResource\Pages;

use App\Filament\Resources\MasterTujuanSasaranResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMasterTujuanSasaran extends CreateRecord
{
    protected static string $resource = MasterTujuanSasaranResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
