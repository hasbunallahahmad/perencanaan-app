<?php

namespace App\Filament\Resources\MasterSasaranResource\Pages;

use App\Filament\Resources\MasterSasaranResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMasterSasaran extends CreateRecord
{
    protected static string $resource = MasterSasaranResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
