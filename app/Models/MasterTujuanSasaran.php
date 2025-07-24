<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterTujuanSasaran extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'master_tujuan_sasarans';

    protected $fillable = [
        'tujuan',
        'indikator_tujuan',
        'target',
        'satuan',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'target' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Scope untuk data aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    public function realisasiTujuanSasaran()
    {
        return $this->hasMany(RealisasiTujuanSasaran::class, 'master_tujuan_sasarans_id');
    }
    // Relasi ke Tujas
    public function tujas()
    {
        return $this->hasMany(Tujas::class, 'master_tujuan_sasaran_id');
    }

    // Accessor untuk display di select
    public function getDisplayTextAttribute()
    {
        return $this->tujuan . ' - ' . $this->sasaran;
    }
}
