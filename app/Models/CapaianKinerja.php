<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CapaianKinerja extends Model
{
    use HasFactory;

    protected $table = 'capaian_kinerja';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_program',
        'id_kegiatan',
        'id_sub_kegiatan',
        'tahun',
        'target_dokumen',
        'target_nilai',
        'tw1',
        'tw2',
        'tw3',
        'tw4',
        'total',
        'persentase',
        'status_perencanaan',
        'status_realisasi',
    ];

    protected $casts = [
        'target_nilai' => 'decimal:2',
        'tw1' => 'decimal:2',
        'tw2' => 'decimal:2',
        'tw3' => 'decimal:2',
        'tw4' => 'decimal:2',
        'total' => 'decimal:2',
        'persentase' => 'decimal:2',
        'tahun' => 'integer',
    ];

    protected $attributes = [
        'tw1' => 0,
        'tw2' => 0,
        'tw3' => 0,
        'tw4' => 0,
        'total' => 0,
        'persentase' => 0,
        'status_perencanaan' => 'draft',
        'status_realisasi' => 'not_started',
    ];

    // Relationships
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'id_program', 'id_program');
    }

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(Kegiatan::class, 'id_kegiatan', 'id_kegiatan');
    }

    public function subKegiatan(): BelongsTo
    {
        return $this->belongsTo(SubKegiatan::class, 'id_sub_kegiatan', 'id_sub_kegiatan');
    }

    // Accessors & Mutators
    public function getPersentaseFormatAttribute(): string
    {
        return number_format($this->persentase, 2) . '%';
    }

    public function getStatusRealisasiAttribute($value)
    {
        if ($this->total == 0) {
            return 'not_started';
        } elseif ($this->persentase >= 100) {
            return 'completed';
        } elseif ($this->total > 0) {
            return 'in_progress';
        }

        return $value;
    }

    public function getStatusRealisasiLabelAttribute()
    {
        return match ($this->status_realisasi) {
            'not_started' => 'Belum Dimulai',
            'in_progress' => 'Dalam Progress',
            'completed' => 'Selesai',
            default => 'Unknown'
        };
    }

    public function getPersentaseColorAttribute()
    {
        return match (true) {
            $this->persentase >= 100 => 'success',
            $this->persentase >= 75 => 'warning',
            $this->persentase >= 50 => 'info',
            default => 'danger'
        };
    }

    // Scopes
    public function scopeByProgram($query, $programId)
    {
        return $query->where('id_program', $programId);
    }

    public function scopeByTahun($query, $tahun)
    {
        return $query->where('tahun', $tahun);
    }

    public function scopeCompleted($query)
    {
        return $query->where('persentase', '>=', 100);
    }

    public function scopeInProgress($query)
    {
        return $query->where('total', '>', 0)->where('persentase', '<', 100);
    }

    public function scopeNotStarted($query)
    {
        return $query->where('total', 0);
    }

    // Mutators untuk menghitung total otomatis
    public function setTw1Attribute($value)
    {
        $this->attributes['tw1'] = $value;
        $this->calculateTotal();
    }

    public function setTw2Attribute($value)
    {
        $this->attributes['tw2'] = $value;
        $this->calculateTotal();
    }

    public function setTw3Attribute($value)
    {
        $this->attributes['tw3'] = $value;
        $this->calculateTotal();
    }

    public function setTw4Attribute($value)
    {
        $this->attributes['tw4'] = $value;
        $this->calculateTotal();
    }

    private function calculateTotal()
    {
        $tw1 = $this->attributes['tw1'] ?? 0;
        $tw2 = $this->attributes['tw2'] ?? 0;
        $tw3 = $this->attributes['tw3'] ?? 0;
        $tw4 = $this->attributes['tw4'] ?? 0;

        $total = $tw1 + $tw2 + $tw3 + $tw4;
        $this->attributes['total'] = $total;

        if (isset($this->attributes['target_nilai']) && $this->attributes['target_nilai'] > 0) {
            $this->attributes['persentase'] = round(($total / $this->attributes['target_nilai']) * 100, 2);
        } else {
            $this->attributes['persentase'] = 0;
        }
    }

    // Boot method untuk handling model events
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Auto calculate total and percentage
            $model->total = $model->tw1 + $model->tw2 + $model->tw3 + $model->tw4;

            if ($model->target_nilai > 0) {
                $model->persentase = round(($model->total / $model->target_nilai) * 100, 2);
            } else {
                $model->persentase = 0;
            }

            // Auto update status_realisasi
            if ($model->total == 0) {
                $model->status_realisasi = 'not_started';
            } elseif ($model->persentase >= 100) {
                $model->status_realisasi = 'completed';
            } elseif ($model->total > 0) {
                $model->status_realisasi = 'in_progress';
            }
        });
    }
}
