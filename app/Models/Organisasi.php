<?php

namespace App\Models;

use App\Services\CacheService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organisasi extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'deskripsi',
        'alamat',
        'kota',
        'aktif'
    ];
    protected $casts = [
        'aktif' => 'boolean'
    ];
    protected $attributes = [
        'aktif' => true,
    ];
    public function bidangs(): HasMany
    {
        return $this->hasMany(Bidang::class);
    }

    public function sekretariat(): HasMany
    {
        return $this->hasMany(Bidang::class)->where('is_sekretariat', true);
    }

    public function bidangOperasional(): HasMany
    {
        return $this->hasMany(Bidang::class)->where('is_sekretariat', false);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
    public function programs(): HasMany
    {
        return $this->hasMany(Program::class, 'organisasi_id', 'id');
    }
    public function scopeActive($query)
    {
        return $query->where('aktif', true);
    }
    public function scopeWithAllCounts($query)
    {
        return $query->withCount([
            'bidangs',
            'sekretariat',
            'bidangOperasional',
            'users',
            'programs'
        ]);
    }
    public function scopeWithRelations($query)
    {
        return $query->with([
            'bidangs:id,organisasi_id,nama,is_sekretariat',
            'users:id,organisasi_id,name',
            'programs:id,organisasi_id,nama'
        ]);
    }
    public function getTotalBidangsAttribute(): int
    {
        return $this->bidangs_count ?? $this->bidangs()->count();
    }

    public function getTotalSekretariatAttribute(): int
    {
        return $this->sekretariat_count ?? $this->sekretariat()->count();
    }

    public function getTotalUsersAttribute(): int
    {
        return $this->users_count ?? $this->users()->count();
    }

    public function getTotalProgramsAttribute(): int
    {
        return $this->programs_count ?? $this->programs()->count();
    }
    protected static function booted(): void
    {
        static::saved(function () {
            CacheService::clearAllCaches();
        });

        static::deleted(function () {
            CacheService::clearAllCaches();
        });
    }
}
