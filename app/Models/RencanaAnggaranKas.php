<?php

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
        'jenis_anggaran',
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
    public function getJenisAnggaranTextAttribute(): string
    {
        $jenisAnggaranMap = [
            'anggaran_murni' => 'Anggaran Murni',
            'pergeseran' => 'Pergeseran',
            'perubahan' => 'Perubahan',
        ];
        return $jenisAnggaranMap[$this->jenis_anggaran] ?? 'Unknown';
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

    public function scopeByJenisAnggaran($query, $jenisAnggaran)
    {
        return $query->where('jenis_anggaran', $jenisAnggaran);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
    public function scopeByYear($query, ?int $year = null)
    {
        $activeYear = $year ?? YearContext::getActiveYear();
        return $query->where('tahun', $activeYear)
            ->orderBy('created_at', 'desc')
            ->limit(1);
    }

    public function scopeLatestApprovedByYear($query, ?int $year = null)
    {
        $activeYear = $year ?? YearContext::getActiveYear();
        return $query->where('tahun', $activeYear)
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->limit(1);
    }
    public static function getLatestByYear($tahun = null, $approved = true)
    {
        $tahun = $tahun ?? date('Y');

        $query = self::byTahun($tahun);

        if ($approved) {
            $query = $query->approved();
        }

        return $query->orderBy('created_at', 'desc')->first();
    }
    public static function getTotalByJenisAnggaran($tahun, $jenisAnggaran)
    {
        return self::byTahun($tahun)
            ->byJenisAnggaran($jenisAnggaran)
            ->approved()
            ->sum('jumlah_rencana');
    }
    // Static method untuk mendapatkan data widget
    public static function getWidgetData($tahun = null)
    {
        $tahun = $tahun ?? date('Y');
        $latestRecord = self::getLatestByYear($tahun, true);
        if (!$latestRecord) {
            return [
                'total_anggaran' => 0,
                'jenis_anggaran' => null,
                'tanggal_update' => null,
                'status' => null,
            ];
        }
        return [
            'total_anggaran' => $latestRecord->jumlah_rencana,
            'jenis_anggaran' => $latestRecord->jenis_anggaran_text,
            'tanggal_update' => $latestRecord->created_at,
            'status' => $latestRecord->status_text,
        ];
    }
    public static function getRiwayatAnggaran($tahun = null)
    {
        $tahun = $tahun ?? date('Y');

        return self::byTahun($tahun)
            ->approved()
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($record) {
                return [
                    'id' => $record->id,
                    'jenis_anggaran' => $record->jenis_anggaran_text,
                    'jumlah_rencana' => $record->jumlah_rencana,
                    'jumlah_formatted' => $record->jumlah_formatted,
                    'tanggal_rencana' => $record->tanggal_rencana,
                    'created_at' => $record->created_at,
                    'deskripsi' => $record->deskripsi,
                ];
            });
    }
    public static function getRingkasanAnggaran($tahun = null)
    {
        $tahun = $tahun ?? date('Y');

        $data = [];
        $jenisAnggaranList = ['anggaran_murni', 'pergeseran', 'perubahan'];

        foreach ($jenisAnggaranList as $jenis) {
            $total = self::getTotalByJenisAnggaran($tahun, $jenis);
            $data[$jenis] = [
                'total' => $total,
                'formatted' => 'Rp ' . number_format($total, 0, ',', '.'),
            ];
        }

        $latestRecord = self::getLatestByYear($tahun, true);
        $data['latest'] = $latestRecord ? [
            'jenis_anggaran' => $latestRecord->jenis_anggaran,
            'jumlah_rencana' => $latestRecord->jumlah_rencana,
            'tanggal_update' => $latestRecord->created_at,
        ] : null;

        return $data;
    }
}
