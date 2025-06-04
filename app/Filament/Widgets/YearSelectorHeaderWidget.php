<?php

namespace App\Filament\Widgets;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Enums\MaxWidth;
use Filament\Widgets\Widget;

class YearSelectorHeaderWidget extends Widget implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;

    protected static string $view = 'filament.widgets.year-selector-header-widget';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = -10;

    public function yearSelectorAction(): Action
    {
        return Action::make('yearSelector')
            ->label('Ganti Tahun: ' . session('selected_year', now()->year))
            ->icon('heroicon-o-calendar')
            ->color('primary')
            ->size('lg')
            ->form([
                Select::make('year')
                    ->label('Pilih Tahun')
                    ->options([
                        2020 => 'Tahun 2020',
                        2021 => 'Tahun 2021',
                        2022 => 'Tahun 2022',
                        2023 => 'Tahun 2023',
                        2024 => 'Tahun 2024',
                        2025 => 'Tahun 2025',
                        2026 => 'Tahun 2026',
                        2027 => 'Tahun 2027',
                        2028 => 'Tahun 2028',
                        2029 => 'Tahun 2029',
                        2030 => 'Tahun 2030',
                    ])
                    ->default(session('selected_year', now()->year))
                    ->required()
                    ->native(false)
            ])
            ->action(function (array $data): void {
                session(['selected_year' => $data['year']]);

                // Refresh halaman
                $this->redirect(request()->url(), navigate: false);
            })
            ->modalHeading('Pilih Tahun')
            ->modalDescription('Pilih tahun untuk memfilter data')
            ->modalSubmitActionLabel('Simpan')
            ->modalCancelActionLabel(label: 'Batal')
            ->modalWidth(MaxWidth::Medium)
            ->closeModalByClickingAway(false);
    }

    public static function canView(): bool
    {
        return true;
    }
}
