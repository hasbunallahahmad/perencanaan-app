<?php

namespace App\Filament\Resources\RealisasiTujuanSasaranResource\Pages;

use App\Filament\Resources\RealisasiTujuanSasaranResource;
use App\Services\YearContext;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Enums\IconPosition;

class ListRealisasiTujuanSasarans extends ListRecords
{
    protected static string $resource = RealisasiTujuanSasaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Realisasi')
                ->icon('heroicon-o-plus-circle'),

            Actions\Action::make('year_context')
                ->label('Tahun: ' . YearContext::getActiveYear())
                ->icon('heroicon-o-calendar-days')
                ->iconPosition(IconPosition::Before)
                ->color('info')
                ->action(function (array $data): void {
                    YearContext::setActiveYear($data['year']);
                    $this->redirect(request()->header('Referer'));
                })
                ->form([
                    \Filament\Forms\Components\Select::make('year')
                        ->label('Pilih Tahun')
                        ->options(collect(YearContext::getAvailableYears())->mapWithKeys(fn($year) => [$year => $year]))
                        ->default(YearContext::getActiveYear())
                        ->required(),
                ])
                ->modalHeading('Ganti Tahun Aktif')
                ->modalSubmitActionLabel('Ganti Tahun'),
        ];
    }

    public function getTabs(): array
    {
        $activeYear = YearContext::getActiveYear();

        return [
            'all' => Tab::make('Semua Data')
                // ->icon('heroicon-o-list-bullet')
                ->badge(fn() => $this->getModel()::byYear($activeYear)->count())
                ->badgeColor('primary'),

            'tujuan' => Tab::make('Tujuan')
                // ->icon('heroicon-o-target')
                ->modifyQueryUsing(fn(Builder $query) => $query->tujuanOnly())
                ->badge(fn() => $this->getModel()::byYear($activeYear)->tujuanOnly()->count())
                ->badgeColor('success'),

            'sasaran' => Tab::make('Sasaran')
                // ->icon('heroicon-o-flag')
                ->modifyQueryUsing(fn(Builder $query) => $query->sasaranOnly())
                ->badge(fn() => $this->getModel()::byYear($activeYear)->sasaranOnly()->count())
                ->badgeColor('info'),

            'verified' => Tab::make('Terverifikasi')
                // ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(function (Builder $query) {
                    return $query->where(function ($q) {
                        $q->where('verifikasi_tw1', true)
                            ->orWhere('verifikasi_tw2', true)
                            ->orWhere('verifikasi_tw3', true)
                            ->orWhere('verifikasi_tw4', true);
                    });
                })
                ->badge(function () use ($activeYear) {
                    return $this->getModel()::byYear($activeYear)
                        ->where(function ($q) {
                            $q->where('verifikasi_tw1', true)
                                ->orWhere('verifikasi_tw2', true)
                                ->orWhere('verifikasi_tw3', true)
                                ->orWhere('verifikasi_tw4', true);
                        })->count();
                })
                ->badgeColor('warning'),

            'draft' => Tab::make('Draft')
                // ->icon('heroicon-o-document')
                ->modifyQueryUsing(function (Builder $query) {
                    return $query->where(function ($q) {
                        $q->where('status_tw1', 'draft')
                            ->orWhere('status_tw2', 'draft')
                            ->orWhere('status_tw3', 'draft')
                            ->orWhere('status_tw4', 'draft');
                    });
                })
                ->badge(function () use ($activeYear) {
                    return $this->getModel()::byYear($activeYear)
                        ->where(function ($q) {
                            $q->where('status_tw1', 'draft')
                                ->orWhere('status_tw2', 'draft')
                                ->orWhere('status_tw3', 'draft')
                                ->orWhere('status_tw4', 'draft');
                        })->count();
                })
                ->badgeColor('gray'),
        ];
    }

    // protected function getHeaderWidgets(): array
    // {
    //     return [
    //         RealisasiTujuanSasaranResource\Widgets\RealisasiStatsWidget::class,
    //     ];
    // }

    public function getTitle(): string
    {
        return 'Realisasi Tujuan & Sasaran - Tahun ' . YearContext::getActiveYear();
    }
}
