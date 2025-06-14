<?php
// app/Models/RencanaAnggaranKas.php

namespace App\Models;

use App\Services\YearContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RencanaAnggaranKas extends Model
{
    use HasFactory;

    protected $table = 'rencana_anggaran_kas';

    protected $fillable = [
        'tahun',
        'triwulan',
        'kategori',
        'deskripsi',
        'jumlah_rencana',
        'tanggal_rencana',
        'status',
        'catatan',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'jumlah_rencana' => 'decimal:2',
        'tanggal_rencana' => 'date',
    ];

    // Relationship
    public function realisasiAnggaranKas(): HasMany
    {
        return $this->hasMany(RealisasiAnggaranKas::class);
    }

    // Accessor
    public function getTriwulanTextAttribute(): string
    {
        $triwulanMap = [
            '1' => 'Triwulan I',
            '2' => 'Triwulan II',
            '3' => 'Triwulan III',
            '4' => 'Triwulan IV',
        ];

        return $triwulanMap[$this->triwulan] ?? 'Unknown';
    }

    public function getStatusTextAttribute(): string
    {
        $statusMap = [
            'draft' => 'Draft',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
        ];

        return $statusMap[$this->status] ?? 'Unknown';
    }

    public function getJumlahFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->jumlah_rencana, 0, ',', '.');
    }

    // Scope
    public function scopeByTahun($query, $tahun)
    {
        return $query->where('tahun', $tahun);
    }

    public function scopeByTriwulan($query, $triwulan)
    {
        return $query->where('triwulan', $triwulan);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
    public function scopeByYear($query, ?int $year = null)
    {
        $activeYear = $year ?? YearContext::getActiveYear();
        return $query->where('tahun', $activeYear);
    }

    public function scopeByQuarter($query, int $quarter)
    {
        return $query->where('triwulan', $quarter);
    }
    // Static method untuk mendapatkan total per triwulan
    public static function getTotalByTriwulan($tahun, $triwulan)
    {
        return self::byTahun($tahun)
            ->byTriwulan($triwulan)
            ->approved()
            ->sum('jumlah_rencana');
    }

    // Static method untuk mendapatkan data widget
    public static function getWidgetData($tahun = null)
    {
        $tahun = $tahun ?? date('Y');

        $data = [];
        for ($i = 1; $i <= 4; $i++) {
            $total = self::getTotalByTriwulan($tahun, $i);
            $data["triwulan_$i"] = $total;
        }

        return $data;
    }
}
