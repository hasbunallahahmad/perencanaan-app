<?php

namespace App\Filament\Resources\KegiatanResource\Pages;

use App\Filament\Resources\KegiatanResource;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListKegiatans extends ListRecords
{
    protected static string $resource = KegiatanResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Kegiatan')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->tooltip('Tambah Kegiatan baru'),
        ];
    }
    public function table(Table $table): Table
    {
        return $this->getResource()::table($table)
            ->emptyStateHeading('Belum Ada Data Kegiatan')
            ->emptyStateDescription('Mulai dengan menambahkan kegiatan organisasi Anda.')
            ->emptyStateIcon('heroicon-o-clipboard-document-list')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambahkan Kegiatan Sesuai Nomenklatur Organisasi')
                    ->icon('heroicon-o-plus-circle')
                    ->button()
                    ->color('primary'),
            ]);
    }
}
