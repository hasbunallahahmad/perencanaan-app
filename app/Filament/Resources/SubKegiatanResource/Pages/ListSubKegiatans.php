<?php

namespace App\Filament\Resources\SubKegiatanResource\Pages;

use App\Filament\Resources\SubKegiatanResource;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Pages\ListRecords;
use Filament\Pages\Concerns\ExposesTableToWidgets;

class ListSubKegiatans extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = SubKegiatanResource::class;

    public function table(Table $table): Table
    {
        return $this->getResource()::table($table)
            ->emptyStateHeading('Belum Ada Data Sub Kegiatan')
            ->emptyStateDescription('Mulai dengan menambahkan Sub kegiatan organisasi Anda.')
            ->emptyStateIcon('heroicon-o-clipboard-document-list')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambahkan Sub Kegiatan Sesuai Nomenklatur Organisasi')
                    ->icon('heroicon-o-plus-circle')
                    ->button()
                    ->color('primary'),
            ]);
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Sub Kegiatan')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->tooltip('Tambah Sub Kegiatan baru'),
        ];
    }
    protected function getHeaderWidgets(): array
    {
        return SubKegiatanResource::getWidgets();
    }
}
