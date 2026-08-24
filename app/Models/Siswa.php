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
    ];

    public function rombel(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function kelulusans(): HasMany
    {
        return $this->hasMany(Kelulusan::class);
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
