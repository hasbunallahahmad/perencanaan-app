<?php

namespace App\Filament\Resources\SerapanAnggaranResource\Pages;

use App\Filament\Resources\SerapanAnggaranResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSerapanAnggaran extends EditRecord
{
    protected static string $resource = SerapanAnggaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
