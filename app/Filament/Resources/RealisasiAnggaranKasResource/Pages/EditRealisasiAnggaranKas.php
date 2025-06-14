<?php

namespace App\Filament\Resources\RealisasiAnggaranKasResource\Pages;

use App\Filament\Resources\RealisasiAnggaranKasResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRealisasiAnggaranKas extends EditRecord
{
    protected static string $resource = RealisasiAnggaranKasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
