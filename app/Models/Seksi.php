<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seksi extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'kode',
        'deskripsi',
        'bidang_id',
        'jenis',
        'aktif'
    ];

    protected $casts = [
        'aktif' => 'boolean'
    ];

    public function bidang(): BelongsTo
    {
        return $this->belongsTo(Bidang::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function getJenisLabelAttribute()
    {
        return $this->jenis === 'subbagian' ? 'Subbagian' : 'Seksi';
    }
}
