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
        'deskripsi',
        'jumlah_realisasi',
        'persentase_realisasi',
        'tanggal_realisasi',
        'status',
        'catatan_realisasi',
        'bukti_dokumen',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'triwulan' => 'integer',
        'jumlah_realisasi' => 'decimal:2',
        'persentase_realisasi' => 'decimal:2',
        'tanggal_realisasi' => 'date',
    ];
    protected $attributes = [
        'tahun' => null,
        'triwulan' => 1,
        'status' => 'completed',
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

    // public function getPersentaseRealisasiAttribute(): float
    // {
    //     if (!$this->rencanaAnggaranKas || $this->rencanaAnggaranKas->jumlah_rencana == 0) {
    //         return 0;
    //     }

    //     return round(($this->jumlah_realisasi / $this->rencanaAnggaranKas->jumlah_rencana) * 100, 2);
    // }

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

    public function scopeLatestByYear($query, ?int $year = null)
    {
        $activeYear = $year ?? YearContext::getActiveYear();
        return $query->where('tahun', $activeYear)
            ->orderBy('created_at', 'desc')
            ->limit(1);
    }

    public function scopeLatestCompletedByYear($query, ?int $year = null)
    {
        $activeYear = $year ?? YearContext::getActiveYear();
        return $query->where('tahun', $activeYear)
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->limit(1);
    }

    // Static method untuk mendapatkan data berdasarkan tahun
    public static function getLatestByYear($tahun = null, $completed = true)
    {
        $tahun = $tahun ?? date('Y');

        $query = self::byTahun($tahun);

        if ($completed) {
            $query = $query->completed();
        }

        return $query->orderBy('created_at', 'desc')->first();
    }

    // Static method untuk mendapatkan total per triwulan
    public static function getTotalByTriwulan($tahun, $triwulan)
    {
        return self::byTahun($tahun)
            ->byTriwulan($triwulan)
            ->completed()
            ->sum('jumlah_realisasi');
    }

    // Static method untuk mendapatkan data widget (data realisasi per triwulan)
    public static function getWidgetData($tahun = null)
    {
        $tahun = $tahun ?? date('Y');

        $data = [];
        for ($i = 1; $i <= 4; $i++) {
            $total = self::getTotalByTriwulan($tahun, $i);
            $data["triwulan_$i"] = $total;
        }

        $latestRecord = self::getLatestByYear($tahun, true);
        $data['latest'] = $latestRecord ? [
            'triwulan' => $latestRecord->triwulan,
            'jumlah_realisasi' => $latestRecord->jumlah_realisasi,
            'tanggal_update' => $latestRecord->created_at,
            'status' => $latestRecord->status_text,
        ] : null;

        return $data;
    }

    // Static method untuk mendapatkan perbandingan data rencana vs realisasi
    public static function getComparisonData($tahun = null)
    {
        $tahun = $tahun ?? date('Y');

        // Ambil data rencana anggaran (tahunan)
        $rencanaRecord = RencanaAnggaranKas::getLatestByYear($tahun, true);
        $totalRencana = $rencanaRecord ? $rencanaRecord->jumlah_rencana : 0;

        // Bagi rata rencana per triwulan untuk perbandingan
        $rencanaPerTriwulan = $totalRencana / 4;

        $comparison = [];
        for ($i = 1; $i <= 4; $i++) {
            $realisasi = self::getTotalByTriwulan($tahun, $i);
            $selisih = $realisasi - $rencanaPerTriwulan;
            $persentase = $rencanaPerTriwulan > 0 ? round(($realisasi / $rencanaPerTriwulan) * 100, 2) : 0;

            $comparison["triwulan_$i"] = [
                'rencana' => $rencanaPerTriwulan,
                'realisasi' => $realisasi,
                'selisih' => $selisih,
                'persentase' => $persentase,
                'rencana_formatted' => 'Rp ' . number_format($rencanaPerTriwulan, 0, ',', '.'),
                'realisasi_formatted' => 'Rp ' . number_format($realisasi, 0, ',', '.'),
            ];
        }

        // Tambahkan data total
        $totalRealisasi = array_sum(array_column($comparison, 'realisasi'));
        $comparison['total'] = [
            'rencana' => $totalRencana,
            'realisasi' => $totalRealisasi,
            'selisih' => $totalRealisasi - $totalRencana,
            'persentase' => $totalRencana > 0 ? round(($totalRealisasi / $totalRencana) * 100, 2) : 0,
            'rencana_formatted' => 'Rp ' . number_format($totalRencana, 0, ',', '.'),
            'realisasi_formatted' => 'Rp ' . number_format($totalRealisasi, 0, ',', '.'),
        ];

        return $comparison;
    }

    // Static method untuk mendapatkan riwayat realisasi per triwulan
    public static function getRiwayatRealisasi($tahun = null)
    {
        $tahun = $tahun ?? date('Y');

        return self::byTahun($tahun)
            ->completed()
            ->orderBy('triwulan', 'asc')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($record) {
                return [
                    'id' => $record->id,
                    'triwulan' => $record->triwulan_text,
                    'jumlah_realisasi' => $record->jumlah_realisasi,
                    'jumlah_formatted' => $record->jumlah_formatted,
                    'tanggal_realisasi' => $record->tanggal_realisasi,
                    'created_at' => $record->created_at,
                    'deskripsi' => $record->deskripsi,
                    'persentase_realisasi' => $record->persentase_realisasi,
                ];
            });
    }
    public function getPersentaseRealisasiAttribute($value): float
    {
        // Jika kolom database kosong, hitung dari relasi
        if ($value === null && $this->rencanaAnggaranKas) {
            if ($this->rencanaAnggaranKas->jumlah_rencana > 0) {
                return round(($this->jumlah_realisasi / $this->rencanaAnggaranKas->jumlah_rencana) * 100, 2);
            }
            return 0;
        }

        return (float) $value;
    }
    // Static method untuk mendapatkan ringkasan realisasi per triwulan
    public static function getRingkasanRealisasi($tahun = null)
    {
        $tahun = $tahun ?? date('Y');

        $data = [];
        for ($i = 1; $i <= 4; $i++) {
            $total = self::getTotalByTriwulan($tahun, $i);
            $data["triwulan_$i"] = [
                'total' => $total,
                'formatted' => 'Rp ' . number_format($total, 0, ',', '.'),
            ];
        }

        $latestRecord = self::getLatestByYear($tahun, true);
        $data['latest'] = $latestRecord ? [
            'triwulan' => $latestRecord->triwulan,
            'jumlah_realisasi' => $latestRecord->jumlah_realisasi,
            'tanggal_update' => $latestRecord->created_at,
        ] : null;

        return $data;
    }
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            // Set tahun default jika belum diisi
            if (!$model->tahun) {
                $model->tahun = YearContext::getActiveYear() ?? date('Y');
            }

            // Set triwulan default jika belum diisi
            if (!$model->triwulan) {
                $model->triwulan = self::getCurrentTriwulan();
            }

            // Set status default jika belum diisi
            if (!$model->status) {
                $model->status = 'pending';
            }
        });
        // Observer untuk mengisi persentase_realisasi otomatis
        static::saving(function ($model) {
            if ($model->rencana_anggaran_kas_id && $model->jumlah_realisasi) {
                $rencana = $model->rencanaAnggaranKas ??
                    \App\Models\RencanaAnggaranKas::find($model->rencana_anggaran_kas_id);

                if ($rencana && $rencana->jumlah_rencana > 0) {
                    $model->persentase_realisasi = round(
                        ($model->jumlah_realisasi / $rencana->jumlah_rencana) * 100,
                        2
                    );
                } else {
                    $model->persentase_realisasi = 0;
                }
            }
        });
    }
    public static function getCurrentTriwulan(): int
    {
        $month = date('n');

        if ($month >= 1 && $month <= 3) {
            return 1;
        } elseif ($month >= 4 && $month <= 6) {
            return 2;
        } elseif ($month >= 7 && $month <= 9) {
            return 3;
        } else {
            return 4;
        }
    }
}
