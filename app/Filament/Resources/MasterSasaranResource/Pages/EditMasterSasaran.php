<?php

namespace App\Filament\Resources\MasterSasaranResource\Pages;

use App\Filament\Resources\MasterSasaranResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterSasaran extends EditRecord
{
    protected static string $resource = MasterSasaranResource::class;

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
}
