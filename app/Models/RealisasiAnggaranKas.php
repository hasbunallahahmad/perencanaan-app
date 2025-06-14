<?php
// app/Models/RealisasiAnggaranKas.php

namespace App\Models;

use App\Services\YearContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RealisasiAnggaranKas extends Model
{
    use HasFactory;

    protected $table = 'realisasi_anggaran_kas';

    protected $fillable = [
        'rencana_anggaran_kas_id',
        'tahun',
        'triwulan',
        'kategori',
        'deskripsi',
        'jumlah_realisasi',
        'tanggal_realisasi',
        'status',
        'catatan_realisasi',
        'bukti_dokumen',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'jumlah_realisasi' => 'decimal:2',
        'tanggal_realisasi' => 'date',
    ];

    // Relationship
    public function rencanaAnggaranKas(): BelongsTo
    {
        return $this->belongsTo(RencanaAnggaranKas::class);
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
            'pending' => 'Pending',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];

        return $statusMap[$this->status] ?? 'Unknown';
    }

    public function getJumlahFormattedAttribute(): string
    {
        return 'Rp ' . number_format($this->jumlah_realisasi, 0, ',', '.');
    }

    public function getPersentaseRealisasiAttribute(): float
    {
        if (!$this->rencanaAnggaranKas || $this->rencanaAnggaranKas->jumlah_rencana == 0) {
            return 0;
        }

        return round(($this->jumlah_realisasi / $this->rencanaAnggaranKas->jumlah_rencana) * 100, 2);
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

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
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

    public static function getComparisonData(int $year): array
    {
        $quarters = [];

        for ($i = 1; $i <= 4; $i++) {
            // Get rencana data for this quarter and year
            $rencana = RencanaAnggaranKas::byYear($year)
                ->byQuarter($i)
                ->sum('jumlah_rencana') ?? 0;

            // Get realisasi data for this quarter and year
            $realisasi = static::byYear($year)
                ->byQuarter($i)
                ->sum('jumlah_realisasi') ?? 0;

            $selisih = $realisasi - $rencana;
            $persentase = $rencana > 0 ? round(($realisasi / $rencana) * 100, 2) : 0;

            $quarters["triwulan_$i"] = [
                'rencana' => $rencana,
                'realisasi' => $realisasi,
                'selisih' => $selisih,
                'persentase' => $persentase,
            ];
        }

        return $quarters;
    }
    // Static method untuk mendapatkan total per triwulan
    public static function getTotalByTriwulan($tahun, $triwulan)
    {
        return self::byTahun($tahun)
            ->byTriwulan($triwulan)
            ->completed()
            ->sum('jumlah_realisasi');
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

    // Static method untuk mendapatkan perbandingan data
    // public static function getComparisonData($tahun = null)
    // {
    //     $tahun = $tahun ?? date('Y');

    //     $rencanaData = RencanaAnggaranKas::getWidgetData($tahun);
    //     $realisasiData = self::getWidgetData($tahun);

    //     $comparison = [];
    //     for ($i = 1; $i <= 4; $i++) {
    //         $rencana = $rencanaData["triwulan_$i"];
    //         $realisasi = $realisasiData["triwulan_$i"];
    //         $persentase = $rencana > 0 ? round(($realisasi / $rencana) * 100, 2) : 0;

    //         $comparison["triwulan_$i"] = [
    //             'rencana' => $rencana,
    //             'realisasi' => $realisasi,
    //             'selisih' => $realisasi - $rencana,
    //             'persentase' => $persentase,
    //         ];
    //     }

    //     return $comparison;
    // }
}
