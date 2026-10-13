<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Siswa extends Model
{
    protected $fillable = [
        'nis',
        'nama',
        'kelas_id',
        'public_id',
        'status',
        'tanggal_lulus',
        'periode_lulus_id',
    ];

    public function rombel(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function periodeLulus(): BelongsTo
    {
        return $this->belongsTo(PeriodeAkademik::class, 'periode_lulus_id');
    }

    public function kelulusans(): HasMany
    {
        return $this->hasMany(Kelulusan::class);
    }

    public function riwayatKelas(): HasMany
    {
        return $this->hasMany(RiwayatKelas::class);
    }

    protected function casts(): array
    {
        return [
            'tanggal_lulus' => 'date',
        ];
    }
    
    protected static function booted(): void
{
    static::creating(function ($siswa) {
        if (empty($siswa->public_id)) {
            $siswa->public_id = (string) Str::ulid();
        }
    });
}
}
