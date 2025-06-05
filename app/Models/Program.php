<?php

namespace App\Models;

use App\Services\YearContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'deskripsi',
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
        'formatted_realisasi'
    ];
    public function organisasi()
    {
        return $this->belongsTo(Organisasi::class, 'organisasi_id', 'id');
    }
    public function kegiatan()
    {
        return $this->hasMany(Kegiatan::class, 'id_program', 'id_program');
    }
    public function subKegiatan()
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
    public function getTotalAnggaranAttribute(): int
    {
        return $this->kegiatan()->join('sub_kegiatan', 'kegiatan.id_kegiatan', '=', 'sub_kegiatan.id_kegiatan')
            ->sum('sub_kegiatan.anggaran') ?? 0;
    }
    public function getTotalRealisasiAttribute(): int
    {
        return $this->kegiatan()->join('sub_kegiatan', 'kegiatan.id_kegiatan', '=', 'sub_kegiatan.id_kegiatan')
            ->sum('sub_kegiatan.realisasi') ?? 0;
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
    public function getTotalKegiatanAttribute(): int
    {
        return $this->kegiatan()->count();
    }
    public function getTotalSubKegiatanAttribute(): int
    {
        return $this->subKegiatan()->count();
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
    public function getKategoriAttribute(): string
    {
        $kode = $this->kode_program;

        if (str_starts_with($kode, '2.08.01')) {
            return 'Program Penunjang';
        } elseif (str_starts_with($kode, '2.08.02')) {
            return 'PUG & Pemberdayaan Perempuan';
        } elseif (str_starts_with($kode, '2.08.03')) {
            return 'Perlindungan Perempuan';
        } elseif (str_starts_with($kode, '2.08.04')) {
            return 'Peningkatan Kualitas Keluarga';
        } elseif (str_starts_with($kode, '2.08.05')) {
            return 'Data Gender dan Anak';
        } elseif (str_starts_with($kode, '2.08.06')) {
            return 'Pemenuhan Hak Anak';
        } elseif (str_starts_with($kode, '2.08.07')) {
            return 'Perlindungan Khusus Anak';
        } elseif (str_starts_with($kode, '2.13.04')) {
            return 'Administrasi Pemerintahan Desa';
        } elseif (str_starts_with($kode, '2.13.05')) {
            return 'Pemberdayaan Lembaga Kemasyarakatan';
        }
        return 'Lainnya';
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
