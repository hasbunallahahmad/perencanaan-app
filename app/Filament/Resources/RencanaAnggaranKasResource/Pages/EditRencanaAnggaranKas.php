<?php

namespace App\Filament\Resources\RencanaAnggaranKasResource\Pages;

use App\Filament\Resources\RencanaAnggaranKasResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRencanaAnggaranKas extends EditRecord
{
    protected static string $resource = RencanaAnggaranKasResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
