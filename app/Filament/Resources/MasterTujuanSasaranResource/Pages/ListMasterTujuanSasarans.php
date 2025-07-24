<?php

namespace App\Filament\Resources\MasterTujuanSasaranResource\Pages;

use App\Filament\Resources\MasterTujuanSasaranResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMasterTujuanSasarans extends ListRecords
{
    protected static string $resource = MasterTujuanSasaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
