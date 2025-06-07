<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tujas extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'id',
        'tujuan',
        'sasaran',
        'indikator',
        'target',
        'satuan',
        'realisasi_tw_1',
        'realisasi_tw_2',
        'realisasi_tw_3',
        'realisasi_tw_4',
        'persentase',
    ];
    protected $casts = [
        'target' => 'decimal:5',
        'realisasi_tw_1' => 'decimal:2',
        'realisasi_tw_2' => 'decimal:2',
        'realisasi_tw_3' => 'decimal:2',
        'realisasi_tw_4' => 'decimal:2',
        'persentase' => 'decimal:2',
        'deleted_at' => 'datetime',
    ];

    public function getTotalRealisasiAttribut(): float
    {
        return ($this->realisasi_tw_1 ?? 0) +
            ($this->realisasi_tw_2 ?? 0) +
            ($this->realisasi_tw_3 ?? 0) +
            ($this->realisasi_tw_4 ?? 0);
    }

    public function getPersentasePencapaianAttribute(): float
    {
        if ($this->target <= 0) {
            return 0;
        }

        return ($this->total_realisasi / $this->target) * 100;
    }
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $totalRealisasi = ($model->realisasi_tw_1 ?? 0) +
                ($model->realisasi_tw_2 ?? 0) +
                ($model->realisasi_tw_3 ?? 0) +
                ($model->realisasi_tw_4 ?? 0);

            if ($model->target > 0) {
                $model->persentase = ($totalRealisasi / $model->target) * 100;
            } else {
                $model->persentase = 0;
            }
        });
    }
}
