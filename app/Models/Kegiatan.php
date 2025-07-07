<?php

namespace App\Models;

use App\Services\YearContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kegiatan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kegiatan';
    protected $primaryKey = 'id_kegiatan';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'kode_kegiatan',
        'nama_kegiatan',
        'id_program',
        'indikator_id',
        'tahun',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Tambahan accessor 
    protected $appends = [
        'formatted_anggaran',
        'formatted_realisasi',
        'serapan_percentage',
        'serapan_color',
        'indikator_nama',
    ];

    public function getRouteKeyName()
    {
        return 'id_kegiatan';
    }

    // ========== RELASI ==========
    public function indikator(): BelongsTo
    {
        return $this->belongsTo(MasterIndikator::class, 'indikator_id');
    }
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'id_program', 'id_program');
    }
    public function subKegiatan(): HasMany
    {
        return $this->hasMany(SubKegiatan::class, 'id_kegiatan', 'id_kegiatan');
    }
    public function subKegiatans(): HasMany
    {
        return $this->subKegiatan();
    }

    public function getOrganisasiAttribute()
    {
        return $this->program?->organisasi;
    }
    public function getIndikatorNamaAttribute(): string
    {
        return $this->indikator?->nama_indikator ?? '-';
    }

    // ========== perhitungan ==========

    public function getAnggaranAttribute(): int
    {
        if (isset($this->attributes['sub_kegiatan_sum_anggaran'])) {
            return $this->attributes['sub_kegiatan_sum_anggaran'] ?? 0;
        }
        if (!$this->relationLoaded('subKegiatan')) {
            return $this->subKegiatan()->sum('anggaran') ?? 0;
        }
        return  $this->subKegiatan()->sum('anggaran') ?? 0;
    }

    public function getRealisasiAttribute(): int
    {
        if (isset($this->attributes['sub_kegiatan_sum_realisasi'])) {
            return $this->attributes['sub_kegiatan_sum_realisasi'] ?? 0;
        }
        if (!$this->relationLoaded('subKegiatan')) {
            return $this->subKegiatan()->sum('realisasi') ?? 0;
        }
        return  $this->subKegiatan()->sum('realisasi') ?? 0;
    }

    public function getPersentaseSerapanAttribute(): float
    {
        $totalAnggaran = $this->anggaran;
        $totalRealisasi = $this->realisasi;

        if ($totalAnggaran > 0) {
            return round(($totalRealisasi / $totalAnggaran) * 100, 2);
        }

        return 0;
    }

    public function getPersentaseRealisasiAttribute(): float
    {
        return $this->persentase_serapan;
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

    // ========== SCOPES ==========

    public function scopeForYear($query, $year = null)
    {
        $year = $year ?? YearContext::getActiveYear();
        return $query->where('tahun', $year);
    }

    public function scopeByProgram($query, int $programId)
    {
        return $query->where('id_program', $programId);
    }

    public function scopeByOrganisasi($query, int $organisasiId)
    {
        return $query->whereHas('program', function ($q) use ($organisasiId) {
            $q->where('organisasi_id', $organisasiId);
        });
    }

    public function scopeByIndikator($query, $indikatorId)
    {
        return $query->where('indikator_id', $indikatorId);
    }

    public function scopeHasSubKegiatan($query)
    {
        return $query->has('subKegiatan');
    }

    public function scopeWithOptimizedData($query)
    {
        return $query->with(['program.organisasi', 'indikator'])
            ->withSum(['subKegiatan' => function ($query) {
                $query->whereNull('deleted_at');
            }], 'anggaran')
            ->withSum(['subKegiatan' => function ($query) {
                $query->whereNull('deleted_at');
            }], 'realisasi')
            ->withCount(['subKegiatan']);
    }
    public function scopeSerapanRendah($query, float $threshold = 60)
    {
        return $query->withOptimizedData()
            ->havingRaw('
                CASE 
                    WHEN COALESCE(sub_kegiatan_sum_anggaran, 0) = 0 THEN 0
                    ELSE (COALESCE(sub_kegiatan_sum_realisasi, 0) / sub_kegiatan_sum_anggaran * 100)
                END < ?
            ', [$threshold]);
    }

    public function scopeSerapanTinggi($query, float $threshold = 80)
    {
        return $query->withOptimizedData()
            ->havingRaw('
                CASE 
                    WHEN COALESCE(sub_kegiatan_sum_anggaran, 0) = 0 THEN 0
                    ELSE (COALESCE(sub_kegiatan_sum_realisasi, 0) / sub_kegiatan_sum_anggaran * 100)
                END >= ?
            ', [$threshold]);
    }

    public function scopeWithFullData($query)
    {
        return $this->scopeWithOptimizedData($query);
    }

    // ========== BOOT ==========

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->tahun) {
                $model->tahun = YearContext::getActiveYear();
            }
        });
    }

    // ========== STATIC ==========
    public static function getStatistics(): array
    {
        $stats = static::selectRaw('
            COUNT(*) as total_kegiatan,
            COUNT(CASE WHEN sub_kegiatan_count > 0 THEN 1 END) as kegiatan_dengan_sub,
            COUNT(CASE WHEN sub_kegiatan_count = 0 THEN 1 END) as kegiatan_tanpa_sub,
            SUM(COALESCE(sub_kegiatan_sum_anggaran, 0)) as total_anggaran,
            SUM(COALESCE(sub_kegiatan_sum_realisasi, 0)) as total_realisasi
        ')
            ->withCount('subKegiatan')
            ->withSum('subKegiatan', 'anggaran')
            ->withSum('subKegiatan', 'realisasi')
            ->first();

        return [
            'total_kegiatan' => $stats->total_kegiatan ?? 0,
            'kegiatan_dengan_sub' => $stats->kegiatan_dengan_sub ?? 0,
            'kegiatan_tanpa_sub' => $stats->kegiatan_tanpa_sub ?? 0,
            'total_anggaran' => $stats->total_anggaran ?? 0,
            'total_realisasi' => $stats->total_realisasi ?? 0,
        ];
    }
    public static function getOptimizedList($filters = [])
    {
        $query = static::withOptimizedData();

        if (isset($filters['program_id'])) {
            $query->byProgram($filters['program_id']);
        }

        if (isset($filters['organisasi_id'])) {
            $query->byOrganisasi($filters['organisasi_id']);
        }

        if (isset($filters['year'])) {
            $query->forYear($filters['year']);
        }

        return $query;
    }
}
