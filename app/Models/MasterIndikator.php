<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
}
