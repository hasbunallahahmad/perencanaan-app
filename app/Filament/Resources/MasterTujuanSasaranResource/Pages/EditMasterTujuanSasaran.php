<?php

namespace App\Filament\Resources\MasterTujuanSasaranResource\Pages;

use App\Filament\Resources\MasterTujuanSasaranResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterTujuanSasaran extends EditRecord
{
    protected static string $resource = MasterTujuanSasaranResource::class;

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
