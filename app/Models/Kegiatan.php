<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kegiatan extends Model
{
    use HasFactory;
    protected $table = 'kegiatan';
    protected $primaryKey = 'id_kegiatan';
    protected $fillable = [
        'kode_kegiatan',
        'nama_kegiatan',
        'id_program',
        'anggaran',
        'realisasi',
    ];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'id_program', 'id_program');
    }
    public function subKegiatans(): HasMany
    {
        // if ($this->primaryKey === 'id_kegiatan') {
        //     return $this->hasMany(SubKegiatan::class, 'id_kegiatan', 'id_kegiatan');
        // } else {
        //     return $this->hasMany(SubKegiatan::class, 'id_kegiatan');
        // }
        return $this->hasMany(SubKegiatan::class, 'id_kegiatan', 'id_kegiatan');
    }
    // public function getTotalAnggaranAttribute()
    // {
    //     return $this->subKegiatans()
    //         ->join('serapan_anggaran', 'sub_kegiatan.id', '=', 'serapan_anggaran.id_sub_kegiatan')
    //         ->sum('serapan_anggaran.anggaran');
    // }
    // public function getTotalRealisasiAttribute()
    // {
    //     return $this->subKegiatans()
    //         ->join('serapan_anggaran', 'sub_kegiatan.id', '=', 'serapan_anggaran.id_sub_kegiatan')
    //         ->sum('serapan_anggaran.realisasi');
    // }
    // public function getTotalAnggaranAttribute()
    // {
    //     return $this->subKegiatans->sum(function ($subKegiatan) {
    //         return $subKegiatan->serapanAnggaran->sum('anggaran');
    //     });
    // }

    // public function getTotalRealisasiAttribute()
    // {
    //     return $this->subKegiatans->sum(function ($subKegiatan) {
    //         return $subKegiatan->serapanAnggaran->sum('realisasi');
    //     });
    // }
    // public function getPersentaseSerapanAttribute()
    // {
    //     $totalAnggaran = $this->total_anggaran;
    //     $totalRealisasi = $this->total_realisasi;

    //     if ($totalAnggaran > 0) {
    //         return round(($totalRealisasi / $totalAnggaran) * 100, 2);
    //     }

    //     return 0;
    // }
    public function getOrganisasiAttribute()
    {
        return $this->program?->organisasi;
    }
}
