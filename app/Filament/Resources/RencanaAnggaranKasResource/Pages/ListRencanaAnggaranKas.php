<?php

namespace App\Filament\Resources\RencanaAnggaranKasResource\Pages;

use App\Filament\Resources\RencanaAnggaranKasResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRencanaAnggaranKas extends ListRecords
{
    protected static string $resource = RencanaAnggaranKasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Rencana Anggaran Kas')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->tooltip('Tambah rencana anggaran kas baru'),
        ];
    }
}
