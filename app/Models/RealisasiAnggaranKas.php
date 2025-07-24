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
        'tanggal_realisasi',
        'realisasi_sd_tw',
        'persentase_total',
        'persentase_realisasi',
        'status',
        'catatan_realisasi',
        'bukti_dokumen',
    ];

    protected $casts = [
        // Data finansial menggunakan decimal untuk presisi tinggi
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
        'jumlah_realisasi' => 'decimal:2',

        // Tanggal
        'tanggal_realisasi_tw_1' => 'date',
        'tanggal_realisasi_tw_2' => 'date',
        'tanggal_realisasi_tw_3' => 'date',
        'tanggal_realisasi_tw_4' => 'date',
        'tanggal_realisasi' => 'date',

        // Integer dan enum
        'tahun' => 'integer',
        'triwulan' => 'integer',
    ];

    protected $attributes = [
        'rencana_tw_1' => '0.00',
        'rencana_tw_2' => '0.00',
        'rencana_tw_3' => '0.00',
        'rencana_tw_4' => '0.00',
        'realisasi_tw_1' => '0.00',
        'realisasi_tw_2' => '0.00',
        'realisasi_tw_3' => '0.00',
        'realisasi_tw_4' => '0.00',
        'realisasi_sd_tw' => '0.00',
        'persentase_total' => '0.00',
        'status' => 'pending',
    ];

    // Add appends to define computed attributes
    protected $appends = [
        'total_rencana',
        'total_realisasi',
        'total_rencana_formatted',
        'total_realisasi_formatted',
        'persentase_formatted',
        'status_color',
        'status_label',
        'persentase_color',
    ];

    // Relationship
    public function rencanaAnggaranKas(): BelongsTo
    {
        return $this->belongsTo(RencanaAnggaranKas::class);
    }

    // Accessor untuk format currency Indonesia - Fixed type conversion
    public function getRencanaTw1FormattedAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->rencana_tw_1, 0, ',', '.');
    }

    public function getRencanaTw2FormattedAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->rencana_tw_2, 0, ',', '.');
    }

    public function getRencanaTw3FormattedAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->rencana_tw_3, 0, ',', '.');
    }

    public function getRencanaTw4FormattedAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->rencana_tw_4, 0, ',', '.');
    }

    public function getRealisasiTw1FormattedAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->realisasi_tw_1, 0, ',', '.');
    }

    public function getRealisasiTw2FormattedAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->realisasi_tw_2, 0, ',', '.');
    }

    public function getRealisasiTw3FormattedAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->realisasi_tw_3, 0, ',', '.');
    }

    public function getRealisasiTw4FormattedAttribute(): string
    {
        return 'Rp ' . number_format((float) $this->realisasi_tw_4, 0, ',', '.');
    }

    // Computed attributes - Fixed with explicit type conversion
    public function getTotalRencanaAttribute(): float
    {
        return (float) $this->rencana_tw_1 +
            (float) $this->rencana_tw_2 +
            (float) $this->rencana_tw_3 +
            (float) $this->rencana_tw_4;
    }

    public function getTotalRealisasiAttribute(): float
    {
        return (float) $this->realisasi_tw_1 +
            (float) $this->realisasi_tw_2 +
            (float) $this->realisasi_tw_3 +
            (float) $this->realisasi_tw_4;
    }

    public function getTotalRencanaFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->getTotalRencanaAttribute(), 0, ',', '.');
    }

    public function getTotalRealisasiFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->getTotalRealisasiAttribute(), 0, ',', '.');
    }

    public function getPersentaseFormattedAttribute(): string
    {
        return number_format((float) $this->persentase_total, 2) . '%';
    }

    // Status badge color helper
    public function getStatusColorAttribute(): string
    {
        // Ensure status is not null before using match
        $status = $this->attributes['status'] ?? 'pending';

        return match ($status) {
            'pending' => 'secondary',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        // Ensure status is not null before using match
        $status = $this->attributes['status'] ?? 'pending';

        return match ($status) {
            'pending' => 'Pending',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => $status,
        };
    }

    // Percentage color helper for badge
    public function getPersentaseColorAttribute(): string
    {
        $persentase = (float) $this->persentase_total;
        return match (true) {
            $persentase >= 100 => 'success',
            $persentase >= 75 => 'warning',
            default => 'danger',
        };
    }

    // Scopes
    public function scopeByYear(Builder $query, ?int $year = null): Builder
    {
        $year = $year ?? YearContext::getActiveYear();
        return $query->where('tahun', $year);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    // Methods untuk kalkulasi - Fixed type safety
    public function calculatePersentase(): void
    {
        $totalRencana = $this->getTotalRencanaAttribute();

        if ($totalRencana > 0) {
            $this->persentase_total = ($this->getTotalRealisasiAttribute() / $totalRencana) * 100;
        } else {
            $this->persentase_total = 0;
        }
    }

    public function updateRealisasiSdTw(): void
    {
        $this->realisasi_sd_tw = $this->getTotalRealisasiAttribute();
    }

    // Boot method untuk auto-calculate
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->updateRealisasiSdTw();
            $model->calculatePersentase();
        });
    }

    // Validation rules
    public static function validationRules(): array
    {
        return [
            'tahun' => ['required', 'integer', 'min:2020', 'max:2030'],
            'rencana_anggaran_kas_id' => ['required', 'exists:rencana_anggaran_kas,id'],
            'rencana_tw_1' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'rencana_tw_2' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'rencana_tw_3' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'rencana_tw_4' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'realisasi_tw_1' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'realisasi_tw_2' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'realisasi_tw_3' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'realisasi_tw_4' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'tanggal_realisasi_tw_1' => ['nullable', 'date'],
            'tanggal_realisasi_tw_2' => ['nullable', 'date'],
            'tanggal_realisasi_tw_3' => ['nullable', 'date'],
            'tanggal_realisasi_tw_4' => ['nullable', 'date'],
            'status' => ['required', 'in:pending,completed,cancelled'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'catatan_realisasi' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
