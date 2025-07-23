<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use App\Services\YearContext;

class RealisasiAnggaranKas extends Model
{
    use HasFactory;

    protected $table = 'realisasi_anggaran_kas';

    protected $fillable = [
        'rencana_anggaran_kas_id',
        'tahun',
        'triwulan',
        'deskripsi',
        'jumlah_realisasi',
        'rencana_tw_1',
        'rencana_tw_2',
        'rencana_tw_3',
        'rencana_tw_4',
        'realisasi_tw_1',
        'realisasi_tw_2',
        'realisasi_tw_3',
        'realisasi_tw_4',
        'tanggal_realisasi_tw_1',
        'tanggal_realisasi_tw_2',
        'tanggal_realisasi_tw_3',
        'tanggal_realisasi_tw_4',
        'realisasi_sd_tw',
        'persentase_total',
        'persentase_realisasi',
        'tanggal_realisasi',
        'status',
        'catatan_realisasi',
        'bukti_dokumen'
    ];

    protected $casts = [
        'tahun' => 'integer',
        'jumlah_realisasi' => 'decimal:2',
        'rencana_tw_1' => 'decimal:2',
        'rencana_tw_2' => 'decimal:2',
        'rencana_tw_3' => 'decimal:2',
        'rencana_tw_4' => 'decimal:2',
        'realisasi_tw_1' => 'decimal:2',
        'realisasi_tw_2' => 'decimal:2',
        'realisasi_tw_3' => 'decimal:2',
        'realisasi_tw_4' => 'decimal:2',
        'realisasi_sd_tw' => 'decimal:2',
        'persentase_total' => 'decimal:2',
        'persentase_realisasi' => 'decimal:2',
        'tanggal_realisasi_tw_1' => 'date',
        'tanggal_realisasi_tw_2' => 'date',
        'tanggal_realisasi_tw_3' => 'date',
        'tanggal_realisasi_tw_4' => 'date',
        'tanggal_realisasi' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Add appends to make accessors available when converting to array/JSON
    protected $appends = [
        'total_rencana',
        'total_realisasi',
        'persentase_realisasi_calculated',
        'persentase_realisasi_terhadap_pagu',
        'status_color',
        'status_text',
        'triwulan_text'
    ];

    /**
     * Relationship to RencanaAnggaranKas
     */
    public function rencanaAnggaranKas(): BelongsTo
    {
        return $this->belongsTo(RencanaAnggaranKas::class, 'rencana_anggaran_kas_id');
    }

    /**
     * Scope untuk filter berdasarkan tahun
     */
    public function scopeByYear(Builder $query, ?int $year = null): Builder
    {
        $year = $year ?? YearContext::getActiveYear();
        return $query->where('tahun', $year);
    }

    /**
     * Scope untuk filter berdasarkan status
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk filter berdasarkan triwulan
     */
    public function scopeByTriwulan(Builder $query, string $triwulan): Builder
    {
        return $query->where('triwulan', $triwulan);
    }

    /**
     * Accessor untuk mendapatkan total rencana
     */
    public function getTotalRencanaAttribute(): float
    {
        return ($this->rencana_tw_1 ?? 0) +
            ($this->rencana_tw_2 ?? 0) +
            ($this->rencana_tw_3 ?? 0) +
            ($this->rencana_tw_4 ?? 0);
    }

    /**
     * Accessor untuk mendapatkan total realisasi
     */
    public function getTotalRealisasiAttribute(): float
    {
        return ($this->realisasi_tw_1 ?? 0) +
            ($this->realisasi_tw_2 ?? 0) +
            ($this->realisasi_tw_3 ?? 0) +
            ($this->realisasi_tw_4 ?? 0);
    }

    /**
     * Accessor untuk mendapatkan persentase realisasi berdasarkan total rencana
     */
    public function getPersentaseRealisasiCalculatedAttribute(): float
    {
        $totalRealisasi = $this->getTotalRealisasiAttribute();
        $totalRencana = $this->getTotalRencanaAttribute();

        if ($totalRencana > 0) {
            return round(($totalRealisasi / $totalRencana) * 100, 2);
        }

        return 0;
    }

    /**
     * Accessor untuk mendapatkan persentase realisasi terhadap pagu
     */
    public function getPersentaseRealisasiTerhadapPaguAttribute(): float
    {
        $totalRealisasi = $this->getTotalRealisasiAttribute();
        $pagu = $this->rencanaAnggaranKas->jumlah_rencana ?? 0;

        if ($pagu > 0) {
            return round(($totalRealisasi / $pagu) * 100, 2);
        }

        return 0;
    }

    /**
     * Accessor untuk mendapatkan status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->attributes['status'] ?? '') {
            'pending' => 'secondary',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Accessor untuk mendapatkan status text
     */
    public function getStatusTextAttribute(): string
    {
        return match ($this->attributes['status'] ?? '') {
            'pending' => 'Pending',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => $this->attributes['status'] ?? '',
        };
    }

    /**
     * Accessor untuk mendapatkan triwulan text
     */
    public function getTriwulanTextAttribute(): string
    {
        return match ($this->attributes['triwulan'] ?? '') {
            '1' => 'Triwulan 1',
            '2' => 'Triwulan 2',
            '3' => 'Triwulan 3',
            '4' => 'Triwulan 4',
            default => $this->attributes['triwulan'] ?? '',
        };
    }

    /**
     * Boot method untuk auto-calculate values
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Auto-calculate total realisasi
            $model->realisasi_sd_tw = ($model->realisasi_tw_1 ?? 0) +
                ($model->realisasi_tw_2 ?? 0) +
                ($model->realisasi_tw_3 ?? 0) +
                ($model->realisasi_tw_4 ?? 0);

            // Calculate total rencana
            $totalRencana = ($model->rencana_tw_1 ?? 0) +
                ($model->rencana_tw_2 ?? 0) +
                ($model->rencana_tw_3 ?? 0) +
                ($model->rencana_tw_4 ?? 0);

            // Auto-calculate persentase berdasarkan total rencana
            if ($totalRencana > 0) {
                $model->persentase_total = round(($model->realisasi_sd_tw / $totalRencana) * 100, 2);
                $model->persentase_realisasi = $model->persentase_total;
            } else {
                $model->persentase_total = 0;
                $model->persentase_realisasi = 0;
            }

            // Set tahun from YearContext if not provided
            if (empty($model->tahun)) {
                $model->tahun = YearContext::getActiveYear();
            }
        });
    }

    /**
     * Get formatted currency value
     */
    public function getFormattedRealisasi(string $field): string
    {
        $value = $this->{$field} ?? 0;
        return 'Rp ' . number_format($value, 0, ',', '.');
    }

    /**
     * Get formatted currency value for rencana
     */
    public function getFormattedRencana(string $field): string
    {
        $value = $this->{$field} ?? 0;
        return 'Rp ' . number_format($value, 0, ',', '.');
    }

    /**
     * Check if realization is complete for a quarter
     */
    public function isQuarterComplete(int $quarter): bool
    {
        $field = "realisasi_tw_{$quarter}";
        return !empty($this->{$field}) && $this->{$field} > 0;
    }

    /**
     * Check if plan is set for a quarter
     */
    public function isQuarterPlanSet(int $quarter): bool
    {
        $field = "rencana_tw_{$quarter}";
        return !empty($this->{$field}) && $this->{$field} > 0;
    }

    /**
     * Get completion percentage for progress tracking
     */
    public function getCompletionPercentage(): float
    {
        $completedQuarters = 0;
        for ($i = 1; $i <= 4; $i++) {
            if ($this->isQuarterComplete($i)) {
                $completedQuarters++;
            }
        }
        return ($completedQuarters / 4) * 100;
    }

    /**
     * Get planning percentage for progress tracking
     */
    public function getPlanningPercentage(): float
    {
        $plannedQuarters = 0;
        for ($i = 1; $i <= 4; $i++) {
            if ($this->isQuarterPlanSet($i)) {
                $plannedQuarters++;
            }
        }
        return ($plannedQuarters / 4) * 100;
    }

    /**
     * Get variance between plan and realization for a quarter
     */
    public function getQuarterVariance(int $quarter): float
    {
        $rencanaField = "rencana_tw_{$quarter}";
        $realisasiField = "realisasi_tw_{$quarter}";

        $rencana = $this->{$rencanaField} ?? 0;
        $realisasi = $this->{$realisasiField} ?? 0;

        return $realisasi - $rencana;
    }

    /**
     * Get variance percentage for a quarter
     */
    public function getQuarterVariancePercentage(int $quarter): float
    {
        $rencanaField = "rencana_tw_{$quarter}";
        $realisasiField = "realisasi_tw_{$quarter}";

        $rencana = $this->{$rencanaField} ?? 0;
        $realisasi = $this->{$realisasiField} ?? 0;

        if ($rencana > 0) {
            return round((($realisasi - $rencana) / $rencana) * 100, 2);
        }

        return 0;
    }

    /**
     * Get total variance between total plan and total realization
     */
    public function getTotalVariance(): float
    {
        return $this->getTotalRealisasiAttribute() - $this->getTotalRencanaAttribute();
    }

    /**
     * Get total variance percentage
     */
    public function getTotalVariancePercentage(): float
    {
        $totalRencana = $this->getTotalRencanaAttribute();
        if ($totalRencana > 0) {
            return round((($this->getTotalRealisasiAttribute() - $totalRencana) / $totalRencana) * 100, 2);
        }

        return 0;
    }
}
