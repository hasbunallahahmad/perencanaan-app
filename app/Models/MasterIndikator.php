<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterIndikator extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'master_indikator';
    protected $fillable = [
        'nama_indikator'
    ];
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    public function programs()
    {
        return $this->hasMany(Program::class, 'indikator_id');
    }
    // Di App\Models\MasterIndikator.php
    public function kegiatans()
    {
        return $this->belongsToMany(Kegiatan::class, 'kegiatan_indikator', 'indikator_id', 'kegiatan_id');
    }
    public function subKegiatans(): HasMany
    {
        return $this->hasMany(SubKegiatan::class, 'indikator_id');
    }
    public function scopeUsed($query)
    {
        return $query->whereHas('subKegiatans');
    }
    public function scopeUnused($query)
    {
        return $query->whereDoesntHave('subKegiatans');
    }
    public function getSubKegiatanCountAttribute(): int
    {
        return $this->subKegiatans()->count();
    }
    public function getFormattedNameAttribute(): string
    {
        return $this->nama_indikator;
    }
}
