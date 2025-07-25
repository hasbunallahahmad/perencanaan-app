<?php

namespace App\Models;

use App\Services\YearContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Realisasi extends Model
{
    use HasFactory;

    protected $table = 'realisasi';

    protected $fillable = [
        'rencana_aksi_id',
        'rencana_aksi_list_index', // Tambahan untuk menyimpan index dari rencana_aksi_list
        'nama_aksi',
        'tanggal',
        'tempat',
        'jumlah_dprd',
        'jumlah_kepala_dinas',
        'jumlah_kepala_daerah',
        'total_narasumber',
        'laki_laki',
        'perempuan',
        'jumlah_peserta',
        'asal_peserta',
        'realisasi_anggaran',
        'foto_link_gdrive',
        'tahun',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah_dprd' => 'integer',
        'jumlah_kepala_dinas' => 'integer',
        'jumlah_kepala_daerah' => 'integer',
        'total_narasumber' => 'integer',
        'laki_laki' => 'integer',
        'perempuan' => 'integer',
        'jumlah_peserta' => 'integer',
        'realisasi_anggaran' => 'decimal:2',
        'tahun' => 'integer',
        'rencana_aksi_list_index' => 'integer',
    ];

    public function rencanaAksi(): BelongsTo
    {
        return $this->belongsTo(RencanaAksi::class, 'rencana_aksi_id', 'id');
    }

    public function getBidangAttribute()
    {
        return $this->rencanaAksi?->bidang;
    }

    public function getProgramAttribute()
    {
        return $this->rencanaAksi?->program;
    }

    public function getKegiatanAttribute()
    {
        return $this->rencanaAksi?->kegiatan;
    }

    public function getSubKegiatanAttribute()
    {
        return $this->rencanaAksi?->subKegiatan;
    }

    // Accessor untuk mendapatkan detail aksi dari rencana_aksi_list
    public function getRencanaAksiDetailAttribute()
    {
        if ($this->rencana_aksi_list_index !== null && $this->rencanaAksi) {
            $rencanaAksiList = $this->rencanaAksi->rencana_aksi_list;
            if (isset($rencanaAksiList[$this->rencana_aksi_list_index])) {
                return $rencanaAksiList[$this->rencana_aksi_list_index];
            }
        }
        return null;
    }
    public function getNarasumberDetailAttribute(): array
    {
        $detail = [];

        if ($this->jumlah_dprd > 0) {
            $detail[] = $this->jumlah_dprd . ' DPRD';
        }

        if ($this->jumlah_kepala_dinas > 0) {
            $detail[] = $this->jumlah_kepala_dinas . ' Kepala Dinas';
        }

        if ($this->jumlah_kepala_daerah > 0) {
            $detail[] = $this->jumlah_kepala_daerah . ' Kepala Daerah';
        }

        return $detail;
    }
    // Accessor untuk mendapatkan nama aksi dari rencana_aksi_list
    public function getNamaAksiFromListAttribute()
    {
        $detail = $this->rencana_aksi_detail;
        return $detail['aksi'] ?? $this->nama_aksi;
    }

    public function setLakiLakiAttribute($value)
    {
        $this->attributes['laki_laki'] = $value;
        $this->calculateJumlahPeserta();
    }

    public function setPerempuanAttribute($value)
    {
        $this->attributes['perempuan'] = $value;
        $this->calculateJumlahPeserta();
    }

    private function calculateJumlahPeserta()
    {
        $lakiLaki = $this->attributes['laki_laki'] ?? 0;
        $perempuan = $this->attributes['perempuan'] ?? 0;
        $this->attributes['jumlah_peserta'] = $lakiLaki + $perempuan;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->tahun) {
                $model->tahun = YearContext::getActiveYear();
            }
        });

        static::saving(function ($model) {
            if (isset($model->attributes['laki_laki']) || isset($model->attributes['perempuan'])) {
                $lakiLaki = $model->attributes['laki_laki'] ?? $model->getOriginal('laki_laki', 0);
                $perempuan = $model->attributes['perempuan'] ?? $model->getOriginal('perempuan', 0);
                $model->attributes['jumlah_peserta'] = $lakiLaki + $perempuan;
            }

            // Auto-update nama_aksi dari rencana_aksi_list jika index tersedia
            if ($model->rencana_aksi_list_index !== null && $model->rencana_aksi_id) {
                $rencanaAksi = RencanaAksi::find($model->rencana_aksi_id);
                if ($rencanaAksi && isset($rencanaAksi->rencana_aksi_list[$model->rencana_aksi_list_index]['aksi'])) {
                    $model->attributes['nama_aksi'] = $rencanaAksi->rencana_aksi_list[$model->rencana_aksi_list_index]['aksi'];
                }
            }
        });

        static::addGlobalScope('yearContext', function (Builder $builder) {
            $builder->where('tahun', YearContext::getActiveYear());
        });
        // Auto-calculate total_narasumber before saving
        static::saving(function ($model) {
            $model->total_narasumber =
                $model->jumlah_dprd +
                $model->jumlah_kepala_dinas +
                $model->jumlah_kepala_daerah;
        });
    }

    public function scopeForYear(Builder $query, int $year): Builder
    {
        return $query->where('tahun', $year);
    }

    public function scopeWithoutYearContext(Builder $query): Builder
    {
        return $query->withoutGlobalScope('yearContext');
    }

    public function scopeByRencanaAksi(Builder $query, int $rencanaAksiId): Builder
    {
        return $query->where('rencana_aksi_id', $rencanaAksiId);
    }

    public function scopeByBidang(Builder $query, int $bidangId): Builder
    {
        return $query->whereHas('rencanaAksi', function ($q) use ($bidangId) {
            $q->where('bidang_id', $bidangId);
        });
    }
}
