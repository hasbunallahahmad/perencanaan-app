<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User; // Import User model
use App\Models\MasterTujuanSasaran; // Import MasterTujuanSasaran
use App\Models\MasterSasaran; // Import MasterSasaran
use Illuminate\Support\Facades\Auth;

class RealisasiTujuanSasaran extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'realisasi_tujuan_sasaran';

    protected $fillable = [
        'master_tujuan_sasarans_id',
        'master_sasaran_id',
        'tahun',
        'triwulan',
        'target_tahun',
        'realisasi_tahun_lalu',
        'perencanan_target',
        'realisasi_tw1',
        'realisasi_tw2',
        'realisasi_tw3',
        'realisasi_tw4',
        'verifikasi_tw1',
        'verifikasi_tw2',
        'verifikasi_tw3',
        'verifikasi_tw4',
        'dokumen_tw1',
        'dokumen_tw2',
        'dokumen_tw3',
        'dokumen_tw4',
        'status_tw1',
        'status_tw2',
        'status_tw3',
        'status_tw4',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'dokumen_tw1' => 'array',
        'dokumen_tw2' => 'array',
        'dokumen_tw3' => 'array',
        'dokumen_tw4' => 'array',
        'target_tahun' => 'decimal:2',
        'realisasi_tahun_lalu' => 'decimal:2',
        'perencanan_target' => 'decimal:2',
        'realisasi_tw1' => 'decimal:2',
        'realisasi_tw2' => 'decimal:2',
        'realisasi_tw3' => 'decimal:2',
        'realisasi_tw4' => 'decimal:2',
        'verifikasi_tw1' => 'boolean',
        'verifikasi_tw2' => 'boolean',
        'verifikasi_tw3' => 'boolean',
        'verifikasi_tw4' => 'boolean',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    // Relasi ke MasterTujuanSasaran - DIPERBAIKI: menggunakan foreign key yang benar
    public function masterTujuanSasaran()
    {
        return $this->belongsTo(MasterTujuanSasaran::class, 'master_tujuan_sasarans_id');
    }

    // Relasi ke MasterSasaran
    public function masterSasaran()
    {
        return $this->belongsTo(MasterSasaran::class, 'master_sasaran_id');
    }
    // Relasi ke User (creator)
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }


    // Relasi ke User (updater)
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scope untuk filter berdasarkan tahun
    public function scopeByYear($query, $year)
    {
        return $query->where('tahun', $year);
    }

    // Scope untuk filter berdasarkan triwulan
    public function scopeByTriwulan($query, $triwulan)
    {
        return $query->where('triwulan', $triwulan);
    }

    // Scope untuk filter berdasarkan status
    public function scopeByStatus($query, $status, $triwulan = null)
    {
        if ($triwulan) {
            return $query->where("status_tw{$triwulan}", $status);
        }
        return $query;
    }

    // Scope untuk data yang sudah diverifikasi
    public function scopeVerified($query, $triwulan = null)
    {
        if ($triwulan) {
            return $query->where("verifikasi_tw{$triwulan}", true);
        }
        return $query;
    }

    // Scope untuk data tujuan saja
    public function scopeTujuanOnly($query)
    {
        return $query->whereNotNull('master_tujuan_sasarans_id')
            ->whereNull('master_sasaran_id');
    }

    // Scope untuk data sasaran saja
    public function scopeSasaranOnly($query)
    {
        return $query->whereNotNull('master_sasaran_id')
            ->whereNull('master_tujuan_sasarans_id');
    }

    // Accessor untuk menghitung total realisasi
    public function getTotalRealisasiAttribute()
    {
        return ($this->realisasi_tw1 ?? 0) +
            ($this->realisasi_tw2 ?? 0) +
            ($this->realisasi_tw3 ?? 0) +
            ($this->realisasi_tw4 ?? 0);
    }

    // Accessor untuk menghitung persentase pencapaian
    public function getPersentasePencapaianAttribute()
    {
        $targetTahun = $this->target_tahun ?? 0;

        if ($targetTahun > 0) {
            return round(($this->total_realisasi / $targetTahun) * 100, 2);
        }
        return 0;
    }

    // Accessor untuk mendapatkan realisasi per triwulan
    public function getRealisasiTriwulan($triwulan)
    {
        $field = "realisasi_tw{$triwulan}";
        return $this->{$field} ?? 0;
    }

    // Accessor untuk mendapatkan status verifikasi per triwulan
    public function getVerifikasiTriwulan($triwulan)
    {
        $field = "verifikasi_tw{$triwulan}";
        return $this->{$field} ?? false;
    }

    // Accessor untuk mendapatkan dokumen per triwulan
    public function getDokumenTriwulan($triwulan)
    {
        $field = "dokumen_tw{$triwulan}";
        return $this->{$field} ?? [];
    }

    // Accessor untuk mendapatkan status per triwulan
    public function getStatusTriwulan($triwulan)
    {
        $field = "status_tw{$triwulan}";
        return $this->{$field} ?? null;
    }

    // Method untuk mendapatkan progress sampai triwulan tertentu
    public function getProgressSampaiTriwulan($triwulan)
    {
        $totalRealisasi = 0;
        for ($i = 1; $i <= $triwulan; $i++) {
            $totalRealisasi += $this->getRealisasiTriwulan($i);
        }

        $targetTahun = $this->target_tahun ?? 0;
        if ($targetTahun > 0) {
            return round(($totalRealisasi / $targetTahun) * 100, 2);
        }
        return 0;
    }

    // Method untuk cek apakah data sudah lengkap sampai triwulan tertentu
    public function isLengkapSampaiTriwulan($triwulan)
    {
        for ($i = 1; $i <= $triwulan; $i++) {
            if (is_null($this->getRealisasiTriwulan($i))) {
                return false;
            }
        }
        return true;
    }

    // Method untuk mendapatkan nama tujuan atau sasaran
    public function getNamaAttribute()
    {
        if ($this->master_tujuan_sasarans_id) {
            return $this->tujuanSasaran->tujuan ?? 'Tujuan tidak ditemukan';
        } elseif ($this->master_sasaran_id) {
            return $this->sasaran->sasaran ?? 'Sasaran tidak ditemukan';
        }
        return 'Data tidak valid';
    }

    // Method untuk mendapatkan indikator
    public function getIndikatorAttribute()
    {
        if ($this->master_tujuan_sasarans_id) {
            return $this->tujuanSasaran->indikator_tujuan ?? '-';
        } elseif ($this->master_sasaran_id) {
            return $this->sasaran->indikator_sasaran ?? '-';
        }
        return '-';
    }

    // Method untuk mendapatkan satuan
    public function getSatuanAttribute()
    {
        if ($this->master_tujuan_sasarans_id) {
            return $this->tujuanSasaran->satuan ?? '';
        } elseif ($this->master_sasaran_id) {
            return $this->sasaran->satuan ?? '';
        }
        return '';
    }

    // Method untuk mendapatkan tipe (tujuan/sasaran)
    public function getTipeAttribute()
    {
        if ($this->master_tujuan_sasarans_id) {
            return 'tujuan';
        } elseif ($this->master_sasaran_id) {
            return 'sasaran';
        }
        return 'unknown';
    }

    // Boot method untuk auto-fill created_by dan updated_by
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by = Auth::id();
                $model->updated_by = Auth::id();
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });
    }

    // Validation rules untuk model
    public static function getValidationRules()
    {
        return [
            'tahun' => 'required|integer|min:2020|max:2030',
            'target_tahun' => 'nullable|numeric|min:0',
            'realisasi_tw1' => 'nullable|numeric|min:0',
            'realisasi_tw2' => 'nullable|numeric|min:0',
            'realisasi_tw3' => 'nullable|numeric|min:0',
            'realisasi_tw4' => 'nullable|numeric|min:0',
            'master_tujuan_sasarans_id' => 'nullable|exists:master_tujuan_sasarans,id',
            'master_sasaran_id' => 'nullable|exists:master_sasarans,id',
        ];
    }

    // Custom validation messages
    public static function getValidationMessages()
    {
        return [
            'tahun.required' => 'Tahun harus diisi',
            'tahun.integer' => 'Tahun harus berupa angka',
            'tahun.min' => 'Tahun minimal 2020',
            'tahun.max' => 'Tahun maksimal 2030',
            'target_tahun.numeric' => 'Target tahun harus berupa angka',
            'target_tahun.min' => 'Target tahun tidak boleh negatif',
            'realisasi_tw1.numeric' => 'Realisasi TW1 harus berupa angka',
            'realisasi_tw1.min' => 'Realisasi TW1 tidak boleh negatif',
            'realisasi_tw2.numeric' => 'Realisasi TW2 harus berupa angka',
            'realisasi_tw2.min' => 'Realisasi TW2 tidak boleh negatif',
            'realisasi_tw3.numeric' => 'Realisasi TW3 harus berupa angka',
            'realisasi_tw3.min' => 'Realisasi TW3 tidak boleh negatif',
            'realisasi_tw4.numeric' => 'Realisasi TW4 harus berupa angka',
            'realisasi_tw4.min' => 'Realisasi TW4 tidak boleh negatif',
        ];
    }
}
