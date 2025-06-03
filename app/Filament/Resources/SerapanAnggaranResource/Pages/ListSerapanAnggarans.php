<?php

namespace App\Filament\Resources\SerapanAnggaranResource\Pages;

use App\Filament\Resources\SerapanAnggaranResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSerapanAnggarans extends ListRecords
{
    protected static string $resource = SerapanAnggaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
