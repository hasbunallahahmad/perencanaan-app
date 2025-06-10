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

    // Tambahkan accessor ke appends
    protected $appends = [
        'anggaran',
        'realisasi',
        'persentase_serapan',
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

    // ========== RELATIONSHIPS ==========

    /**
     * Relasi ke indikator utama
     */
    public function indikator(): BelongsTo
    {
        return $this->belongsTo(MasterIndikator::class, 'indikator_id');
    }

    /**
     * Relasi ke program
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'id_program', 'id_program');
    }

    /**
     * Relasi ke sub kegiatan
     */
    public function subKegiatan(): HasMany
    {
        return $this->hasMany(SubKegiatan::class, 'id_kegiatan', 'id_kegiatan');
    }

    /**
     * Alias untuk konsistensi
     */
    public function subKegiatans(): HasMany
    {
        return $this->subKegiatan();
    }

    /**
     * Accessor untuk organisasi melalui program
     */
    public function getOrganisasiAttribute()
    {
        return $this->program?->organisasi;
    }

    /**
     * Accessor untuk nama indikator
     */
    public function getIndikatorNamaAttribute(): string
    {
        return $this->indikator?->nama_indikator ?? '-';
    }

    // ========== CALCULATED ATTRIBUTES ==========

    /**
     * Menghitung total anggaran dari semua sub kegiatan
     */
    public function getAnggaranAttribute(): int
    {
        return $this->subKegiatan()->sum('anggaran') ?? 0;
    }

    /**
     * Menghitung total realisasi dari semua sub kegiatan
     */
    public function getRealisasiAttribute(): int
    {
        return $this->subKegiatan()->sum('realisasi') ?? 0;
    }

    /**
     * Menghitung persentase serapan
     */
    public function getPersentaseSerapanAttribute(): float
    {
        $totalAnggaran = $this->anggaran;
        $totalRealisasi = $this->realisasi;

        if ($totalAnggaran > 0) {
            return round(($totalRealisasi / $totalAnggaran) * 100, 2);
        }

        return 0;
    }

    /**
     * Persentase realisasi (alias)
     */
    public function getPersentaseRealisasiAttribute(): float
    {
        return $this->persentase_serapan;
    }

    /**
     * Format anggaran untuk display
     */
    public function getFormattedAnggaranAttribute(): string
    {
        return 'Rp ' . number_format($this->anggaran, 0, ',', '.');
    }

    /**
     * Format realisasi untuk display
     */
    public function getFormattedRealisasiAttribute(): string
    {
        return 'Rp ' . number_format($this->realisasi, 0, ',', '.');
    }

    /**
     * Persentase serapan dengan format string
     */
    public function getSerapanPercentageAttribute(): string
    {
        return number_format($this->persentase_serapan, 2) . '%';
    }

    /**
     * Warna badge berdasarkan serapan
     */
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

    /**
     * Scope untuk filter berdasarkan tahun
     */
    public function scopeForYear($query, $year = null)
    {
        $year = $year ?? YearContext::getActiveYear();
        return $query->where('tahun', $year);
    }

    /**
     * Scope untuk filter berdasarkan program
     */
    public function scopeByProgram($query, int $programId)
    {
        return $query->where('id_program', $programId);
    }

    /**
     * Scope untuk filter berdasarkan organisasi
     */
    public function scopeByOrganisasi($query, int $organisasiId)
    {
        return $query->whereHas('program', function ($q) use ($organisasiId) {
            $q->where('organisasi_id', $organisasiId);
        });
    }

    /**
     * Scope untuk filter berdasarkan indikator
     */
    public function scopeByIndikator($query, $indikatorId)
    {
        return $query->where('indikator_id', $indikatorId);
    }

    /**
     * Scope untuk kegiatan dengan sub kegiatan
     */
    public function scopeHasSubKegiatan($query)
    {
        return $query->has('subKegiatan');
    }

    /**
     * Scope untuk serapan rendah
     */
    public function scopeSerapanRendah($query, float $threshold = 60)
    {
        return $query->whereHas('subKegiatan')
            ->havingRaw('
                (SELECT SUM(realisasi) FROM sub_kegiatan WHERE sub_kegiatan.id_kegiatan = kegiatan.id_kegiatan) / 
                NULLIF((SELECT SUM(anggaran) FROM sub_kegiatan WHERE sub_kegiatan.id_kegiatan = kegiatan.id_kegiatan), 0) * 100 < ?
            ', [$threshold]);
    }

    /**
     * Scope untuk serapan tinggi
     */
    public function scopeSerapanTinggi($query, float $threshold = 80)
    {
        return $query->whereHas('subKegiatan')
            ->havingRaw('
                (SELECT SUM(realisasi) FROM sub_kegiatan WHERE sub_kegiatan.id_kegiatan = kegiatan.id_kegiatan) / 
                NULLIF((SELECT SUM(anggaran) FROM sub_kegiatan WHERE sub_kegiatan.id_kegiatan = kegiatan.id_kegiatan), 0) * 100 >= ?
            ', [$threshold]);
    }

    /**
     * Scope dengan data lengkap
     */
    public function scopeWithFullData($query)
    {
        return $query->with(['program.organisasi', 'indikator'])
            ->withCount(['subKegiatan']);
    }

    // ========== BOOT METHOD ==========

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->tahun) {
                $model->tahun = YearContext::getActiveYear();
            }
        });
    }

    // ========== STATIC METHODS ==========

    /**
     * Mendapatkan statistik kegiatan
     */
    public static function getStatistics(): array
    {
        return [
            'total_kegiatan' => static::count(),
            'kegiatan_dengan_sub' => static::has('subKegiatan')->count(),
            'kegiatan_tanpa_sub' => static::doesntHave('subKegiatan')->count(),
            'total_anggaran' => static::join('sub_kegiatan', 'kegiatan.id_kegiatan', '=', 'sub_kegiatan.id_kegiatan')
                ->sum('sub_kegiatan.anggaran'),
            'total_realisasi' => static::join('sub_kegiatan', 'kegiatan.id_kegiatan', '=', 'sub_kegiatan.id_kegiatan')
                ->sum('sub_kegiatan.realisasi'),
        ];
    }
}
