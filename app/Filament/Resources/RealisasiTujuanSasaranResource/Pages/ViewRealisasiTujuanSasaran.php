<?php

namespace App\Filament\Resources\RealisasiTujuanSasaranResource\Pages;

use App\Filament\Resources\RealisasiTujuanSasaranResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Tabs;

class ViewRealisasiTujuanSasaran extends ViewRecord
{
    protected static string $resource = RealisasiTujuanSasaranResource::class;

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
                Section::make('Informasi Umum')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('tahun')
                                    ->label('Tahun')
                                    ->badge()
                                    ->color('primary'),

                                TextEntry::make('tipe')
                                    ->label('Tipe')
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                                        'tujuan' => 'success',
                                        'sasaran' => 'info',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn(string $state): string => ucfirst($state)),

                                TextEntry::make('target_tahun')
                                    ->label('Target Tahun')
                                    ->numeric(2)
                                    ->suffix('%'),
                            ]),

                        TextEntry::make('nama')
                            ->label('Nama Tujuan/Sasaran'),

                        TextEntry::make('indikator')
                            ->label('Indikator'),

                        TextEntry::make('satuan')
                            ->label('Satuan'),
                    ])
                    ->columns(2),

                Tabs::make('Realisasi Per Triwulan')
                    ->tabs([
                        Tabs\Tab::make('Triwulan 1')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('realisasi_tw1')
                                            ->label('Realisasi')
                                            ->numeric(2)
                                            ->suffix('%')
                                            ->color(fn($state) => $state > 0 ? 'success' : 'gray'),

                                        TextEntry::make('status_tw1')
                                            ->label('Status')
                                            ->badge()
                                            ->color(fn(string $state): string => match ($state) {
                                                'draft' => 'gray',
                                                'submitted' => 'info',
                                                'verified' => 'success',
                                                'rejected' => 'danger',
                                                default => 'gray',
                                            }),

                                        TextEntry::make('verifikasi_tw1')
                                            ->label('Verifikasi')
                                            ->formatStateUsing(fn($state) => $state ? 'Sudah Diverifikasi' : 'Belum Diverifikasi')
                                            ->badge()
                                            ->color(fn($state) => $state ? 'success' : 'warning'),

                                        TextEntry::make('dokumen_tw1')
                                            ->label('Dokumen')
                                            ->formatStateUsing(function ($state) {
                                                if (empty($state)) return 'Tidak ada dokumen';
                                                return count($state) . ' file dokumen';
                                            }),
                                    ]),
                            ]),

                        Tabs\Tab::make('Triwulan 2')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('realisasi_tw2')
                                            ->label('Realisasi')
                                            ->numeric(2)
                                            ->suffix('%')
                                            ->color(fn($state) => $state > 0 ? 'success' : 'gray'),

                                        TextEntry::make('status_tw2')
                                            ->label('Status')
                                            ->badge()
                                            ->color(fn(string $state): string => match ($state) {
                                                'draft' => 'gray',
                                                'submitted' => 'info',
                                                'verified' => 'success',
                                                'rejected' => 'danger',
                                                default => 'gray',
                                            }),

                                        TextEntry::make('verifikasi_tw2')
                                            ->label('Verifikasi')
                                            ->formatStateUsing(fn($state) => $state ? 'Sudah Diverifikasi' : 'Belum Diverifikasi')
                                            ->badge()
                                            ->color(fn($state) => $state ? 'success' : 'warning'),

                                        TextEntry::make('dokumen_tw2')
                                            ->label('Dokumen')
                                            ->formatStateUsing(function ($state) {
                                                if (empty($state)) return 'Tidak ada dokumen';
                                                return count($state) . ' file dokumen';
                                            }),
                                    ]),
                            ]),

                        Tabs\Tab::make('Triwulan 3')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('realisasi_tw3')
                                            ->label('Realisasi')
                                            ->numeric(2)
                                            ->suffix('%')
                                            ->color(fn($state) => $state > 0 ? 'success' : 'gray'),

                                        TextEntry::make('status_tw3')
                                            ->label('Status')
                                            ->badge()
                                            ->color(fn(string $state): string => match ($state) {
                                                'draft' => 'gray',
                                                'submitted' => 'info',
                                                'verified' => 'success',
                                                'rejected' => 'danger',
                                                default => 'gray',
                                            }),

                                        TextEntry::make('verifikasi_tw3')
                                            ->label('Verifikasi')
                                            ->formatStateUsing(fn($state) => $state ? 'Sudah Diverifikasi' : 'Belum Diverifikasi')
                                            ->badge()
                                            ->color(fn($state) => $state ? 'success' : 'warning'),

                                        TextEntry::make('dokumen_tw3')
                                            ->label('Dokumen')
                                            ->formatStateUsing(function ($state) {
                                                if (empty($state)) return 'Tidak ada dokumen';
                                                return count($state) . ' file dokumen';
                                            }),
                                    ]),
                            ]),

                        Tabs\Tab::make('Triwulan 4')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('realisasi_tw4')
                                            ->label('Realisasi')
                                            ->numeric(2)
                                            ->suffix('%')
                                            ->color(fn($state) => $state > 0 ? 'success' : 'gray'),

                                        TextEntry::make('status_tw4')
                                            ->label('Status')
                                            ->badge()
                                            ->color(fn(string $state): string => match ($state) {
                                                'draft' => 'gray',
                                                'submitted' => 'info',
                                                'verified' => 'success',
                                                'rejected' => 'danger',
                                                default => 'gray',
                                            }),

                                        TextEntry::make('verifikasi_tw4')
                                            ->label('Verifikasi')
                                            ->formatStateUsing(fn($state) => $state ? 'Sudah Diverifikasi' : 'Belum Diverifikasi')
                                            ->badge()
                                            ->color(fn($state) => $state ? 'success' : 'warning'),

                                        TextEntry::make('dokumen_tw4')
                                            ->label('Dokumen')
                                            ->formatStateUsing(function ($state) {
                                                if (empty($state)) return 'Tidak ada dokumen';
                                                return count($state) . ' file dokumen';
                                            }),
                                    ]),
                            ]),
                    ]),

                Section::make('Ringkasan Pencapaian')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('total_realisasi')
                                    ->label('Total Realisasi')
                                    ->numeric(2)
                                    ->suffix('%')
                                    ->color('primary'),

                                TextEntry::make('persentase_pencapaian')
                                    ->label('Pencapaian')
                                    ->numeric(2)
                                    ->suffix('%')
                                    ->badge()
                                    ->color(function ($state) {
                                        if ($state >= 100) return 'success';
                                        if ($state >= 75) return 'warning';
                                        if ($state >= 50) return 'info';
                                        return 'danger';
                                    }),

                                TextEntry::make('created_at')
                                    ->label('Dibuat Pada')
                                    ->dateTime('d/m/Y H:i'),
                            ]),
                    ]),

                Section::make('Informasi Audit')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('creator.name')
                                    ->label('Dibuat Oleh'),

                                TextEntry::make('updater.name')
                                    ->label('Diperbarui Oleh'),

                                TextEntry::make('created_at')
                                    ->label('Tanggal Dibuat')
                                    ->dateTime('d/m/Y H:i'),

                                TextEntry::make('updated_at')
                                    ->label('Tanggal Diperbarui')
                                    ->dateTime('d/m/Y H:i'),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }

    public function getTitle(): string
    {
        return 'Detail Realisasi - ' . $this->record->nama;
    }
}
