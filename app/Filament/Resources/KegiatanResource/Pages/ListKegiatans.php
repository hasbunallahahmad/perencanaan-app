<?php

namespace App\Filament\Resources\KegiatanResource\Pages;

use App\Filament\Resources\KegiatanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListKegiatans extends ListRecords
{
    protected static string $resource = KegiatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua')
                ->badge(fn() => $this->getModel()::count()),

            'high_performance' => Tab::make('Serapan Tinggi (≥80%)')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereRaw('
                    (SELECT COALESCE(SUM(sa.realisasi), 0) / NULLIF(SUM(sa.anggaran), 0) * 100
                     FROM sub_kegiatan sk 
                     JOIN serapan_anggaran sa ON sk.id_sub_kegiatan = sa.id_sub_kegiatan 
                     WHERE sk.id_kegiatan = kegiatan.id_kegiatan) >= 80
                '))
                ->badge(fn() => $this->getModel()::whereRaw('
                    (SELECT COALESCE(SUM(sa.realisasi), 0) / NULLIF(SUM(sa.anggaran), 0) * 100
                     FROM sub_kegiatan sk 
                     JOIN serapan_anggaran sa ON sk.id_sub_kegiatan = sa.id_sub_kegiatan 
                     WHERE sk.id_kegiatan = kegiatan.id_kegiatan) >= 80
                ')->count()),

            'medium_performance' => Tab::make('Serapan Sedang (60-79%)')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereRaw('
                    (SELECT COALESCE(SUM(sa.realisasi), 0) / NULLIF(SUM(sa.anggaran), 0) * 100
                     FROM sub_kegiatan sk 
                     JOIN serapan_anggaran sa ON sk.id_sub_kegiatan = sa.id_sub_kegiatan 
                     WHERE sk.id_kegiatan = kegiatan.id_kegiatan) BETWEEN 60 AND 79
                '))
                ->badge(fn() => $this->getModel()::whereRaw('
                    (SELECT COALESCE(SUM(sa.realisasi), 0) / NULLIF(SUM(sa.anggaran), 0) * 100
                     FROM sub_kegiatan sk 
                     JOIN serapan_anggaran sa ON sk.id_sub_kegiatan = sa.id_sub_kegiatan 
                     WHERE sk.id_kegiatan = kegiatan.id_kegiatan) BETWEEN 60 AND 79
                ')->count()),

            'low_performance' => Tab::make('Serapan Rendah (<60%)')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereRaw('
                    (SELECT COALESCE(SUM(sa.realisasi), 0) / NULLIF(SUM(sa.anggaran), 0) * 100
                     FROM sub_kegiatan sk 
                     JOIN serapan_anggaran sa ON sk.id_sub_kegiatan = sa.id_sub_kegiatan 
                     WHERE sk.id_kegiatan = kegiatan.id_kegiatan) < 60
                '))
                ->badge(fn() => $this->getModel()::whereRaw('
                    (SELECT COALESCE(SUM(sa.realisasi), 0) / NULLIF(SUM(sa.anggaran), 0) * 100
                     FROM sub_kegiatan sk 
                     JOIN serapan_anggaran sa ON sk.id_sub_kegiatan = sa.id_sub_kegiatan 
                     WHERE sk.id_kegiatan = kegiatan.id_kegiatan) < 60
                ')->count()),
        ];
    }
}
