<?php

namespace App\Filament\Resources\RealisasiAnggaranKasResource\Pages;

use App\Filament\Resources\RealisasiAnggaranKasResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewRealisasiAnggaranKas extends ViewRecord
{
    protected static string $resource = RealisasiAnggaranKasResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),

            Actions\EditAction::make(),
        ];
    }
}
