<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Services\YearContext;
use Filament\Notifications\Notification;

class YearSelectorWidget extends Widget
{
    protected static string $view = 'filament.widgets.year-selector';
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = -10;

    public $selectedYear;

    public function mount()
    {
        $this->selectedYear = YearContext::getActiveYear();
    }

    public function updatedSelectedYear()
    {
        if (YearContext::isValidYear($this->selectedYear)) {
            YearContext::setActiveYear($this->selectedYear);

            // Tampilkan notifikasi jika tahun dipilih tidak memiliki data
            if (!YearContext::hasDataForYear($this->selectedYear)) {
                Notification::make()
                    ->title('Info')
                    ->body("Tahun {$this->selectedYear} belum memiliki data. Silakan tambahkan data baru.")
                    ->info()
                    ->send();
            }

            return redirect(request()->header('Referer'));
        }
    }

    public function getAvailableYears(): array
    {
        return YearContext::getAvailableYears();
    }

    public function getYearsWithData(): array
    {
        return YearContext::getYearsWithData();
    }

    public function hasDataForYear($year): bool
    {
        return YearContext::hasDataForYear($year);
    }
}
