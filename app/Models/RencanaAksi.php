<?php

namespace App\Models;

use App\Services\YearContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class RencanaAksi extends Model
{
    use HasFactory;

    protected $table = 'rencana_aksi';

    protected $fillable = [
        'bidang_id',
        'id_program',
        'id_kegiatan',
        'id_sub_kegiatan',
        'rencana_aksi_list',
        'jenis_anggaran',
        'tahun',
        'narasumber',
        'rencana_pelaksanaan',
    ];

    protected $casts = [
        'rencana_aksi_list' => 'array',
        'rencana_pelaksanaan' => 'array',
        'jenis_anggaran' => 'array',
        'narasumber' => 'array',
        'tahun' => 'integer',
    ];

    protected $attributes = [
        'rencana_aksi_list' => '[]',
        'rencana_pelaksanaan' => '[]',
        'jenis_anggaran' => '[]',
        'narasumber' => '[]',
    ];

    public const NARASUMBER_OPTIONS = [
        'DPRD' => 'DPRD',
        'Kepala Dinas' => 'Kepala Dinas',
        'Kepala Daerah' => 'Kepala Daerah',
    ];

    public const JENIS_ANGGARAN_OPTIONS = [
        'APBD' => 'APBD',
        'DAK' => 'DAK',
        'DBHCHT' => 'DBHCHT',
        'BANKEU' => 'BANKEU',
    ];

    // PERBAIKAN 1: Konstanta untuk mapping bulan yang konsisten
    public const MONTH_MAPPING = [
        '01' => 'Jan',
        '1' => 'Jan',
        '02' => 'Feb',
        '2' => 'Feb',
        '03' => 'Mar',
        '3' => 'Mar',
        '04' => 'Apr',
        '4' => 'Apr',
        '05' => 'Mei',
        '5' => 'Mei',
        '06' => 'Jun',
        '6' => 'Jun',
        '07' => 'Jul',
        '7' => 'Jul',
        '08' => 'Ags',
        '8' => 'Ags',
        '09' => 'Sep',
        '9' => 'Sep',
        '10' => 'Okt',
        '11' => 'Nov',
        '12' => 'Des'
    ];

    public const MONTH_MAPPING_FULL = [
        '01' => 'Januari',
        '1' => 'Januari',
        '02' => 'Februari',
        '2' => 'Februari',
        '03' => 'Maret',
        '3' => 'Maret',
        '04' => 'April',
        '4' => 'April',
        '05' => 'Mei',
        '5' => 'Mei',
        '06' => 'Juni',
        '6' => 'Juni',
        '07' => 'Juli',
        '7' => 'Juli',
        '08' => 'Agustus',
        '8' => 'Agustus',
        '09' => 'September',
        '9' => 'September',
        '10' => 'Oktober',
        '11' => 'November',
        '12' => 'Desember'
    ];

    // Relationships
    public function bidang(): BelongsTo
    {
        return $this->belongsTo(Bidang::class, 'bidang_id', 'id');
    }

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

    // PERBAIKAN 2: Accessor yang lebih konsisten untuk TextColumn
    public function getRencanaPelaksanaanStringAttribute(): array
    {
        // Kembalikan array untuk konsistensi dengan TextColumn
        if (is_array($this->rencana_pelaksanaan) && !empty($this->rencana_pelaksanaan)) {
            return $this->rencana_pelaksanaan;
        }

        // Fallback ke rencana_aksi_list jika kolom utama kosong
        if (is_array($this->rencana_aksi_list) && !empty($this->rencana_aksi_list)) {
            $allBulan = [];
            foreach ($this->rencana_aksi_list as $item) {
                if (isset($item['bulan']) && is_array($item['bulan'])) {
                    $allBulan = array_merge($allBulan, $item['bulan']);
                }
            }
            return array_unique($allBulan);
        }

        return [];
    }

    // PERBAIKAN 3: Accessor untuk display formatted (tetap string untuk keperluan lain)
    public function getRencanaPelaksanaanFormattedAttribute(): string
    {
        $bulanArray = $this->rencana_pelaksanaan_string;

        if (empty($bulanArray)) {
            return 'Tidak ada data';
        }

        $bulanNames = [];
        foreach ($bulanArray as $bulan) {
            $cleanBulan = trim($bulan);
            $bulanNames[] = self::MONTH_MAPPING[$cleanBulan] ?? $cleanBulan;
        }

        return implode(', ', $bulanNames);
    }

    // Accessor untuk format bulan panjang
    public function getRencanaPelaksanaanFullAttribute(): string
    {
        $bulanArray = $this->rencana_pelaksanaan_string;

        if (empty($bulanArray)) {
            return 'Belum dijadwalkan';
        }

        $bulanNames = [];
        foreach ($bulanArray as $bulan) {
            $cleanBulan = trim($bulan);
            $bulanNames[] = self::MONTH_MAPPING_FULL[$cleanBulan] ?? $cleanBulan;
        }

        return implode(', ', $bulanNames);
    }

    public function getNarasumberStringAttribute(): string
    {
        // First check if narasumber column has data
        if (is_array($this->narasumber) && !empty($this->narasumber)) {
            return implode(', ', $this->narasumber);
        }

        // If not, try to get from rencana_aksi_list
        if (is_array($this->rencana_aksi_list) && !empty($this->rencana_aksi_list)) {
            $allNarasumber = [];
            foreach ($this->rencana_aksi_list as $item) {
                if (isset($item['narasumber']) && is_array($item['narasumber'])) {
                    $allNarasumber = array_merge($allNarasumber, $item['narasumber']);
                }
            }
            $uniqueNarasumber = array_unique($allNarasumber);
            return !empty($uniqueNarasumber) ? implode(', ', $uniqueNarasumber) : '-';
        }

        return '-';
    }

    public function getJenisAnggaranStringAttribute(): string
    {
        // First check if jenis_anggaran column has data
        if (is_array($this->jenis_anggaran) && !empty($this->jenis_anggaran)) {
            return implode(', ', $this->jenis_anggaran);
        }

        // If not, try to get from rencana_aksi_list
        if (is_array($this->rencana_aksi_list) && !empty($this->rencana_aksi_list)) {
            $allJenisAnggaran = [];
            foreach ($this->rencana_aksi_list as $item) {
                if (isset($item['jenis_anggaran']) && is_array($item['jenis_anggaran'])) {
                    $allJenisAnggaran = array_merge($allJenisAnggaran, $item['jenis_anggaran']);
                }
            }
            $uniqueJenisAnggaran = array_unique($allJenisAnggaran);
            return !empty($uniqueJenisAnggaran) ? implode(', ', $uniqueJenisAnggaran) : '-';
        }

        return '-';
    }

    // MENGHAPUS accessor yang duplikat - hanya menyisakan yang diperlukan
    // getFormattedRencanaPelaksanaanAttribute dan getFormattedRencanaPelaksanaanShortAttribute dihapus
    // karena sudah diganti dengan yang lebih konsisten di atas

    // Method untuk check apakah bulan tertentu aktif
    public function isBulanAktif($bulan): bool
    {
        $bulanArray = $this->rencana_pelaksanaan_string;

        if (empty($bulanArray)) {
            return false;
        }

        // Normalize bulan (bisa '1' atau '01')
        $bulan = str_pad($bulan, 2, '0', STR_PAD_LEFT);

        return in_array($bulan, $bulanArray) ||
            in_array((string)intval($bulan), $bulanArray);
    }

    protected static function boot()
    {
        parent::boot();

        // Automatically set tahun when creating new record
        static::creating(function ($model) {
            if (!$model->tahun) {
                $model->tahun = YearContext::getActiveYear();
            }
        });

        // Extract data from rencana_aksi_list to separate columns when saving
        static::saving(function ($model) {
            if (is_array($model->rencana_aksi_list) && !empty($model->rencana_aksi_list)) {
                $allNarasumber = [];
                $allJenisAnggaran = [];
                $allBulan = [];

                foreach ($model->rencana_aksi_list as $item) {
                    if (isset($item['narasumber']) && is_array($item['narasumber'])) {
                        $allNarasumber = array_merge($allNarasumber, $item['narasumber']);
                    }
                    if (isset($item['jenis_anggaran']) && is_array($item['jenis_anggaran'])) {
                        $allJenisAnggaran = array_merge($allJenisAnggaran, $item['jenis_anggaran']);
                    }
                    if (isset($item['bulan']) && is_array($item['bulan'])) {
                        $allBulan = array_merge($allBulan, $item['bulan']);
                    }
                }

                // Set the extracted data to separate columns
                $model->narasumber = array_unique($allNarasumber);
                $model->jenis_anggaran = array_unique($allJenisAnggaran);
                $model->rencana_pelaksanaan = array_unique($allBulan);
            }
        });

        // Apply global scope for year context
        static::addGlobalScope('yearContext', function (Builder $builder) {
            $builder->where('tahun', YearContext::getActiveYear());
        });
    }

    /**
     * Scope to filter by specific year
     */
    public function scopeForYear(Builder $query, int $year): Builder
    {
        return $query->where('tahun', $year);
    }

    /**
     * Scope to get records without year context filter
     */
    public function scopeWithoutYearContext(Builder $query): Builder
    {
        return $query->withoutGlobalScope('yearContext');
    }

    /**
     * Scope to filter by year range
     */
    public function scopeForYearRange(Builder $query, int $startYear, int $endYear): Builder
    {
        return $query->whereBetween('tahun', [$startYear, $endYear]);
    }
}
