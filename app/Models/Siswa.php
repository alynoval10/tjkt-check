<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Siswa extends Model
{
    protected $fillable = [
        'nis',
        'nama',
        'kelas_id',
    ];

    public function rombel(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function kelulusans(): HasMany
    {
        return $this->hasMany(Kelulusan::class);
    }
}
