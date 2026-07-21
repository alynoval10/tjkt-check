<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Kelulusan;

class Siswa extends Model
{
    protected $fillable = [
        'nis',
        'nama',
        'kelas',
    ];

    public function kelulusans()
    {
        return $this->hasMany(Kelulusan::class);
    }
}
