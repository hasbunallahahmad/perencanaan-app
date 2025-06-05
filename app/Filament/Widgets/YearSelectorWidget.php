<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Services\YearContext;

class YearSelectorWidget extends Widget
{
    protected static string $view = 'filament.widgets.year-selector';
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = -10; // Tampil di atas

    public $selectedYear;

    public function mount()
    {
        $this->selectedYear = YearContext::getActiveYear();
    }

    public function updatedSelectedYear()
    {
        if (YearContext::isValidYear($this->selectedYear)) {
            YearContext::setActiveYear($this->selectedYear);
            return redirect(request()->header('Referer'));
        }
    }

    public function getAvailableYears(): array
    {
        return YearContext::getAvailableYears();
    }
}
