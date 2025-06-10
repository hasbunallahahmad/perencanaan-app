<?php

namespace App\Models;

use App\Services\YearContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Validation\Rule;

class Program extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'program';
    protected $primaryKey = 'id_program';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'kode_program',
        'nama_program',
        'organisasi_id',
        'indikator_id',
        'indikator_id_2',
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

    protected $appends = [
        'total_anggaran',
        'total_realisasi',
        'persentase_serapan',
        'formatted_anggaran',
        'formatted_realisasi',
        'total_kegiatan',
        'total_sub_kegiatan',
        'kategori',
        'badge_color',
        'indikator_list',
    ];
    public static function validationRules($id = null)
    {
        return [
            'kode_program' => [
                'required',
                'string',
                'max:20',
                Rule::unique('program')
                    ->where(function ($query) {
                        return $query->where('tahun', request()->tahun);
                    })
                    ->ignore($id)
            ],
            'tahun' => 'required|integer',
            'nama_program' => 'required|string',
        ];
    }
    // ========== RELATIONSHIPS ==========
    public function indikator()
    {
        return $this->belongsTo(MasterIndikator::class, 'indikator_id');
    }
    public function indikator2()
    {
        return $this->belongsTo(MasterIndikator::class, 'indikator_id_2');
    }
    public function organisasi(): BelongsTo
    {
        return $this->belongsTo(Organisasi::class, 'organisasi_id', 'id');
    }

    public function kegiatan(): HasMany
    {
        return $this->hasMany(Kegiatan::class, 'id_program', 'id_program');
    }

    public function subKegiatan(): HasManyThrough
    {
        return $this->hasManyThrough(
            SubKegiatan::class,
            Kegiatan::class,
            'id_program',        // Foreign key on kegiatan table
            'id_kegiatan',       // Foreign key on sub_kegiatan table
            'id_program',        // Local key on program table
            'id_kegiatan'        // Local key on kegiatan table
        );
    }

    // ========== OPTIMIZED ACCESSORS ==========
    public function getTotalAnggaranAttribute(): int
    {
        // Priority 1: Jika sudah ada dari select query
        if (isset($this->attributes['total_anggaran_calculated'])) {
            return (int) $this->attributes['total_anggaran_calculated'];
        }

        // Priority 2: Jika relasi subKegiatan sudah di-load
        if ($this->relationLoaded('subKegiatan')) {
            return $this->subKegiatan->sum('anggaran');
        }

        // Priority 3: Fallback ke query (seperti kode asli Anda)
        return $this->kegiatan()
            ->join('sub_kegiatan', 'kegiatan.id_kegiatan', '=', 'sub_kegiatan.id_kegiatan')
            ->sum('sub_kegiatan.anggaran') ?? 0;
    }

    public function getTotalRealisasiAttribute(): int
    {
        // Priority 1: Jika sudah ada dari select query
        if (isset($this->attributes['total_realisasi_calculated'])) {
            return (int) $this->attributes['total_realisasi_calculated'];
        }

        // Priority 2: Jika relasi subKegiatan sudah di-load
        if ($this->relationLoaded('subKegiatan')) {
            return $this->subKegiatan->sum('realisasi');
        }

        // Priority 3: Fallback ke query (seperti kode asli Anda)
        return $this->kegiatan()
            ->join('sub_kegiatan', 'kegiatan.id_kegiatan', '=', 'sub_kegiatan.id_kegiatan')
            ->sum('sub_kegiatan.realisasi') ?? 0;
    }

    public function getTotalKegiatanAttribute(): int
    {
        // Priority 1: Jika sudah ada dari withCount
        if (isset($this->attributes['kegiatan_count'])) {
            return (int) $this->attributes['kegiatan_count'];
        }

        // Priority 2: Jika relasi kegiatan sudah di-load
        if ($this->relationLoaded('kegiatan')) {
            return $this->kegiatan->count();
        }

        // Priority 3: Fallback ke query
        return $this->kegiatan()->count();
    }

    public function getTotalSubKegiatanAttribute(): int
    {
        // Priority 1: Jika sudah ada dari select query
        if (isset($this->attributes['total_sub_kegiatan_calculated'])) {
            return (int) $this->attributes['total_sub_kegiatan_calculated'];
        }

        // Priority 2: Jika relasi sudah di-load
        if ($this->relationLoaded('subKegiatan')) {
            return $this->subKegiatan->count();
        }

        // Priority 3: Fallback ke query
        return $this->subKegiatan()->count();
    }

    public function getPersentaseRealisasiAttribute()
    {
        return $this->anggaran > 0 ? ($this->realisasi / $this->anggaran) * 100 : 0;
    }

    public function getPersentaseSerapanAttribute(): float
    {
        $totalAnggaran = $this->total_anggaran;
        $totalRealisasi = $this->total_realisasi;

        if ($totalAnggaran > 0) {
            return round(($totalRealisasi / $totalAnggaran) * 100, 2);
        }

        return 0;
    }

    public function getFormattedAnggaranAttribute(): string
    {
        return 'Rp ' . number_format($this->total_anggaran, 0, ',', '.');
    }

    public function getFormattedRealisasiAttribute(): string
    {
        return 'Rp ' . number_format($this->total_realisasi, 0, ',', '.');
    }

    public function getKategoriAttribute(): string
    {
        $kode = $this->kode_program;

        return match (true) {
            str_starts_with($kode, '2.08.01') => 'Program Penunjang',
            str_starts_with($kode, '2.08.02') => 'PUG & Pemberdayaan Perempuan',
            str_starts_with($kode, '2.08.03') => 'Perlindungan Perempuan',
            str_starts_with($kode, '2.08.04') => 'Peningkatan Kualitas Keluarga',
            str_starts_with($kode, '2.08.05') => 'Data Gender dan Anak',
            str_starts_with($kode, '2.08.06') => 'Pemenuhan Hak Anak',
            str_starts_with($kode, '2.08.07') => 'Perlindungan Khusus Anak',
            str_starts_with($kode, '2.13.04') => 'Administrasi Pemerintahan Desa',
            str_starts_with($kode, '2.13.05') => 'Pemberdayaan Lembaga Kemasyarakatan',
            default => 'Lainnya',
        };
    }

    public function getBadgeColorAttribute(): string
    {
        return match ($this->kategori) {
            'Program Penunjang' => 'gray',
            'PUG & Pemberdayaan Perempuan' => 'blue',
            'Perlindungan Perempuan' => 'red',
            'Peningkatan Kualitas Keluarga' => 'green',
            'Data Gender dan Anak' => 'yellow',
            'Pemenuhan Hak Anak' => 'purple',
            'Perlindungan Khusus Anak' => 'pink',
            'Administrasi Pemerintahan Desa' => 'indigo',
            'Pemberdayaan Lembaga Kemasyarakatan' => 'teal',
            default => 'gray',
        };
    }
    public function getAllIndikators()
    {
        $indikators = collect();

        if ($this->indikator) {
            $indikators->push($this->indikator);
        }

        if ($this->indikator2) {
            $indikators->push($this->indikator2);
        }

        return $indikators;
    }
    public function getIndikatorListAttribute(): string
    {
        $indikators = [];

        if ($this->indikator) {
            $indikators[] = $this->indikator->nama_indikator;
        }

        if ($this->indikator2) {
            $indikators[] = $this->indikator2->nama_indikator;
        }

        return implode(', ', $indikators);
    }
    // ========== SCOPES ==========

    // TAMBAHAN: Scope untuk filter berdasarkan indikator
    public function scopeByIndikator($query, $indikatorId)
    {
        return $query->where(function ($q) use ($indikatorId) {
            $q->where('indikator_id', $indikatorId)
                ->orWhere('indikator_id_2', $indikatorId);
        });
    }

    // TAMBAHAN: Scope untuk program yang memiliki indikator tertentu
    public function scopeHasIndikator($query, $indikatorId)
    {
        return $query->where(function ($q) use ($indikatorId) {
            $q->where('indikator_id', $indikatorId)
                ->orWhere('indikator_id_2', $indikatorId);
        });
    }

    // ========== SCOPES ==========
    public function scopeForYear($query, $year = null)
    {
        $year = $year ?? YearContext::getActiveYear();
        return $query->where('tahun', $year);
    }

    public function scopeByKode($query, string $kode)
    {
        return $query->where('kode_program', $kode);
    }

    public function scopeByOrganisasi($query, int $organisasiId)
    {
        return $query->where('organisasi_id', $organisasiId);
    }

    public function scopeHasKegiatan($query)
    {
        return $query->has('kegiatan');
    }

    public function scopePenunjang($query)
    {
        return $query->where('kode_program', 'like', '2.08.01%');
    }

    public function scopePug($query)
    {
        return $query->where('kode_program', 'like', '2.08.02%');
    }

    public function scopeAnak($query)
    {
        return $query->where(function ($q) {
            $q->where('kode_program', 'like', '2.08.06%')
                ->orWhere('kode_program', 'like', '2.08.07%');
        });
    }

    // ========== OPTIMIZATION SCOPES ==========
    public function scopeWithCalculatedTotals($query)
    {
        return $query->selectRaw('
            program.*,
            COALESCE(
                (SELECT SUM(sk.anggaran) 
                 FROM kegiatan k 
                 JOIN sub_kegiatan sk ON k.id_kegiatan = sk.id_kegiatan 
                 WHERE k.id_program = program.id_program), 0
            ) as total_anggaran_calculated,
            COALESCE(
                (SELECT SUM(sk.realisasi) 
                 FROM kegiatan k 
                 JOIN sub_kegiatan sk ON k.id_kegiatan = sk.id_kegiatan 
                 WHERE k.id_program = program.id_program), 0
            ) as total_realisasi_calculated,
            COALESCE(
                (SELECT COUNT(sk.id_sub_kegiatan) 
                 FROM kegiatan k 
                 JOIN sub_kegiatan sk ON k.id_kegiatan = sk.id_kegiatan 
                 WHERE k.id_program = program.id_program), 0
            ) as total_sub_kegiatan_calculated
        ');
    }

    public function scopeWithFullData($query)
    {
        return $query->with(['organisasi', 'indikator', 'indikator2'])
            ->withCount(['kegiatan'])
            ->withCalculatedTotals();
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
    public static function getStatistics(): array
    {
        return [
            'total_program' => static::count(),
            'program_dengan_kegiatan' => static::has('kegiatan')->count(),
            'program_tanpa_kegiatan' => static::doesntHave('kegiatan')->count(),
            'program_penunjang' => static::penunjang()->count(),
            'program_pug' => static::pug()->count(),
            'program_anak' => static::anak()->count(),
            'total_anggaran_semua' => static::join('kegiatan', 'program.id_program', '=', 'kegiatan.id_program')
                ->join('sub_kegiatan', 'kegiatan.id_kegiatan', '=', 'sub_kegiatan.id_kegiatan')
                ->sum('sub_kegiatan.anggaran'),
            'total_realisasi_semua' => static::join('kegiatan', 'program.id_program', '=', 'kegiatan.id_program')
                ->join('sub_kegiatan', 'kegiatan.id_kegiatan', '=', 'sub_kegiatan.id_kegiatan')
                ->sum('sub_kegiatan.realisasi'),
        ];
    }
}
