<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeriodeAkademik extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Mass Assignment
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'tahun_ajaran',
        'semester',
        'is_active',
    ];

    /*
    |--------------------------------------------------------------------------
    | Casting
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relasi Kelulusan
    |--------------------------------------------------------------------------
    */

    public function kelulusans(): HasMany
    {
        return $this->hasMany(Kelulusan::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Periode Aktif
    |--------------------------------------------------------------------------
    | Helper untuk mengambil periode yang sedang digunakan.
    */

    public static function aktif(): ?self
    {
        return static::query()
            ->where('is_active', true)
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | Nama Lengkap
    |--------------------------------------------------------------------------
    */

    public function getLabelAttribute(): string
    {
        return "{$this->tahun_ajaran} - {$this->semester}";
    }
}