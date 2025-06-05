<?php

namespace App\Models;

use App\Services\YearContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubKegiatan extends Model
{
    use HasFactory, SoftDeletes;
    protected $primaryKey = 'id_sub_kegiatan';
    protected $table = 'sub_kegiatan';

    protected $fillable = [
        'kode_sub_kegiatan',
        'nama_sub_kegiatan',
        'id_kegiatan',
        'sumber_dana',
        'anggaran',
        'realisasi',
    ];

    protected $casts = [
        'anggaran' => 'integer',
        'realisasi' => 'integer',
        'kegiatan_id' => 'integer',
        'sumber_dana' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'persentase_serapan',
        'formatted_anggaran',
        'formatted_realisasi',
        'serapan_percentage',
        'serapan_color',
        'sumber_dana_string',
    ];
    public function getRouteKeyName()
    {
        return 'id_sub_kegiatan';
    }
    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(Kegiatan::class, 'id_kegiatan', 'id_kegiatan');
    }
    public function scopeForYear($query, $year = null)
    {
        $year = $year ?? YearContext::getActiveYear();
        return $query->where('tahun', $year);
    }
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (!$model->tahun) {
                $model->tahun = YearContext::getActiveYear();
            }
        });
    }
    public function getPersentaseRealisasiAttribute()
    {
        return $this->anggaran > 0 ? ($this->realisasi / $this->anggaran) * 100 : 0;
    }
    public function getPersentaseSerapanAttribute()
    {
        if (($this->anggaran ?? 0) == 0) {
            return 0;
        }

        $serapan = (($this->realisasi ?? 0) / $this->anggaran) * 100;
        return round($serapan, 2);
    }
    public function program()
    {
        return $this->hasOneThrough(
            Program::class,
            Kegiatan::class,
            'id_program',
            'id_program',
            'id_kegiatan',
            'id'
        );
    }
    public function getOrganisasiAttribute()
    {
        return $this->kegiatan->program->organisasi;
    }
    public function getSumberDanaStringAttribute(): string
    {
        if (is_array($this->sumber_dana) && !empty($this->sumber_dana)) {
            return implode(', ', $this->sumber_dana);
        }
        return '-';
    }

    /**
     * Mutator untuk memastikan sumber_dana selalu array
     */
    public function setSumberDanaAttribute($value)
    {
        if (is_string($value)) {
            $this->attributes['sumber_dana'] = json_encode([$value]);
        } elseif (is_array($value)) {
            $this->attributes['sumber_dana'] = json_encode($value);
        } else {
            $this->attributes['sumber_dana'] = json_encode([]);
        }
    }

    /**
     * Check apakah memiliki sumber dana tertentu
     */
    public function hasSumberDana(string $sumberDana): bool
    {
        return is_array($this->sumber_dana) && in_array($sumberDana, $this->sumber_dana);
    }

    public function scopeByOrganisasi($query, int $organisasiId)
    {
        return $query->whereHas('kegiatan.program.organisasi', function ($q) use ($organisasiId) {
            $q->where('id', $organisasiId);
        });
    }


    public function scopeByProgram($query, int $programId)
    {
        return $query->whereHas('kegiatan.program', function ($q) use ($programId) {
            $q->where('id', $programId);
        });
    }
    public function getFormattedAnggaranAttribute(): string
    {
        return 'Rp ' . number_format($this->anggaran, 0, ',', '.');
    }

    public function getFormattedRealisasiAttribute(): string
    {
        return 'Rp ' . number_format($this->realisasi, 0, ',', '.');
    }

    public function getSerapanPercentageAttribute(): string
    {
        return number_format($this->persentase_serapan, 2) . '%';
    }

    public function getSerapanColorAttribute(): string
    {
        $serapan = $this->persentase_serapan;

        return match (true) {
            $serapan >= 80 => 'success',
            $serapan >= 60 => 'warning',
            $serapan >= 40 => 'info',
            default => 'danger'
        };
    }

    /**
     * Scope untuk filter berdasarkan sumber dana
     */
    public function scopeBySumberDana($query, string $sumberDana)
    {
        return $query->whereJsonContains('sumber_dana', $sumberDana);
    }

    public function scopeSerapanRendah($query, float $threshold = 60)
    {
        return $query->whereRaw('
            CASE 
                WHEN anggaran = 0 OR anggaran IS NULL THEN 0
                ELSE (realisasi / anggaran * 100) 
            END < ?', [$threshold]);
    }

    public function scopeSerapanTinggi($query, float $threshold = 80)
    {
        return $query->whereRaw('
            CASE 
                WHEN anggaran = 0 OR anggaran IS NULL THEN 0
                ELSE (realisasi / anggaran * 100) 
            END >= ?', [$threshold]);
    }

    public function scopeBySerapanRange($query, float $min, float $max)
    {
        return $query->whereRaw('
            CASE 
                WHEN anggaran = 0 OR anggaran IS NULL THEN 0
                ELSE (realisasi / anggaran * 100) 
            END BETWEEN ? AND ?', [$min, $max]);
    }
}
