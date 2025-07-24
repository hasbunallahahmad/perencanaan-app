<?php

// App/Models/Tujas.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Tujas extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'master_tujuan_sasaran_id',
        'master_sasaran_id',
        'tujuan',
        'sasaran',
        'indikator_tujuan_text',
        'indikator_sasaran_text',
        'tahun',
        'target_tujuan',
        'satuan_tujuan',
        'target_sasaran',
        'satuan_sasaran',
        'realisasi_tujuan_tw_1',
        'realisasi_tujuan_tw_2',
        'realisasi_tujuan_tw_3',
        'realisasi_tujuan_tw_4',
        'realisasi_sasaran_tw_1',
        'realisasi_sasaran_tw_2',
        'realisasi_sasaran_tw_3',
        'realisasi_sasaran_tw_4',
    ];

    protected $casts = [
        'target_tujuan' => 'decimal:3',
        'target_sasaran' => 'decimal:3',
        'realisasi_tujuan_tw_1' => 'decimal:3',
        'realisasi_tujuan_tw_2' => 'decimal:3',
        'realisasi_tujuan_tw_3' => 'decimal:3',
        'realisasi_tujuan_tw_4' => 'decimal:3',
        'realisasi_sasaran_tw_1' => 'decimal:3',
        'realisasi_sasaran_tw_2' => 'decimal:3',
        'realisasi_sasaran_tw_3' => 'decimal:3',
        'realisasi_sasaran_tw_4' => 'decimal:3',
    ];

    // Relationships
    public function masterTujuanSasaran()
    {
        return $this->belongsTo(MasterTujuanSasaran::class, 'master_tujuan_sasaran_id');
    }

    public function masterSasaran()
    {
        return $this->belongsTo(MasterSasaran::class, 'master_sasaran_id');
    }

    // Accessors for Tujuan
    public function getTotalRealisasiTujuanAttribute()
    {
        return $this->realisasi_tujuan_tw_1 + $this->realisasi_tujuan_tw_2 +
            $this->realisasi_tujuan_tw_3 + $this->realisasi_tujuan_tw_4;
    }

    public function getPersentaseTujuanCalculatedAttribute()
    {
        if (!$this->target_tujuan || $this->target_tujuan == 0) {
            return 0;
        }
        return ($this->total_realisasi_tujuan / $this->target_tujuan) * 100;
    }

    public function getStatusTujuanPencapaianAttribute()
    {
        $persentase = $this->persentase_tujuan_calculated;

        if ($persentase >= 100) {
            return 'Tercapai';
        } elseif ($persentase >= 75) {
            return 'Baik';
        } elseif ($persentase >= 50) {
            return 'Cukup';
        } else {
            return 'Kurang';
        }
    }

    public function getStatusTujuanColorAttribute()
    {
        $persentase = $this->persentase_tujuan_calculated;

        if ($persentase >= 100) {
            return 'success';
        } elseif ($persentase >= 75) {
            return 'primary';
        } elseif ($persentase >= 50) {
            return 'warning';
        } else {
            return 'danger';
        }
    }

    // Accessors for Sasaran
    public function getTotalRealisasiSasaranAttribute()
    {
        return $this->realisasi_sasaran_tw_1 + $this->realisasi_sasaran_tw_2 +
            $this->realisasi_sasaran_tw_3 + $this->realisasi_sasaran_tw_4;
    }

    public function getPersentaseSasaranCalculatedAttribute()
    {
        if (!$this->target_sasaran || $this->target_sasaran == 0) {
            return 0;
        }
        return ($this->total_realisasi_sasaran / $this->target_sasaran) * 100;
    }

    public function getStatusSasaranPencapaianAttribute()
    {
        $persentase = $this->persentase_sasaran_calculated;

        if ($persentase >= 100) {
            return 'Tercapai';
        } elseif ($persentase >= 75) {
            return 'Baik';
        } elseif ($persentase >= 50) {
            return 'Cukup';
        } else {
            return 'Kurang';
        }
    }

    public function getStatusSasaranColorAttribute()
    {
        $persentase = $this->persentase_sasaran_calculated;

        if ($persentase >= 100) {
            return 'success';
        } elseif ($persentase >= 75) {
            return 'primary';
        } elseif ($persentase >= 50) {
            return 'warning';
        } else {
            return 'danger';
        }
    }

    // Legacy accessors for backward compatibility
    public function getTotalRealisasiAttribute()
    {
        return $this->total_realisasi_tujuan;
    }

    public function getPersentaseCalculatedAttribute()
    {
        return $this->persentase_tujuan_calculated;
    }

    public function getStatusPencapaianAttribute()
    {
        return $this->status_tujuan_pencapaian;
    }

    public function getStatusColorAttribute()
    {
        return $this->status_tujuan_color;
    }

    // Scopes
    public function scopeHighAchievement(Builder $query, float $threshold = 100.0): Builder
    {
        return $query->whereRaw("
            ((realisasi_tujuan_tw_1 + realisasi_tujuan_tw_2 + realisasi_tujuan_tw_3 + realisasi_tujuan_tw_4) / NULLIF(target_tujuan, 0)) * 100 >= ?
        ", [$threshold]);
    }

    public function scopeLowAchievement(Builder $query, float $threshold = 50.0): Builder
    {
        return $query->whereRaw("
            ((realisasi_tujuan_tw_1 + realisasi_tujuan_tw_2 + realisasi_tujuan_tw_3 + realisasi_tujuan_tw_4) / NULLIF(target_tujuan, 0)) * 100 < ?
        ", [$threshold]);
    }

    public function scopeAchievementBetween(Builder $query, float $min, float $max): Builder
    {
        return $query->whereRaw("
            ((realisasi_tujuan_tw_1 + realisasi_tujuan_tw_2 + realisasi_tujuan_tw_3 + realisasi_tujuan_tw_4) / NULLIF(target_tujuan, 0)) * 100 BETWEEN ? AND ?
        ", [$min, $max]);
    }

    // Methods
    public function resetRealisasi()
    {
        $this->update([
            'realisasi_tujuan_tw_1' => 0,
            'realisasi_tujuan_tw_2' => 0,
            'realisasi_tujuan_tw_3' => 0,
            'realisasi_tujuan_tw_4' => 0,
            'realisasi_sasaran_tw_1' => 0,
            'realisasi_sasaran_tw_2' => 0,
            'realisasi_sasaran_tw_3' => 0,
            'realisasi_sasaran_tw_4' => 0,
        ]);
    }
}
