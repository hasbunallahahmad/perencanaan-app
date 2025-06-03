<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bidang extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'kode',
        'deskripsi',
        'organisasi_id',
        'is_sekretariat',
        'aktif'
    ];

    protected $casts = [
        'is_sekretariat' => 'boolean',
        'aktif' => 'boolean'
    ];

    public function organisasi(): BelongsTo
    {
        return $this->belongsTo(Organisasi::class);
    }

    public function seksis(): HasMany
    {
        return $this->hasMany(Seksi::class);
    }

    public function subbagians(): HasMany
    {
        return $this->hasMany(Seksi::class)->where('jenis', 'subbagian');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function getJenisUnitAttribute()
    {
        return $this->is_sekretariat ? 'Subbagian' : 'Seksi';
    }
}
