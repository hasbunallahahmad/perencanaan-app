<?php

namespace App\Models;

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
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'persentase_serapan',
        'formatted_anggaran',
        'formatted_realisasi',
        'serapan_percentage',
        'serapan_color'
    ];
    public function getRouteKeyName()
    {
        return 'id_sub_kegiatan';
    }
    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(Kegiatan::class, 'id_kegiatan', 'id_kegiatan');
    }
    public function getPersentaseSerapanAttribute()
    {
        if (($this->anggaran ?? 0) == 0) {
            return 0;
        }

        $serapan = (($this->realisasi ?? 0) / $this->anggaran) * 100;
        return round($serapan, 2);
    }
    // public function getAnggaranAttribute(): int
    // {
    //     return $this->anggaran ?? 0;
    // }
    // public function getRealisasiAttribute(): int
    // {
    //     return $this->realisasi ?? 0;
    // }

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
    public function scopeByOrganisasi($query, int $organisasiId)
    {
        return $query->whereHas('kegiatan.program.organisasi', function ($q) use ($organisasiId) {
            $q->where('id', $organisasiId);
        });
    }
    public function getFormattedAnggaranAttribute(): string
    {
        return 'Rp ' . number_format($this->anggaran, 0, ',', '.');
    }
    public function scopeByProgram($query, int $programId)
    {
        return $query->whereHas('kegiatan.program', function ($q) use ($programId) {
            $q->where('id', $programId);
        });
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
