<?php

namespace App\Filament\Resources\RealisasiAnggaranKasResource\Pages;

use App\Filament\Resources\RealisasiAnggaranKasResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRealisasiAnggaranKas extends ListRecords
{
    protected static string $resource = RealisasiAnggaranKasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Realisasi Anggaran Kas')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->tooltip('Tambah realisasi anggaran kas baru'),
        ];
    }
}
