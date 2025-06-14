<?php

namespace App\Filament\Resources\RencanaAksiResource\Pages;

use App\Filament\Resources\RencanaAksiResource;
use App\Services\YearContext;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListRencanaAksis extends ListRecords
{
    protected static string $resource = RencanaAksiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Rencana Aksi')
                ->icon('heroicon-o-plus'),
        ];
    }

    // public function getTabs(): array
    // {
    //     return [
    //         'all' => Tab::make('Semua')
    //             ->badge($this->getModel()::count()),

    //         'apbd' => Tab::make('APBD')
    //             ->modifyQueryUsing(fn(Builder $query) => $query->whereJsonContains('jenis_anggaran', 'APBD'))
    //             ->badge($this->getModel()::whereJsonContains('jenis_anggaran', 'APBD')->count()),

    //         'dak' => Tab::make('DAK')
    //             ->modifyQueryUsing(fn(Builder $query) => $query->whereJsonContains('jenis_anggaran', 'DAK'))
    //             ->badge($this->getModel()::whereJsonContains('jenis_anggaran', 'DAK')->count()),

    //         'recent' => Tab::make('Terbaru')
    //             ->modifyQueryUsing(fn(Builder $query) => $query->where('created_at', '>=', now()->subDays(7)))
    //             ->badge($this->getModel()::where('created_at', '>=', now()->subDays(7))->count()),
    //     ];
    // }

    protected function getHeaderWidgets(): array
    {
        return [
            // You can add widgets here if needed
        ];
    }

    public function getTitle(): string
    {
        return 'Rencana Aksi - Tahun ' . YearContext::getActiveYear();
    }
}
