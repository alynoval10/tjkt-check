<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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