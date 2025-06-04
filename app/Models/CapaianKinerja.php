<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CapaianKinerja extends Model
{
    use HasFactory;

    protected $table = 'capaian_kinerja';
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
    ];
    protected $casts = [
        // 'target_dokumen' => 'integer',
        'target_nilai' => 'decimal:2',
        'tw1' => 'decimal:2',
        'tw2' => 'decimal:2',
        'tw3' => 'decimal:2',
        'tw4' => 'decimal:2',
        'total' => 'decimal:2',
        'persentase' => 'decimal:2',
        'tahun' => 'integer',
    ];
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

    // Accessor untuk mendapatkan persentase dengan format yang tepat
    public function getPersentaseFormatAttribute(): string
    {
        return number_format($this->persentase, 2) . '%';
    }

    // Mutator untuk menghitung total otomatis saat menyimpan
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
}
