<?php

namespace App\Filament\Resources\SeksiResource\Pages;

use App\Filament\Resources\SeksiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSeksis extends ListRecords
{
    protected static string $resource = SeksiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
