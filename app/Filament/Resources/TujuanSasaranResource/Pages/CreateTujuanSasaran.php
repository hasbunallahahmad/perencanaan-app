<?php

namespace App\Filament\Resources\TujuanSasaranResource\Pages;

use App\Filament\Resources\TujuanSasaranResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTujuanSasaran extends CreateRecord
{
    protected static string $resource = TujuanSasaranResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreateFormAction(): Actions\Action
    {
        return parent::getCreateFormAction()
            ->label('Simpan')
            ->icon('heroicon-o-check');
    }

    protected function getCancelFormAction(): Actions\Action
    {
        return parent::getCancelFormAction()
            ->label('Batal');
    }
}
