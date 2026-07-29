<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Materi extends Model
{
    protected $fillable = [
        'kode',
        'nama',
        'tingkat',
        'deskripsi',
        'pdf_file',
    ];

    public function kelulusans(): HasMany
    {
        return $this->hasMany(Kelulusan::class);
    }
}