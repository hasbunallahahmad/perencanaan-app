<?php

namespace App\Filament\Resources\TujuanSasaranResource\Pages;

use App\Filament\Resources\TujuanSasaranResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTujuanSasarans extends ListRecords
{
    protected static string $resource = TujuanSasaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Tujuan & Sasaran')
                ->icon('heroicon-o-plus'),
        ];
    }
}
