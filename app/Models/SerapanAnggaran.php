<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SerapanAnggaran extends Model
{
    use HasFactory;

    protected $table = 'serapan_anggaran';

    protected $fillable = [
        'id_sub_kegiatan',
        'tahun',
        'bulan',
        'anggaran',
        'realisasi',
        'persentase_serapan',
        'keterangan',
    ];

    protected $casts = [
        'anggaran' => 'decimal:2',
        'realisasi' => 'decimal:2',
        'persentase_serapan' => 'decimal:2',
        'tahun' => 'integer',
        'bulan' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the sub kegiatan that owns the serapan anggaran
     */
    public function subKegiatan(): BelongsTo
    {
        return $this->belongsTo(SubKegiatan::class, 'id_sub_kegiatan', 'id_sub_kegiatan');
    }

    /**
     * Get persentase serapan
     */
    public function getPersentaseSerapanAttribute()
    {
        if ($this->anggaran > 0) {
            return round(($this->realisasi / $this->anggaran) * 100, 2);
        }

        return 0;
    }

    /**
     * Get sisa anggaran
     */
    public function getSisaAnggaranAttribute()
    {
        return $this->anggaran - $this->realisasi;
    }

    /**
     * Get nama bulan
     */
    public function getNamaBulanAttribute()
    {
        $bulanMap = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        return $bulanMap[$this->bulan] ?? 'Unknown';
    }
    /**
     * Get formatted anggaran
     */
    public function getFormattedAnggaranAttribute()
    {
        return 'Rp ' . number_format($this->anggaran, 0, ',', '.');
    }

    /**
     * Get formatted realisasi
     */
    public function getFormattedRealisasiAttribute()
    {
        return 'Rp ' . number_format($this->realisasi, 0, ',', '.');
    }

    /**
     * Get formatted sisa anggaran
     */
    public function getFormattedSisaAnggaranAttribute()
    {
        return 'Rp ' . number_format($this->sisa_anggaran, 0, ',', '.');
    }

    /**
     * Scope untuk filter berdasarkan tahun
     */
    public function scopeByTahun($query, $tahun)
    {
        return $query->where('tahun', $tahun);
    }

    /**
     * Scope untuk filter berdasarkan bulan
     */
    public function scopeByBulan($query, $bulan)
    {
        return $query->where('bulan', $bulan);
    }

    /**
     * Scope untuk filter berdasarkan periode
     */
    public function scopeByPeriode($query, $tahun, $bulan = null)
    {
        $query->where('tahun', $tahun);

        if ($bulan) {
            $query->where('bulan', $bulan);
        }

        return $query;
    }
}
