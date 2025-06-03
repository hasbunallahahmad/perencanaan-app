<?php

namespace App\Filament\Resources\SerapanAnggaranResource\Pages;

use App\Filament\Resources\SerapanAnggaranResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSerapanAnggaran extends ViewRecord
{
    protected static string $resource = SerapanAnggaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
