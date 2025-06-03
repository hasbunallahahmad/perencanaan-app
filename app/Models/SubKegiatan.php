<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubKegiatan extends Model
{
    use HasFactory;

    protected $table = 'sub_kegiatan';

    protected $fillable = [
        'kode_sub_kegiatan',
        'nama_sub_kegiatan',
        'id_kegiatan',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(Kegiatan::class, 'id_kegiatan');
    }
    public function serapanAnggaran(): HasMany
    {
        return $this->hasMany(SerapanAnggaran::class, 'id_sub_kegiatan');
    }
    public function latestSerapanAnggaran(): BelongsTo
    {
        return $this->belongsTo(SerapanAnggaran::class, 'id_sub_kegiatan')
            ->latest('tahun')
            ->latest('bulan');
    }
    public function getTotalAnggaranAttribute()
    {
        return $this->serapanAnggaran()->sum('anggaran');
    }
    public function getTotalRealisasiAttribute()
    {
        return $this->serapanAnggaran()->sum('realisasi');
    }
    public function getPersentaseSerapanAttribute()
    {
        $totalAnggaran = $this->total_anggaran;
        $totalRealisasi = $this->total_realisasi;

        if ($totalAnggaran > 0) {
            return round(($totalRealisasi / $totalAnggaran) * 100, 2);
        }

        return 0;
    }
    public function program()
    {
        return $this->hasOneThrough(Program::class, Kegiatan::class, 'id_program', 'id_program', 'id_kegiatan', 'id');
    }
    public function getOrganisasiAttribute()
    {
        return $this->kegiatan->program->organisasi;
    }
    public function scopeByTahun($query, $tahun)
    {
        return $query->whereHas('serapanAnggaran', function ($q) use ($tahun) {
            $q->where('tahun', $tahun);
        });
    }
    public function scopeByBulan($query, $bulan)
    {
        return $query->whereHas('serapanAnggaran', function ($q) use ($bulan) {
            $q->where('bulan', $bulan);
        });
    }
}
