<?php

namespace App\Filament\Resources\TujuanSasaranResource\Pages;

use App\Filament\Resources\TujuanSasaranResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class ListTujuanSasarans extends ListRecords
{
    protected static string $resource = TujuanSasaranResource::class;
    public function table(Table $table): Table
    {
        return $this->getResource()::table($table)
            ->emptyStateHeading('Belum Ada Data Tujuan & Sasaran')
            ->emptyStateDescription('Mulai dengan menambahkan tujuan dan sasaran organisasi Anda. Data ini akan menjadi dasar untuk perencanaan dan monitoring kinerja.')
            ->emptyStateIcon('heroicon-o-clipboard-document-list')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Buat Tujuan & Sasaran Pertama')
                    ->icon('heroicon-o-plus-circle')
                    ->button()
                    ->color('primary'),
            ]);
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah  Tujuan & Sasaran')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->tooltip('Tambah tujuan & sasaran baru'),
        ];
    }
}
