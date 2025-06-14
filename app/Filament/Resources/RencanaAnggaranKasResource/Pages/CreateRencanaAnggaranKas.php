<?php

namespace App\Filament\Resources\RencanaAnggaranKasResource\Pages;

use App\Filament\Resources\RencanaAnggaranKasResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateRencanaAnggaranKas extends CreateRecord
{
    protected static string $resource = RencanaAnggaranKasResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
