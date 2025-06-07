<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CapaianKinerjaProgram extends Model
{
    use HasFactory;

    protected $table = 'capaian_kinerja_program';

    protected $fillable = [
        'id_program',
        'kode_program',
        'nama_program',
        'tahun',
        'target_dokumen',
        'target_nilai',
        'tw1',
        'tw2',
        'tw3',
        'tw4',
        'total',
        'persentase',
        'organisasi_id',
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
        'id_program' => 'integer',
        'organisasi_id' => 'integer',
    ];

    // Relasi dengan tabel program
    public function program()
    {
        return $this->belongsTo(Program::class, 'id_program', 'id_program');
    }

    // Relasi dengan organisasi (jika ada tabel organisasi)
    public function organisasi()
    {
        return $this->belongsTo(Organisasi::class, 'organisasi_id');
    }

    // Scope untuk filter berdasarkan tahun
    public function scopeByTahun($query, $tahun)
    {
        return $query->where('tahun', $tahun);
    }

    // Scope untuk filter berdasarkan status perencanaan
    public function scopeByStatusPerencanaan($query, $status)
    {
        return $query->where('status_perencanaan', $status);
    }

    // Scope untuk filter berdasarkan status realisasi
    public function scopeByStatusRealisasi($query, $status)
    {
        return $query->where('status_realisasi', $status);
    }

    // Accessor untuk menghitung total otomatis
    public function getTotalAttribute($value)
    {
        if ($value === null || $value == 0) {
            return $this->tw1 + $this->tw2 + $this->tw3 + $this->tw4;
        }
        return $value;
    }

    // Accessor untuk menghitung persentase otomatis
    public function getPersentaseAttribute($value)
    {
        if ($this->target_nilai && $this->target_nilai > 0) {
            return round(($this->total / $this->target_nilai) * 100, 2);
        }
        return $value;
    }

    // Mutator untuk mengupdate total ketika triwulan diubah
    public function setTw1Attribute($value)
    {
        $this->attributes['tw1'] = $value;
        $this->updateTotal();
    }

    public function setTw2Attribute($value)
    {
        $this->attributes['tw2'] = $value;
        $this->updateTotal();
    }

    public function setTw3Attribute($value)
    {
        $this->attributes['tw3'] = $value;
        $this->updateTotal();
    }

    public function setTw4Attribute($value)
    {
        $this->attributes['tw4'] = $value;
        $this->updateTotal();
    }

    // Method untuk mengupdate total dan persentase
    private function updateTotal()
    {
        $this->attributes['total'] = ($this->tw1 ?? 0) + ($this->tw2 ?? 0) + ($this->tw3 ?? 0) + ($this->tw4 ?? 0);

        if ($this->target_nilai && $this->target_nilai > 0) {
            $this->attributes['persentase'] = round(($this->attributes['total'] / $this->target_nilai) * 100, 2);
        }
    }

    // Method untuk mendapatkan status label
    public function getStatusPerencanaanLabelAttribute()
    {
        $labels = [
            'draft' => 'Draft',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak'
        ];

        return $labels[$this->status_perencanaan] ?? 'Unknown';
    }

    public function getStatusRealisasiLabelAttribute()
    {
        $labels = [
            'not_started' => 'Belum Dimulai',
            'in_progress' => 'Sedang Berjalan',
            'completed' => 'Selesai'
        ];

        return $labels[$this->status_realisasi] ?? 'Unknown';
    }

    // Static method untuk mendapatkan daftar tahun yang tersedia
    public static function getAvailableYears()
    {
        return self::distinct('tahun')->orderBy('tahun', 'desc')->pluck('tahun');
    }

    // Static method untuk mendapatkan statistik berdasarkan tahun
    public static function getStatisticsByYear($tahun)
    {
        return self::where('tahun', $tahun)
            ->selectRaw('
                COUNT(*) as total_program,
                AVG(persentase) as rata_rata_pencapaian,
                SUM(CASE WHEN status_perencanaan = "approved" THEN 1 ELSE 0 END) as program_approved,
                SUM(CASE WHEN status_realisasi = "completed" THEN 1 ELSE 0 END) as program_completed
            ')
            ->first();
    }
}
