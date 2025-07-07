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

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'tahun' => 'integer',
    ];

    // ✅ HANYA APPEND YANG TIDAK MELAKUKAN QUERY
    protected $appends = [
        'kategori',
        'badge_color',
    ];

    // ✅ CACHE PROPERTIES UNTUK MENGHINDARI QUERY BERULANG
    protected $cachedTotalAnggaran;
    protected $cachedTotalRealisasi;
    protected $cachedTotalKegiatan;
    protected $cachedTotalSubKegiatan;

    public static function validationRules($id = null)
    {
        return [
            'kode_program' => [
                'required',
                'string',
                'max:20',
                Rule::unique('program')
                    ->where(fn($query) => $query->where('tahun', request()->tahun))
                    ->ignore($id)
            ],
            'tahun' => 'required|integer',
            'nama_program' => 'required|string|max:500',
            'organisasi_id' => 'required|exists:organisasi,id',
            'indikator_id' => 'nullable|exists:master_indikator,id',
            'indikator_id_2' => 'nullable|exists:master_indikator,id|different:indikator_id',
        ];
    }

    // ========== RELATIONSHIPS ==========
    public function organisasi(): BelongsTo
    {
        return $this->belongsTo(Organisasi::class, 'organisasi_id', 'id');
    }

    public function bidang()
    {
        return $this->belongsTo(Bidang::class, 'bidang_id');
    }

    public function indikator()
    {
        return $this->belongsTo(MasterIndikator::class, 'indikator_id');
    }

    public function indikator2()
    {
        return $this->belongsTo(MasterIndikator::class, 'indikator_id_2');
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
            'id_program',
            'id_kegiatan',
            'id_program',
            'id_kegiatan'
        );
    }

    // ========== OPTIMIZED ACCESSORS WITH CACHING ==========
    public function getTotalAnggaranAttribute(): int
    {
        // Priority 1: Dari select query withCalculatedTotals()
        if (isset($this->attributes['total_anggaran_calculated'])) {
            return (int) $this->attributes['total_anggaran_calculated'];
        }

        // Priority 2: Dari eager loaded relations
        if ($this->relationLoaded('subKegiatan')) {
            return $this->subKegiatan->sum('anggaran');
        }

        // Priority 3: Cache untuk menghindari query berulang
        if (!isset($this->cachedTotalAnggaran)) {
            $this->cachedTotalAnggaran = $this->kegiatan()
                ->join('sub_kegiatan', 'kegiatan.id_kegiatan', '=', 'sub_kegiatan.id_kegiatan')
                ->sum('sub_kegiatan.anggaran') ?? 0;
        }

        return $this->cachedTotalAnggaran;
    }

    public function getTotalRealisasiAttribute(): int
    {
        if (isset($this->attributes['total_realisasi_calculated'])) {
            return (int) $this->attributes['total_realisasi_calculated'];
        }

        if ($this->relationLoaded('subKegiatan')) {
            return $this->subKegiatan->sum('realisasi');
        }

        if (!isset($this->cachedTotalRealisasi)) {
            $this->cachedTotalRealisasi = $this->kegiatan()
                ->join('sub_kegiatan', 'kegiatan.id_kegiatan', '=', 'sub_kegiatan.id_kegiatan')
                ->sum('sub_kegiatan.realisasi') ?? 0;
        }

        return $this->cachedTotalRealisasi;
    }

    public function getTotalKegiatanAttribute(): int
    {
        if (isset($this->attributes['kegiatan_count'])) {
            return (int) $this->attributes['kegiatan_count'];
        }

        if ($this->relationLoaded('kegiatan')) {
            return $this->kegiatan->count();
        }

        if (!isset($this->cachedTotalKegiatan)) {
            $this->cachedTotalKegiatan = $this->kegiatan()->count();
        }

        return $this->cachedTotalKegiatan;
    }

    public function getTotalSubKegiatanAttribute(): int
    {
        if (isset($this->attributes['total_sub_kegiatan_calculated'])) {
            return (int) $this->attributes['total_sub_kegiatan_calculated'];
        }

        if ($this->relationLoaded('subKegiatan')) {
            return $this->subKegiatan->count();
        }

        if (!isset($this->cachedTotalSubKegiatan)) {
            $this->cachedTotalSubKegiatan = $this->subKegiatan()->count();
        }

        return $this->cachedTotalSubKegiatan;
    }

    // ✅ ACCESSOR YANG TIDAK MELAKUKAN QUERY - AMAN UNTUK APPENDS
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

    // ✅ ACCESSOR YANG BISA DIPANGGIL MANUAL (TIDAK DI APPENDS)
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

    public function getIndikatorListAttribute(): string
    {
        $indikators = [];

        if ($this->relationLoaded('indikator') && $this->indikator) {
            $indikators[] = $this->indikator->nama_indikator;
        }

        if ($this->relationLoaded('indikator2') && $this->indikator2) {
            $indikators[] = $this->indikator2->nama_indikator;
        }

        return implode(', ', $indikators);
    }

    // ========== METHODS ==========
    public function getAllIndikators()
    {
        $indikators = collect();

        if ($this->relationLoaded('indikator') && $this->indikator) {
            $indikators->push($this->indikator);
        } elseif (!$this->relationLoaded('indikator') && $this->indikator_id) {
            $indikators->push($this->indikator);
        }

        if ($this->relationLoaded('indikator2') && $this->indikator2) {
            $indikators->push($this->indikator2);
        } elseif (!$this->relationLoaded('indikator2') && $this->indikator_id_2) {
            $indikators->push($this->indikator2);
        }

        return $indikators;
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

    public function scopeByIndikator($query, $indikatorId)
    {
        return $query->where(function ($q) use ($indikatorId) {
            $q->where('indikator_id', $indikatorId)
                ->orWhere('indikator_id_2', $indikatorId);
        });
    }

    public function scopeHasIndikator($query, $indikatorId)
    {
        return $query->where(function ($q) use ($indikatorId) {
            $q->where('indikator_id', $indikatorId)
                ->orWhere('indikator_id_2', $indikatorId);
        });
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
        // ✅ OPTIMIZED - Single query untuk multiple statistics
        $baseQuery = static::query();

        return [
            'total_program' => $baseQuery->count(),
            'program_dengan_kegiatan' => $baseQuery->has('kegiatan')->count(),
            'program_tanpa_kegiatan' => $baseQuery->doesntHave('kegiatan')->count(),
            'program_penunjang' => $baseQuery->penunjang()->count(),
            'program_pug' => $baseQuery->pug()->count(),
            'program_anak' => $baseQuery->anak()->count(),
            // Untuk total anggaran/realisasi, gunakan aggregated query
            ...$baseQuery->selectRaw('
                COALESCE(SUM(anggaran_data.total_anggaran), 0) as total_anggaran_semua,
                COALESCE(SUM(anggaran_data.total_realisasi), 0) as total_realisasi_semua
            ')
                ->leftJoinSub(
                    function ($query) {
                        $query->from('kegiatan')
                            ->join('sub_kegiatan', 'kegiatan.id_kegiatan', '=', 'sub_kegiatan.id_kegiatan')
                            ->select('kegiatan.id_program')
                            ->selectRaw('SUM(sub_kegiatan.anggaran) as total_anggaran')
                            ->selectRaw('SUM(sub_kegiatan.realisasi) as total_realisasi')
                            ->groupBy('kegiatan.id_program');
                    },
                    'anggaran_data',
                    'program.id_program',
                    '=',
                    'anggaran_data.id_program'
                )->first()->toArray()
        ];
    }
}
