<?php

namespace App\Traits;

use App\Services\YearContext;
use Illuminate\Database\Eloquent\Builder;

trait HasYearFilter
{
    /**
     * Apply year filter to query
     */
    // public static function getEloquentQuery(): Builder
    // {
    //     $query = parent::getEloquentQuery();

    //     // Filter berdasarkan tahun aktif
    //     $activeYear = YearContext::getActiveYear();

    //     // Tentukan nama kolom tahun berdasarkan tabel
    //     $yearColumn = static::getYearColumn();

    //     return $query->where($yearColumn, $activeYear);
    // }

    /**
     * Get year column name for this resource
     * Override this method in each resource
     */
    // protected static function getYearColumn(): string
    // {
    //     return 'tahun'; // default column name
    // }

    // /**
    //  * Modify form data before saving
    //  */
    // protected function mutateFormDataBeforeCreate(array $data): array
    // {
    //     $data[static::getYearColumn()] = YearContext::getActiveYear();
    //     return $data;
    // }

    // /**
    //  * Modify form data before saving (for edit)
    //  */
    // protected function mutateFormDataBeforeSave(array $data): array
    // {
    //     $data[static::getYearColumn()] = YearContext::getActiveYear();
    //     return $data;
    // }
    // // protected function getTableQuery(): Builder
    // // {
    // //     return $this->getModel()::query()
    // //         ->where('tahun', YearContext::getActiveYear());
    // // }

    // public function getFilteredQuery(): Builder
    // {
    //     return $this->getModel()::query()
    //         ->where('tahun', YearContext::getActiveYear());
    // }

    // public function refreshForYearChange(): void
    // {
    //     $this->resetTable();

    //     // Refresh form dengan tahun baru jika ada
    //     if (method_exists($this, 'form')) {
    //         $this->form->fill(['tahun' => YearContext::getActiveYear()]);
    //     }
    // }
    /**
     * Apply year filter to query
     */
    public function scopeByYear($query, ?int $year = null)
    {
        $activeYear = $year ?? YearContext::getActiveYear();
        $yearColumn = static::getYearColumn();

        return $query->where($yearColumn, $activeYear);
    }

    /**
     * Get year column name for this resource
     * Override this method in each resource
     */
    protected static function getYearColumn(): string
    {
        return 'tahun'; // default column name
    }

    /**
     * Modify form data before saving
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data[static::getYearColumn()] = YearContext::getActiveYear();
        return $data;
    }

    /**
     * Modify form data before saving (for edit)
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data[static::getYearColumn()] = YearContext::getActiveYear();
        return $data;
    }

    /**
     * Get filtered query for current active year
     */
    public function getFilteredQuery(): Builder
    {
        return $this->getModel()::query()
            ->byYear(YearContext::getActiveYear());
    }

    /**
     * Refresh components when year changes
     */
    public function refreshForYearChange(): void
    {
        $this->resetTable();

        // Refresh form dengan tahun baru jika ada
        if (method_exists($this, 'form')) {
            $this->form->fill(['tahun' => YearContext::getActiveYear()]);
        }
    }
}
