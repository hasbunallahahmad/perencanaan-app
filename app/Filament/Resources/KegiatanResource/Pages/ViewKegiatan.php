<?php

namespace App\Filament\Resources\KegiatanResource\Pages;

use App\Filament\Resources\KegiatanResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Support\Enums\FontWeight;

class ViewKegiatan extends ViewRecord
{
    protected static string $resource = KegiatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Kegiatan')
                    ->schema([
                        TextEntry::make('kode_kegiatan')
                            ->label('Kode Kegiatan')
                            ->weight(FontWeight::Bold)
                            ->copyable(),

                        TextEntry::make('nama_kegiatan')
                            ->label('Nama Kegiatan')
                            ->columnSpanFull(),

                        TextEntry::make('program.kode_program')
                            ->label('Kode Program')
                            ->copyable(),

                        TextEntry::make('program.nama_program')
                            ->label('Nama Program')
                            ->columnSpanFull(),

                        TextEntry::make('program.organisasi.nama')
                            ->label('Organisasi')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Informasi Anggaran')
                    ->schema([
                        TextEntry::make('total_anggaran')
                            ->label('Total Anggaran')
                            ->money('Rp')
                            ->weight(FontWeight::Bold),

                        TextEntry::make('total_realisasi')
                            ->label('Total Realisasi')
                            ->money('Rp')
                            ->weight(FontWeight::Bold),

                        TextEntry::make('persentase_serapan')
                            ->label('Persentase Serapan')
                            ->formatStateUsing(fn($state) => $state . '%')
                            ->badge()
                            ->color(fn($state) => match (true) {
                                $state >= 80 => 'success',
                                $state >= 60 => 'warning',
                                default => 'danger',
                            }),

                        TextEntry::make('sub_kegiatans_count')
                            ->label('Jumlah Sub Kegiatan'),
                    ])
                    ->columns(2),

                Section::make('Informasi Sistem')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Dibuat pada')
                            ->dateTime('d F Y, H:i'),

                        TextEntry::make('updated_at')
                            ->label('Diperbarui pada')
                            ->dateTime('d F Y, H:i'),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }
}
