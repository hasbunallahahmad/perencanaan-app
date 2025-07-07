<?php

namespace App\Services;

use App\Models\Organisasi;
use App\Models\Bidang;
use App\Models\Seksi;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CacheService
{
  const CACHE_TTL = 3600; // 1 hour
  const CACHE_PREFIX = 'app_cache_';
  const CACHE_VERSION = 'v1_';
  /**
   * Get active organisasi with caching
   */
  public static function getOrganisasiAktif(): array
  {
    $cacheKey = self::CACHE_PREFIX . 'organisasi_aktif';

    return Cache::remember($cacheKey, self::CACHE_TTL, function () {
      try {
        return Organisasi::where('aktif', true)
          ->orderBy('nama')
          ->pluck('nama', 'id')
          ->toArray();
      } catch (\Exception $e) {
        Log::error('Error getting organisasi aktif: ' . $e->getMessage());
        return [];
      }
    });
  }

  /**
   * Get bidang by organisasi with caching
   */
  public static function getBidangByOrganisasi(int $organisasiId): array
  {
    $cacheKey = self::CACHE_PREFIX . 'bidang_by_org_' . $organisasiId;

    return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($organisasiId) {
      try {
        return Bidang::where('organisasi_id', $organisasiId)
          ->where('aktif', true)
          ->orderBy('nama')
          ->pluck('nama', 'id')
          ->toArray();
      } catch (\Exception $e) {
        Log::error('Error getting bidang by organisasi: ' . $e->getMessage());
        return [];
      }
    });
  }

  /**
   * Get bidang detail with caching
   */
  public static function getBidangDetail(int $bidangId): ?object
  {
    $cacheKey = self::CACHE_PREFIX . 'bidang_detail_' . $bidangId;

    return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($bidangId) {
      try {
        $bidang = Bidang::select(['id', 'nama', 'organisasi_id', 'is_sekretariat'])
          ->find($bidangId);

        return $bidang ? (object) $bidang->toArray() : null;
      } catch (\Exception $e) {
        Log::error('Error getting bidang detail: ' . $e->getMessage());
        return null;
      }
    });
  }

  /**
   * Get bidang with organisasi for filters
   */
  public static function getBidangWithOrganisasi(): array
  {
    $cacheKey = self::CACHE_PREFIX . 'bidang_with_organisasi';

    return Cache::remember($cacheKey, self::CACHE_TTL, function () {
      try {
        return Bidang::with('organisasi:id,nama')
          ->where('aktif', true)
          ->get()
          ->mapWithKeys(function ($bidang) {
            $label = $bidang->nama;
            if ($bidang->organisasi) {
              $label .= ' (' . $bidang->organisasi->nama . ')';
            }
            return [$bidang->id => $label];
          })
          ->toArray();
      } catch (\Exception $e) {
        Log::error('Error getting bidang with organisasi: ' . $e->getMessage());
        return [];
      }
    });
  }

  /**
   * Get seksi by bidang with caching
   */
  public static function getSeksiByBidang(int $bidangId): array
  {
    $cacheKey = self::CACHE_PREFIX . self::CACHE_VERSION . 'seksi_by_bidang_' . $bidangId;

    return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($bidangId) {
      try {
        return Seksi::where('bidang_id', $bidangId)
          ->where('aktif', true)
          ->orderBy('nama')
          ->pluck('nama', 'id')
          ->toArray();
      } catch (\Exception $e) {
        Log::error('Error getting seksi by bidang: ' . $e->getMessage());
        return [];
      }
    });
  }

  public static function getRoles(): array
  {
    $cacheKey = self::CACHE_PREFIX . self::CACHE_VERSION . 'roles';

    return Cache::remember($cacheKey, self::CACHE_TTL, function () {
      try {
        return Role::orderBy('name')
          ->pluck('name', 'name')
          ->toArray();
      } catch (\Exception $e) {
        Log::error('Error getting roles: ' . $e->getMessage());
        return [];
      }
    });
  }
  public static function getPermissions(): array
  {
    $cacheKey = self::CACHE_PREFIX . self::CACHE_VERSION . 'permissions';

    return Cache::remember($cacheKey, self::CACHE_TTL, function () {
      try {
        return Permission::orderBy('name')
          ->pluck('name', 'name')
          ->toArray();
      } catch (\Exception $e) {
        Log::error('Error getting permissions: ' . $e->getMessage());
        return [];
      }
    });
  }
  public static function getRolesDetailed(): array
  {
    $cacheKey = self::CACHE_PREFIX . self::CACHE_VERSION . 'roles_detailed';

    return Cache::remember($cacheKey, self::CACHE_TTL, function () {
      try {
        return Role::orderBy('name')
          ->get(['id', 'name', 'guard_name'])
          ->toArray();
      } catch (\Exception $e) {
        Log::error('Error getting roles detailed: ' . $e->getMessage());
        return [];
      }
    });
  }
  public static function getPermissionsDetailed(): array
  {
    $cacheKey = self::CACHE_PREFIX . self::CACHE_VERSION . 'permissions_detailed';

    return Cache::remember($cacheKey, self::CACHE_TTL, function () {
      try {
        return Permission::orderBy('name')
          ->get(['id', 'name', 'guard_name'])
          ->toArray();
      } catch (\Exception $e) {
        Log::error('Error getting permissions detailed: ' . $e->getMessage());
        return [];
      }
    });
  }
  public static function getUserFormData(): array
  {
    $cacheKey = self::CACHE_PREFIX . self::CACHE_VERSION . 'user_form_data';

    return Cache::remember($cacheKey, self::CACHE_TTL, function () {
      try {
        return [
          'organisasi' => Organisasi::where('aktif', true)->orderBy('nama')->get(['id', 'nama']),
          'bidang' => Bidang::where('aktif', true)->orderBy('nama')->get(['id', 'nama', 'organisasi_id']),
          'seksi' => Seksi::where('aktif', true)->orderBy('nama')->get(['id', 'nama', 'bidang_id']),
          'roles' => Role::orderBy('name')->pluck('name', 'name')->toArray(),
          'permissions' => Permission::orderBy('name')->pluck('name', 'name')->toArray(),
        ];
      } catch (\Exception $e) {
        Log::error('Error getting user form data: ' . $e->getMessage());
        return [
          'organisasi' => collect(),
          'bidang' => collect(),
          'seksi' => collect(),
          'roles' => [],
          'permissions' => [],
        ];
      }
    });
  }
  /**
   * Get count with caching
   */
  public static function getCount(string $model): int
  {
    $cacheKey = self::CACHE_PREFIX . $model . '_count';

    return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($model) {
      try {
        switch ($model) {
          case 'seksi':
            return Seksi::count();
          case 'bidang':
            return Bidang::count();
          case 'organisasi':
            return Organisasi::count();
          default:
            return 0;
        }
      } catch (\Exception $e) {
        Log::error("Error getting {$model} count: " . $e->getMessage());
        return 0;
      }
    });
  }
  /**
   * Clear seksi-related caches when seksi is updated/deleted
   */
  public static function clearSeksiCaches(?int $bidangId, ?int $organisasiId = null): void
  {
    try {
      if ($bidangId) {
        // Clear seksi by bidang cache
        $seksiKey = self::CACHE_VERSION . 'seksi_by_bidang_' . $bidangId;
        Cache::forget(self::CACHE_PREFIX . $seksiKey);

        // Clear bidang detail cache
        $bidangDetailKey = 'bidang_detail_' . $bidangId;
        Cache::forget(self::CACHE_PREFIX . $bidangDetailKey);
      }

      if ($organisasiId) {
        // Clear bidang by organisasi cache
        $bidangKey = 'bidang_by_org_' . $organisasiId;
        Cache::forget(self::CACHE_PREFIX . $bidangKey);
      }

      // Clear general caches
      Cache::forget(self::CACHE_PREFIX . 'bidang_with_organisasi');
      Cache::forget(self::CACHE_PREFIX . self::CACHE_VERSION . 'user_form_data');

      // Clear count caches
      Cache::forget(self::CACHE_PREFIX . 'seksi_count');
      Cache::forget(self::CACHE_PREFIX . 'bidang_count');

      Log::info("Cleared seksi caches for bidang ID: {$bidangId}, organisasi ID: {$organisasiId}");
    } catch (\Exception $e) {
      Log::error('Error clearing seksi caches: ' . $e->getMessage());
    }
  }
  /**
   * Clear specific cache
   */
  public static function clearCache(string $key): void
  {
    Cache::forget(self::CACHE_PREFIX . $key);
  }

  /**
   * Clear all related caches
   */
  public static function clearAllCaches(): void
  {
    try {
      $patterns = [
        'organisasi_aktif',
        'bidang_with_organisasi',
        'seksi_count',
        'bidang_count',
        'organisasi_count'
      ];

      foreach ($patterns as $pattern) {
        Cache::forget(self::CACHE_PREFIX . $pattern);
      }

      // Clear dynamic caches
      Cache::flush(); // Use with caution in production

    } catch (\Exception $e) {
      Log::error('Error clearing all caches: ' . $e->getMessage());
    }
  }

  /**
   * Warm up frequently used caches
   */
  public static function warmUpCaches(): void
  {
    try {
      // Warm up organisasi cache
      self::getOrganisasiAktif();

      // Warm up bidang with organisasi cache
      self::getBidangWithOrganisasi();

      // Warm up count caches
      self::getCount('seksi');
      self::getCount('bidang');
      self::getCount('organisasi');
    } catch (\Exception $e) {
      Log::error('Error warming up caches: ' . $e->getMessage());
    }
  }

  /**
   * Check if cache exists
   */
  public static function hasCache(string $key): bool
  {
    return Cache::has(self::CACHE_PREFIX . $key);
  }

  /**
   * Get cache statistics
   */
  public static function getCacheStats(): array
  {
    try {
      $keys = [
        'organisasi_aktif',
        'bidang_with_organisasi',
        'seksi_count',
        'bidang_count',
        'organisasi_count'
      ];

      $stats = [];
      foreach ($keys as $key) {
        $fullKey = self::CACHE_PREFIX . $key;
        $stats[$key] = [
          'exists' => Cache::has($fullKey),
          'size' => Cache::has($fullKey) ? strlen(serialize(Cache::get($fullKey))) : 0
        ];
      }

      return $stats;
    } catch (\Exception $e) {
      Log::error('Error getting cache stats: ' . $e->getMessage());
      return [];
    }
  }
}
