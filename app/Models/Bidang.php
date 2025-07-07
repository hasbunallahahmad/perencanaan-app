<?php

namespace App\Models;

use App\Services\CacheService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Bidang extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'kode',
        'deskripsi',
        'organisasi_id',
        'is_sekretariat',
        'aktif'
    ];
    protected $casts = [
        'is_sekretariat' => 'boolean',
        'aktif' => 'boolean'
    ];
    protected $attributes = [
        'aktif' => true,
        'is_sekretariat' => false,
    ];
    public function organisasi(): BelongsTo
    {
        return $this->belongsTo(Organisasi::class)
            ->select(['id', 'nama', 'aktif']);
    }
    public function seksis(): HasMany
    {
        return $this->hasMany(Seksi::class);
    }
    public function subbagians(): HasMany
    {
        return $this->hasMany(Seksi::class)->where('jenis', 'subbagian');
    }
    public function programs()
    {
        return $this->hasMany(Program::class, 'bidang_id', 'id');
    }
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
    // Scopes
    public function scopeActive($query)
    {
        return $query->where('aktif', true);
    }
    public function scopeSekretariat($query)
    {
        return $query->where('is_sekretariat', true);
    }
    public function scopeSeksi($query)
    {
        return $query->where('is_sekretariat', false);
    }
    public static function getUniqueActive()
    {
        return CacheService::getBidangWithOrganisasi();
    }
    public function getJenisUnitAttribute()
    {
        return $this->is_sekretariat ? 'Subbagian' : 'Seksi';
    }
    // protected static function boot(): void
    // {
    //     parent::boot();

    //     static::saved(function ($bidang) {
    //         CacheService::clearSeksiCaches($bidang->id, $bidang->organisasi_id);
    //     });

    //     static::deleted(function ($bidang) {
    //         CacheService::clearSeksiCaches($bidang->id, $bidang->organisasi_id);
    //     });
    // }
    public static function clearCache(int $bidangId, int $organisasiId): void
    {
        // Clear seksi by bidang cache
        $seksiKey = CacheService::CACHE_PREFIX . CacheService::CACHE_VERSION . 'seksi_by_bidang_' . $bidangId;
        Cache::forget($seksiKey);

        // Clear bidang by organisasi cache
        $bidangKey = CacheService::CACHE_PREFIX . 'bidang_by_org_' . $organisasiId;
        Cache::forget($bidangKey);

        // Clear bidang detail cache
        $bidangDetailKey = CacheService::CACHE_PREFIX . 'bidang_detail_' . $bidangId;
        Cache::forget($bidangDetailKey);

        // Clear bidang with organisasi cache
        Cache::forget(CacheService::CACHE_PREFIX . 'bidang_with_organisasi');

        // Clear user form data cache
        Cache::forget(CacheService::CACHE_PREFIX . CacheService::CACHE_VERSION . 'user_form_data');

        // Clear count caches
        Cache::forget(CacheService::CACHE_PREFIX . 'bidang_count');
        Cache::forget(CacheService::CACHE_PREFIX . 'seksi_count');
    }
    public function clearInstanceCache(): void
    {
        self::clearCache($this->id, $this->organisasi_id);
    }
    protected static function boot(): void
    {
        parent::boot();

        static::saved(function ($bidang) {
            self::clearCache($bidang->id, $bidang->organisasi_id);
        });

        static::deleted(function ($bidang) {
            self::clearCache($bidang->id, $bidang->organisasi_id);
        });
    }
}
