<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PerformanceOptimizationService
{
  /**
   * Cache duration constants
   */
  const CACHE_SHORT = 300; // 5 minutes
  const CACHE_MEDIUM = 1800; // 30 minutes
  const CACHE_LONG = 3600; // 1 hour
  const CACHE_VERY_LONG = 86400; // 24 hours

  /**
   * Get cached organisasi options for dropdowns
   */
  public static function getCachedOrganisasiOptions(): array
  {
    return Cache::remember('organisasi_dropdown_options', self::CACHE_LONG, function () {
      return DB::table('organisasis')
        ->where('aktif', true)
        ->orderBy('nama')
        ->pluck('nama', 'id')
        ->toArray();
    });
  }

  /**
   * Get cached user permissions to reduce role/permission queries
   */
  // public static function getCachedUserPermissions(int $userId): array
  // {
  //   return Cache::remember("user_permissions_{$userId}", self::CACHE_MEDIUM, function () use ($userId) {
  //     return DB::table('model_has_permissions')
  //       ->join('permissions', 'permissions.id', '=', 'model_has_permissions.permission_id')
  //       ->where('model_id', $userId)
  //       ->where('model_type', 'App\\Models\\User')
  //       ->pluck('permissions.name')
  //       ->toArray();
  //   });
  // }

  /**
   * Get cached user roles to reduce role queries
   */
  // public static function getCachedUserRoles(int $userId): array
  // {
  //   return Cache::remember("user_roles_{$userId}", self::CACHE_MEDIUM, function () use ($userId) {
  //     return DB::table('model_has_roles')
  //       ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
  //       ->where('model_id', $userId)
  //       ->where('model_type', 'App\\Models\\User')
  //       ->pluck('roles.name')
  //       ->toArray();
  //   });
  // }

  /**
   * Batch clear related caches
   */
  public static function clearRelatedCaches(string $prefix): void
  {
    $keys = [
      "{$prefix}_count",
      "{$prefix}_options",
      "{$prefix}_dropdown_options",
      "unique_active_{$prefix}",
    ];

    foreach ($keys as $key) {
      Cache::forget($key);
    }
  }

  /**
   * Optimize database queries by adding missing indexes
   */
  public static function getRecommendedIndexes(): array
  {
    return [
      'bidangs' => [
        ['columns' => ['aktif'], 'name' => 'idx_bidangs_aktif'],
        ['columns' => ['organisasi_id', 'aktif'], 'name' => 'idx_bidangs_org_aktif'],
        ['columns' => ['is_sekretariat'], 'name' => 'idx_bidangs_sekretariat'],
        ['columns' => ['nama'], 'name' => 'idx_bidangs_nama'],
      ],
      'organisasis' => [
        ['columns' => ['aktif'], 'name' => 'idx_organisasis_aktif'],
        ['columns' => ['nama'], 'name' => 'idx_organisasis_nama'],
      ],
      'model_has_permissions' => [
        ['columns' => ['model_id', 'model_type'], 'name' => 'idx_model_permissions'],
      ],
      'model_has_roles' => [
        ['columns' => ['model_id', 'model_type'], 'name' => 'idx_model_roles'],
      ],
    ];
  }

  /**
   * Warm up frequently used caches
   */
  public static function warmUpCaches(): void
  {
    // Warm up organisasi options
    self::getCachedOrganisasiOptions();

    // Warm up other frequently accessed data
    Cache::remember('active_bidang_count', self::CACHE_MEDIUM, function () {
      return DB::table('bidangs')->where('aktif', true)->count();
    });

    Cache::remember('active_organisasi_count', self::CACHE_MEDIUM, function () {
      return DB::table('organisasis')->where('aktif', true)->count();
    });
  }
}
