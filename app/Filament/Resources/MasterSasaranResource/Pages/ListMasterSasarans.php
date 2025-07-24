<?php

namespace App\Filament\Resources\MasterSasaranResource\Pages;

use App\Filament\Resources\MasterSasaranResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterSasarans extends ListRecords
{
    protected static string $resource = MasterSasaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
