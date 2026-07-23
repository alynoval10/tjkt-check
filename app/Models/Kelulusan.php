<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelulusan extends Model
{
    protected $fillable = [
        'siswa_nama',
        'materi_nama',
        'user_nama',
        'tanggal_uji',
    ];


    public function siswa()
{
    return $this->belongsTo(Siswa::class);
}


public function materi()
{
    return $this->belongsTo(Materi::class);
}


public function user()
{
    return $this->belongsTo(User::class);
}
}