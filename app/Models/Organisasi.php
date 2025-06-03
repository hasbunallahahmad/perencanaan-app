<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organisasi extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'deskripsi',
        'alamat',
        'kota',
        'aktif'
    ];

    protected $casts = [
        'aktif' => 'boolean'
    ];

    public function bidangs(): HasMany
    {
        return $this->hasMany(Bidang::class);
    }

    public function sekretariat(): HasMany
    {
        return $this->hasMany(Bidang::class)->where('is_sekretariat', true);
    }

    public function bidangOperasional(): HasMany
    {
        return $this->hasMany(Bidang::class)->where('is_sekretariat', false);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
    public function programs(): HasMany
    {
        return $this->hasMany(Program::class, 'organisasi_id', 'id');
    }

    // Alias untuk konsistensi
    public function program(): HasMany
    {
        return $this->programs();
    }
}
