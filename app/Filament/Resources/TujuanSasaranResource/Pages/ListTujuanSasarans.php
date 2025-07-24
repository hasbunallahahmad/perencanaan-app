<?php

namespace App\Filament\Resources\TujuanSasaranResource\Pages;

use App\Filament\Resources\TujuanSasaranResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListTujuanSasarans extends ListRecords
{
    protected static string $resource = TujuanSasaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Tujuan & Sasaran'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua')
                ->badge($this->getModel()::count()),

            'current_year' => Tab::make('Tahun Ini')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('tahun', date('Y')))
                ->badge($this->getModel()::where('tahun', date('Y'))->count()),

            'tercapai' => Tab::make('Tercapai')
                ->modifyQueryUsing(fn(Builder $query) => $query->highAchievement(100))
                ->badge($this->getModel()::highAchievement(100)->count()),

            'baik' => Tab::make('Baik')
                ->modifyQueryUsing(fn(Builder $query) => $query->achievementBetween(75, 99.99))
                ->badge($this->getModel()::achievementBetween(75, 99.99)->count()),

            'cukup' => Tab::make('Cukup')
                ->modifyQueryUsing(fn(Builder $query) => $query->achievementBetween(50, 74.99))
                ->badge($this->getModel()::achievementBetween(50, 74.99)->count()),

            'kurang' => Tab::make('Kurang')
                ->modifyQueryUsing(fn(Builder $query) => $query->lowAchievement(50))
                ->badge($this->getModel()::lowAchievement(50)->count()),
        ];
    }
}
