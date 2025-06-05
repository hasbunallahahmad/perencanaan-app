<?php

namespace App\Services;

class YearContext
{
  public static function getActiveYear(): int
  {
    return session('active_year', date('Y'));
  }

  public static function setActiveYear(int $year): void
  {
    session(['active_year' => $year]);
  }

  public static function getAvailableYears(): array
  {
    return collect(range(2025, 2030))
      ->mapWithKeys(fn($year) => [$year => $year])
      ->toArray();
  }

  public static function getYearRange(): array
  {
    return range(2025, 2030);
  }

  public static function isValidYear(int $year): bool
  {
    return in_array($year, self::getYearRange());
  }
}
