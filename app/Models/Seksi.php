<?php

namespace App\Models;

use App\Services\CacheService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Seksi extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'kode',
        'deskripsi',
        'bidang_id',
        'jenis',
        'aktif'
    ];

    protected $casts = [
        'aktif' => 'boolean'
    ];

    // Relationships
    public function bidang(): BelongsTo
    {
        return $this->belongsTo(Bidang::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // Accessors
    public function getJenisLabelAttribute(): string
    {
        return $this->jenis === 'subbagian' ? 'Subbagian' : 'Seksi';
    }

    // Scopes
    public function scopeAktif($query, bool $aktif = true)
    {
        return $query->where('aktif', $aktif);
    }

    public function scopeWithBidang($query)
    {
        return $query->with(['bidang.organisasi']);
    }

    public function scopeByBidang($query, int $bidangId)
    {
        return $query->where('bidang_id', $bidangId);
    }

    public function scopeByJenis($query, string $jenis)
    {
        return $query->where('jenis', $jenis);
    }

    public function scopeByOrganisasi($query, int $organisasiId)
    {
        return $query->whereHas('bidang', function ($q) use ($organisasiId) {
            $q->where('organisasi_id', $organisasiId);
        });
    }

    // Static Methods
    public static function validationRules(): array
    {
        return [
            'nama' => 'required|string|max:255',
            'kode' => 'nullable|string|max:50',
            'deskripsi' => 'nullable|string',
            'bidang_id' => 'required|exists:bidangs,id',
            'jenis' => 'required|in:seksi,subbagian',
            'aktif' => 'boolean'
        ];
    }

    public static function getByBidangCached(int $bidangId): \Illuminate\Database\Eloquent\Collection
    {
        try {
            return CacheService::getSeksiByBidang($bidangId);
        } catch (\Exception $e) {
            Log::warning('Failed to get cached seksi by bidang: ' . $e->getMessage());
            // Fallback to direct query
            return self::where('bidang_id', $bidangId)->get();
        }
    }

    public static function getCountCached(): int
    {
        try {
            return CacheService::getCount('seksi');
        } catch (\Exception $e) {
            Log::warning('Failed to get cached seksi count: ' . $e->getMessage());
            // Fallback to direct count
            return self::count();
        }
    }

    // Helper methods
    public function isAktif(): bool
    {
        return $this->aktif === true;
    }

    public function isSeksi(): bool
    {
        return $this->jenis === 'seksi';
    }

    public function isSubbagian(): bool
    {
        return $this->jenis === 'subbagian';
    }

    public function getOrganisasi()
    {
        return $this->bidang?->organisasi;
    }

    public function getOrganisasiNama(): ?string
    {
        return $this->bidang?->organisasi?->nama;
    }

    // Boot method untuk event handling
    protected static function boot()
    {
        parent::boot();

        static::created(function ($seksi) {
            self::clearRelatedCaches();
        });

        static::updated(function ($seksi) {
            self::clearRelatedCaches();
        });

        static::deleted(function ($seksi) {
            self::clearRelatedCaches();
        });
    }

    protected static function clearRelatedCaches(): void
    {
        try {
            Cache::forget('seksi_badge_count');
            CacheService::clearAllCaches();
        } catch (\Exception $e) {
            Log::warning('Failed to clear related caches: ' . $e->getMessage());
        }
    }
}
