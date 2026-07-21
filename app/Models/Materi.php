<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Materi extends Model
{


    protected $fillable = [

        'kode',
        'nama',

    ];



    public function kelulusans()
{
    return $this->hasMany(Kelulusan::class);
}


}