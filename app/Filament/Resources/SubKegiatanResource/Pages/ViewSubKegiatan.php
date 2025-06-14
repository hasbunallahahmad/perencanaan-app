<?php

namespace App\Filament\Resources\SubKegiatanResource\Pages;

use App\Filament\Resources\SubKegiatanResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSubKegiatan extends ViewRecord
{
    protected static string $resource = SubKegiatanResource::class;

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
