<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CapaianKinerjaKegiatan extends Model
{
    use HasFactory;

    protected $table = 'capaian_kinerja_kegiatan';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id_program',
        'id_kegiatan',
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

    public function program()
    {
        return $this->belongsTo(Program::class, 'id_program', 'id_program');
    }

    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class, 'id_kegiatan', 'id_kegiatan');
    }

    public function subKegiatan()
    {
        return $this->belongsTo(SubKegiatan::class);
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

    public function scopeByKegiatan($query, $kegiatanId)
    {
        return $query->where('id_kegiatan', $kegiatanId);
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
    public function getStats(): array
    {
        $query = CapaianKinerjaKegiatan::query();

        return [
            'total' => $query->count(),
            'approved' => $query->where('status_perencanaan', 'approved')->count(),
            'draft' => $query->where('status_perencanaan', 'draft')->count(),
            'current_year' => date('Y'),
            'programs_count' => Program::count(),
            'activities_count' => Kegiatan::count(),
        ];
    }

    // Method untuk mendapatkan statistik berdasarkan tahun
    public function getStatsByYear(int $year = null): array
    {
        $year = $year ?? date('Y');
        $query = CapaianKinerjaKegiatan::where('tahun', $year);

        return [
            'total' => $query->count(),
            'approved' => $query->where('status_perencanaan', 'approved')->count(),
            'draft' => $query->where('status_perencanaan', 'draft')->count(),
            'year' => $year,
        ];
    }

    // Method untuk mendapatkan statistik berdasarkan program
    public function getStatsByProgram(int $programId): array
    {
        $query = CapaianKinerjaKegiatan::where('id_program', $programId);

        return [
            'total' => $query->count(),
            'approved' => $query->where('status_perencanaan', 'approved')->count(),
            'draft' => $query->where('status_perencanaan', 'draft')->count(),
            'avg_target' => $query->avg('target_nilai'),
        ];
    }

    // Method untuk validasi data sebelum menyimpan
    protected function validatePlanningData(array $data): bool
    {
        // Validasi program dan kegiatan masih aktif
        $program = Program::find($data['id_program']);
        $kegiatan = Kegiatan::find($data['id_kegiatan']);

        if (!$program || !$kegiatan) {
            return false;
        }

        // Validasi tahun tidak kurang dari tahun sekarang
        if ($data['tahun'] < date('Y')) {
            return false;
        }

        // Validasi target nilai harus positif
        if ($data['target_nilai'] <= 0) {
            return false;
        }

        return true;
    }

    // Method untuk mendapatkan ringkasan perencanaan
    public function getPlanningDashboard(): array
    {
        $currentYear = date('Y');

        return [
            'current_year_stats' => $this->getStatsByYear($currentYear),
            'previous_year_stats' => $this->getStatsByYear($currentYear - 1),
            'total_programs' => Program::count(),
            'total_activities' => Kegiatan::count(),
            'pending_approvals' => CapaianKinerjaKegiatan::where('status_perencanaan', 'draft')->count(),
            'recent_activities' => CapaianKinerjaKegiatan::with(['program', 'kegiatan'])
                ->latest()
                ->limit(5)
                ->get(),
        ];
    }

    // Method untuk export data perencanaan
    public function exportPlanningData(array $filters = []): array
    {
        $query = CapaianKinerjaKegiatan::with(['program', 'kegiatan']);

        // Apply filters
        if (!empty($filters['program_id'])) {
            $query->where('id_program', $filters['program_id']);
        }

        if (!empty($filters['year'])) {
            $query->where('tahun', $filters['year']);
        }

        if (!empty($filters['status'])) {
            $query->where('status_perencanaan', $filters['status']);
        }

        return $query->get()->map(function ($item) {
            return [
                'Kode Program' => $item->program->kode_program ?? '-',
                'Nama Program' => $item->program->nama_program ?? '-',
                'Kode Kegiatan' => $item->kegiatan->kode_kegiatan ?? '-',
                'Nama Kegiatan' => $item->kegiatan->nama_kegiatan ?? '-',
                'Target Nilai' => $item->target_nilai,
                'Satuan' => $item->target_dokumen,
                'Tahun' => $item->tahun,
                'Status' => $item->status_perencanaan === 'approved' ? 'Disetujui' : 'Draft',
                'Tanggal Dibuat' => $item->created_at->format('d/m/Y'),
                'Terakhir Diperbarui' => $item->updated_at->format('d/m/Y'),
            ];
        })->toArray();
    }

    // Method untuk bulk operations
    public function bulkApproveAllDrafts(): int
    {
        return CapaianKinerjaKegiatan::where('status_perencanaan', 'draft')
            ->update(['status_perencanaan' => 'approved']);
    }

    public function bulkDeleteByYear(int $year): int
    {
        return CapaianKinerjaKegiatan::where('tahun', $year)->delete();
    }

    // Method untuk mendapatkan riwayat perubahan
    public function getActivityHistory(int $limit = 10): array
    {
        return CapaianKinerjaKegiatan::with(['program', 'kegiatan'])
            ->latest('updated_at')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'program' => $item->program->nama_program ?? '-',
                    'kegiatan' => $item->kegiatan->nama_kegiatan ?? '-',
                    'target' => $item->target_nilai . ' ' . $item->target_dokumen,
                    'status' => $item->status_perencanaan,
                    'updated_at' => $item->updated_at->diffForHumans(),
                ];
            })->toArray();
    }
}
