<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class YearContext
{
  private static string $activeYearKey = 'app.active_tahun';
  private static string $cacheKey = 'year_context_data';
  private static int $cacheTime = 3600;

  public static function getActiveYear(): int
  {
    return session(self::$activeYearKey, date('Y'));
  }

  public static function setActiveYear(int $year): void
  {
    if (self::isValidYear($year)) {
      session([self::$activeYearKey => $year]);
      Log::info('Year context changed', [
        'old_year' => session(self::$activeYearKey),
        'new_year' => $year,
        'user_id' => Auth::user()->id,
        'timestamp' => now()
      ]);
    }
  }

  public static function isValidYear(int $year): bool
  {
    return $year >= 2025 && $year <= (date('Y') + 5);
  }

  public static function getAvailableYears(): array
  {
    return Cache::remember(self::$cacheKey . '_available_years', self::$cacheTime, function () {
      $currentYear = date('Y');
      $years = [];

      for ($year = 2025; $year <= ($currentYear + 5); $year++) {
        $years[] = $year;
      }

      return $years;
    });
  }
  public static function getYearsWithData(): array
  {
    return Cache::remember(self::$cacheKey . '_years_with_data', self::$cacheTime, function () {
      $tables = [
        'program' => 'tahun',
        'kegiatan' => 'tahun',
        'sub_kegiatan' => 'tahun',
        'tujas' => 'tahun',
        'capaian_kinerja_program' => 'tahun',
        'capaian_kinerja' => 'tahun',
        'capaian_kinerja_kegiatan' => 'tahun',
      ];

      $yearsWithData = [];

      foreach ($tables as $table => $yearColumn) {
        try {
          if (
            DB::getSchemaBuilder()->hasTable($table) &&
            DB::getSchemaBuilder()->hasColumn($table, $yearColumn)
          ) {
            $years = DB::table($table)
              ->select($yearColumn)
              ->distinct()
              ->whereNotNull($yearColumn)
              ->pluck($yearColumn)
              ->toArray();

            $yearsWithData = array_merge($yearsWithData, $years);
          }
        } catch (\Exception $e) {
          Log::warning("Error checking table {$table}: " . $e->getMessage());
          continue;
        }
      }

      return array_unique(array_filter($yearsWithData));
    });
  }
  public static function hasDataForYear(int $year): bool
  {
    return in_array($year, self::getYearsWithData());
  }
  public static function getYearStatistics(): array
  {
    return Cache::remember(self::$cacheKey . '_statistics', self::$cacheTime, function () {
      $yearsWithData = self::getYearsWithData();
      $statistics = [];

      foreach ($yearsWithData as $year) {
        $statistics[$year] = [
          'year' => $year,
          'has_program' => self::hasDataInTable('program', $year),
          'has_kegiatan' => self::hasDataInTable('kegiatan', $year),
          'has_capaian' => self::hasDataInTable('capaian_kinerja_program', $year),
          'total_programs' => self::countDataInTable('program', $year),
          'total_kegiatan' => self::countDataInTable('kegiatan', $year),
        ];
      }

      return $statistics;
    });
  }
  private static function hasDataInTable(string $table, int $year): bool
  {
    try {
      if (!DB::getSchemaBuilder()->hasTable($table)) {
        return false;
      }

      return DB::table($table)
        ->where('tahun', $year)
        ->exists();
    } catch (\Exception $e) {
      Log::warning("Error checking data in table {$table} for year {$year}: " . $e->getMessage());
      return false;
    }
  }
  private static function countDataInTable(string $table, int $year): int
  {
    try {
      if (!DB::getSchemaBuilder()->hasTable($table)) {
        return 0;
      }

      return DB::table($table)
        ->where('tahun', $year)
        ->count();
    } catch (\Exception $e) {
      Log::warning("Error counting data in table {$table} for year {$year}: " . $e->getMessage());
      return 0;
    }
  }
  public static function clearCache(): void
  {
    Cache::forget(self::$cacheKey . '_available_years');
    Cache::forget(self::$cacheKey . '_years_with_data');
    Cache::forget(self::$cacheKey . '_statistics');

    Log::info('Year context cache cleared');
  }
  public static function getNextYearWithData(int $currentYear): ?int
  {
    $yearsWithData = self::getYearsWithData();
    sort($yearsWithData);

    foreach ($yearsWithData as $year) {
      if ($year > $currentYear) {
        return $year;
      }
    }

    return null;
  }
  public static function getPreviousYearWithData(int $currentYear): ?int
  {
    $yearsWithData = self::getYearsWithData();
    rsort($yearsWithData); // Sort descending

    foreach ($yearsWithData as $year) {
      if ($year < $currentYear) {
        return $year;
      }
    }

    return null;
  }
  public static function getFirstYearWithData(): ?int
  {
    $yearsWithData = self::getYearsWithData();

    if (empty($yearsWithData)) {
      return null;
    }

    sort($yearsWithData);
    return $yearsWithData[0];
  }
  public static function getLastYearWithData(): ?int
  {
    $yearsWithData = self::getYearsWithData();

    if (empty($yearsWithData)) {
      return null;
    }

    rsort($yearsWithData);
    return $yearsWithData[0];
  }
  public static function switchToNextYear(): bool
  {
    $currentYear = self::getActiveYear();
    $nextYear = self::getNextYearWithData($currentYear);

    if ($nextYear) {
      self::setActiveYear($nextYear);
      return true;
    }

    return false;
  }
  public static function switchToPreviousYear(): bool
  {
    $currentYear = self::getActiveYear();
    $previousYear = self::getPreviousYearWithData($currentYear);

    if ($previousYear) {
      self::setActiveYear($previousYear);
      return true;
    }

    return false;
  }
  public static function getContextSummary(): array
  {
    $activeYear = self::getActiveYear();
    $yearsWithData = self::getYearsWithData();
    $statistics = self::getYearStatistics();

    return [
      'active_year' => $activeYear,
      'has_data_for_active_year' => self::hasDataForYear($activeYear),
      'available_years' => self::getAvailableYears(),
      'years_with_data' => $yearsWithData,
      'first_year_with_data' => self::getFirstYearWithData(),
      'last_year_with_data' => self::getLastYearWithData(),
      'next_year_with_data' => self::getNextYearWithData($activeYear),
      'previous_year_with_data' => self::getPreviousYearWithData($activeYear),
      'statistics' => $statistics[$activeYear] ?? null,
      'total_years_with_data' => count($yearsWithData),
    ];
  }
  public static function setYearWithFallback(int $year): int
  {
    // Jika tahun valid dan memiliki data, set sebagai active year
    if (self::isValidYear($year) && self::hasDataForYear($year)) {
      self::setActiveYear($year);
      return $year;
    }

    // Jika tidak, cari tahun terdekat yang memiliki data
    $yearsWithData = self::getYearsWithData();

    if (empty($yearsWithData)) {
      // Jika tidak ada data sama sekali, gunakan tahun sekarang
      $fallbackYear = date('Y');
      self::setActiveYear($fallbackYear);
      return $fallbackYear;
    }

    // Cari tahun terdekat
    $closestYear = null;
    $minDifference = PHP_INT_MAX;

    foreach ($yearsWithData as $availableYear) {
      $difference = abs($year - $availableYear);
      if ($difference < $minDifference) {
        $minDifference = $difference;
        $closestYear = $availableYear;
      }
    }

    if ($closestYear) {
      self::setActiveYear($closestYear);
      return $closestYear;
    }

    // Fallback terakhir
    $fallbackYear = date('Y');
    self::setActiveYear($fallbackYear);
    return $fallbackYear;
  }
}
