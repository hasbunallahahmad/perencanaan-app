<?php

namespace App\Filament\Resources\TujuanSasaranResource\Pages;

use App\Filament\Resources\TujuanSasaranResource;
use Filament\Actions;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Resources\Pages\ViewRecord;


class ViewTujuanSasaran extends ViewRecord
{
    protected static string $resource = TujuanSasaranResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Edit')
                ->icon('heroicon-o-pencil'),
        ];
    }
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Utama')
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                TextEntry::make('tujuan')
                                    ->label('Tujuan')
                                    ->columnSpanFull(),

                                TextEntry::make('sasaran')
                                    ->label('Sasaran')
                                    ->columnSpanFull(),

                                TextEntry::make('indikator')
                                    ->label('Indikator')
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Section::make('Target & Satuan')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('target')
                                    ->label('Target')
                                    ->numeric(5),

                                TextEntry::make('satuan')
                                    ->label('Satuan'),
                            ]),
                    ]),

                Section::make('Realisasi per Triwulan')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('realisasi_tw_1')
                                    ->label('Triwulan 1')
                                    ->numeric(2)
                                    ->placeholder('Belum ada data'),

                                TextEntry::make('realisasi_tw_2')
                                    ->label('Triwulan 2')
                                    ->numeric(2)
                                    ->placeholder('Belum ada data'),

                                TextEntry::make('realisasi_tw_3')
                                    ->label('Triwulan 3')
                                    ->numeric(2)
                                    ->placeholder('Belum ada data'),

                                TextEntry::make('realisasi_tw_4')
                                    ->label('Triwulan 4')
                                    ->numeric(2)
                                    ->placeholder('Belum ada data'),
                            ]),
                    ]),

                Section::make('Ringkasan Pencapaian')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('total_realisasi')
                                    ->label('Total Realisasi')
                                    ->state(function ($record) {
                                        return ($record->realisasi_tw_1 ?? 0) +
                                            ($record->realisasi_tw_2 ?? 0) +
                                            ($record->realisasi_tw_3 ?? 0) +
                                            ($record->realisasi_tw_4 ?? 0);
                                    })
                                    ->numeric(2),

                                TextEntry::make('persentase')
                                    ->label('Persentase Pencapaian')
                                    ->suffix('%')
                                    ->numeric(2)
                                    ->color(fn($state): string => match (true) {
                                        $state >= 100 => 'success',
                                        $state >= 75 => 'warning',
                                        default => 'danger',
                                    }),
                            ]),
                    ]),
            ]);
    }
}
